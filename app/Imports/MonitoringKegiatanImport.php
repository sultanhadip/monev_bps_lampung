<?php

namespace App\Imports;

use App\Models\DataKegiatan;
use App\Models\MonitoringKegiatan;
use App\Models\target_realisasi_satker;
use App\Models\TimKerja;
use App\Models\SatuanKerja;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonitoringKegiatanImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $previousRow = null;
    protected $monitoringKegiatan = null;
    protected $totalTargetSample = 0;

    protected $duplicateRows = [];
    protected $successCount = 0;

    public function model(array $row)
    {
        // Validasi data wajib (sesuai rules)
        if (!isset($row['nama_tim'], $row['nama_kegiatan'], $row['waktu_mulai'], $row['waktu_selesai'], $row['kode_satuan_kerja'], $row['target_sampel'])) {
            Log::error('Data yang dibutuhkan tidak lengkap', $row);
            return null;
        }

        try {
            // Validasi periode kegiatan
            if (!$this->isValidPeriodeKegiatan($row)) {
                Log::error('Periode kegiatan tidak sesuai dengan inputan', $row);
                return null;
            }

            $bulanNames = [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember'
            ];

            $dataKeg = DataKegiatan::where('nama_kegiatan', $row['nama_kegiatan'])->first();
            if (!$dataKeg) {
                Log::error('Kegiatan tidak ditemukan: ' . $row['nama_kegiatan'], $row);
                return null;
            }

            $tipe = strtolower($dataKeg->periode_kegiatan);
            $start = Carbon::parse($row['waktu_mulai']);
            $month = $start->month;
            $year = $start->year;

            // Cek duplikat berdasar tipe periode
            switch ($tipe) {
                case 'bulanan':
                    $exists = MonitoringKegiatan::whereMonth('waktu_mulai', $month)
                        ->whereYear('waktu_mulai', $year)
                        ->where('kode_kegiatan', $dataKeg->id)
                        ->exists();
                    $label = "{$bulanNames[$month - 1]} {$year}";
                    break;
                case 'triwulan':
                    $q = ceil($month / 3);
                    $range = [($q - 1) * 3 + 1, $q * 3];
                    $exists = MonitoringKegiatan::whereYear('waktu_mulai', $year)
                        ->whereBetween(DB::raw('MONTH(waktu_mulai)'), $range)
                        ->where('kode_kegiatan', $dataKeg->id)
                        ->exists();
                    $label = "Triwulan {$q} {$year}";
                    break;
                case 'semesteran':
                    if ($month <= 6) {
                        $range = [1, 6];
                        $sem = 'I';
                    } else {
                        $range = [7, 12];
                        $sem = 'II';
                    }
                    $exists = MonitoringKegiatan::whereYear('waktu_mulai', $year)
                        ->whereBetween(DB::raw('MONTH(waktu_mulai)'), $range)
                        ->where('kode_kegiatan', $dataKeg->id)
                        ->exists();
                    $label = "Semester {$sem} {$year}";
                    break;
                default:
                    $exists = MonitoringKegiatan::whereYear('waktu_mulai', $year)
                        ->where('kode_kegiatan', $dataKeg->id)
                        ->exists();
                    $label = (string)$year;
            }

            if ($exists) {
                // Catat duplikat, jangan throw agar import tetap lanjut
                $this->duplicateRows[] = [
                    'nama_kegiatan' => $row['nama_kegiatan'],
                    'periode' => $label,
                ];
                return null;
            }

            // Cari tim kerja
            $tim = TimKerja::where('nama_tim', $row['nama_tim'])->first();
            if (!$tim) {
                Log::error('Tim tidak ditemukan: ' . $row['nama_tim'], $row);
                return null;
            }

            // Cek apakah baris bagian kegiatan sama dengan baris sebelumnya
            $isSameKegiatan = $this->isSameKegiatan($row);

            if ($isSameKegiatan) {
                $this->accumulateTargetSample($row);
            } else {
                $this->createNewMonitoringKegiatan($row, $tim, $dataKeg);
            }

            // Cari satuan kerja
            $satuanKerja = SatuanKerja::where('kode_satuan_kerja', $row['kode_satuan_kerja'])->first();
            if (!$satuanKerja) {
                Log::error('Satuan kerja tidak ditemukan: ' . $row['kode_satuan_kerja'], $row);
                return null;
            }

            // Simpan target sampel satuan kerja
            $this->saveTargetSampel($row, $satuanKerja->id);

            // Update previous row
            $this->previousRow = $row;

            // Tambah counter sukses
            $this->successCount++;

            return null;
        } catch (\Exception $e) {
            Log::error('Error import baris Monitoring Kegiatan: ' . $e->getMessage(), $row);
            return null;
        }
    }

    private function saveTargetSampel($row, $satuanKerjaId)
    {
        target_realisasi_satker::create([
            'id_monitoring_kegiatan' => $this->monitoringKegiatan->id,
            'kode_satuan_kerja' => $satuanKerjaId,
            'target_satker' => $row['target_sampel'],
        ]);
    }

    private function isSameKegiatan($row)
    {
        if (!$this->previousRow) {
            return false;
        }

        return (
            $this->previousRow['nama_tim'] == $row['nama_tim'] &&
            $this->previousRow['nama_kegiatan'] == $row['nama_kegiatan'] &&
            $this->previousRow['waktu_mulai'] == $row['waktu_mulai'] &&
            $this->previousRow['waktu_selesai'] == $row['waktu_selesai']
        );
    }

    private function createNewMonitoringKegiatan($row, $tim, $kegiatan)
    {
        $this->monitoringKegiatan = MonitoringKegiatan::create([
            'kode_tim' => $tim->id,
            'kode_kegiatan' => $kegiatan->id,
            'waktu_mulai' => Carbon::parse($row['waktu_mulai'])->format('Y-m-d'),
            'waktu_selesai' => Carbon::parse($row['waktu_selesai'])->format('Y-m-d'),
            'realisasi_kegiatan' => 0,
            'target_sampel' => 0,
        ]);

        $this->totalTargetSample = 0;
    }

    private function accumulateTargetSample($row)
    {
        $this->totalTargetSample += $row['target_sampel'];
        $this->monitoringKegiatan->update([
            'target_sampel' => $this->totalTargetSample,
        ]);
    }

    private function isValidPeriodeKegiatan($row)
    {
        $kegiatan = DataKegiatan::where('nama_kegiatan', $row['nama_kegiatan'])->first();
        if (!$kegiatan) {
            Log::error('Kegiatan tidak ditemukan: ' . $row['nama_kegiatan'], $row);
            return false;
        }
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_tim' => 'required',
            'nama_kegiatan' => 'required',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after_or_equal:waktu_mulai',
            'kode_satuan_kerja' => 'required',
            'target_sampel' => 'required|numeric',
        ];
    }

    // Getter untuk duplikat dan sukses agar controller dapat mengaksesnya
    public function getDuplicateRows()
    {
        return $this->duplicateRows;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
