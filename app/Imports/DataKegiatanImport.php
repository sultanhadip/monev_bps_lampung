<?php

namespace App\Imports;

use App\Models\DataKegiatan;
use App\Models\TimKerja;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class DataKegiatanImport implements ToModel, WithHeadingRow
{
    protected $duplicateRows = []; // Menyimpan baris duplikat
    protected $successCount = 0; // Menyimpan jumlah data yang berhasil disimpan
    protected $errorRows = []; // Menyimpan baris yang error

    public function model(array $row)
    {
        try {
            // Validasi data wajib
            if (
                empty($row['nama_tim']) ||
                empty($row['objek_kegiatan']) ||
                empty($row['periode_kegiatan']) ||
                empty($row['nama_kegiatan'])
            ) {
                Log::error('Data yang dibutuhkan tidak lengkap', ['row' => $row]);
                $this->errorRows[] = $row; // Menyimpan baris yang error
                return null;
            }

            // Cari tim berdasarkan nama_tim
            $tim = TimKerja::where('nama_tim', $row['nama_tim'])->first();
            if (!$tim) {
                Log::error('Tim tidak ditemukan: ' . $row['nama_tim'], ['row' => $row]);
                $this->errorRows[] = $row; // Menyimpan baris yang error
                return null;
            }

            // Cek duplikat berdasarkan nama_kegiatan dan id_tim_kerja
            $exists = DataKegiatan::where('nama_kegiatan', $row['nama_kegiatan'])
                ->where('id_tim_kerja', $tim->id)
                ->exists();

            if ($exists) {
                // Simpan data duplikat untuk ditampilkan di controller
                $this->duplicateRows[] = [
                    'nama_kegiatan' => $row['nama_kegiatan'],
                    'nama_tim' => $row['nama_tim'],
                ];
                return null; // Skip insert duplikat
            }

            // Simpan data kegiatan baru
            $dataKegiatan = DataKegiatan::create([
                'id_tim_kerja' => $tim->id,
                'kode_tim' => $tim->id,
                'objek_kegiatan' => $row['objek_kegiatan'],
                'periode_kegiatan' => $row['periode_kegiatan'],
                'nama_kegiatan' => $row['nama_kegiatan'],
            ]);

            $this->successCount++; // Increment success count

            Log::info('Data Kegiatan berhasil disimpan', ['data_kegiatan' => $dataKegiatan]);

            return $dataKegiatan;
        } catch (\Throwable $th) {
            Log::error('Terjadi kesalahan saat mengimpor data Data Kegiatan: ' . $th->getMessage(), ['row' => $row]);
            $this->errorRows[] = $row; // Menyimpan baris yang error
            return null;
        }
    }

    // Getter duplikat untuk controller
    public function getDuplicateRows()
    {
        return $this->duplicateRows;
    }

    // Getter jumlah sukses untuk controller
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    // Getter untuk mendapatkan baris yang error
    public function getErrorRows()
    {
        return $this->errorRows;
    }
}
