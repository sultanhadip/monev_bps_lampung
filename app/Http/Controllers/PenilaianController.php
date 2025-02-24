<?php

namespace App\Http\Controllers;

use App\Models\Penilaian;
use App\Models\Sertifikat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenilaianController extends Controller
{

    public function index(Request $request)
    {
        // Menentukan jumlah records per halaman, default 10
        $perPage = $request->get('recordsPerPage', 10);

        // Menampilkan semua data penilaian untuk setiap satuan kerja
        $penilaian = Penilaian::with('satuanKerja')  // Relasi dengan satuan kerja jika ada
            ->orderBy('peringkat', 'asc')  // Urutkan berdasarkan peringkat
            ->paginate($perPage);  // Membatasi tampilan dengan pagination sesuai dengan recordsPerPage

        // Menambahkan log untuk memverifikasi data yang dikirim ke view
        Log::info('Data Penilaian yang Dikirim ke View:', $penilaian->toArray());

        return view('penilaian', compact('penilaian'));
    }

    public function generate()
    {
        // Mendapatkan bulan dan tahun saat ini
        $currentPeriod = now()->format('m-Y');  // Format bulan-tahun (misal: 01-2025)

        // Ambil data kegiatan yang relevan untuk bulan dan tahun ini
        $kegiatan = DB::table('target_realisasi_satkers as trs')
            ->join('update_target_realisasis as ur', 'ur.id_target_realisasi', '=', 'trs.id')
            ->join('monitoring_kegiatans as mk', 'mk.id', '=', 'trs.id_monitoring_kegiatan')
            ->select(
                'trs.kode_satuan_kerja',
                'trs.target_satker',
                'ur.realisasi_satker',
                'mk.waktu_selesai',
                'mk.kode_tim'
            )
            ->whereRaw('MONTH(mk.waktu_selesai) = ?', [now()->month])  // Filter berdasarkan bulan saat ini
            ->whereRaw('YEAR(mk.waktu_selesai) = ?', [now()->year])   // Filter berdasarkan tahun saat ini
            ->get();

        // Tambahkan log untuk memverifikasi data yang diambil
        Log::info('Data kegiatan yang diambil untuk bulan ' . now()->format('Y F'), ['kegiatan' => json_encode($kegiatan->toArray())]);

        if ($kegiatan->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data kegiatan untuk dihitung.'], 400);
        }

        // Menyimpan nilai kinerja tim untuk setiap satuan kerja
        $nilaiTimKerja = [];

        // Menghitung nilai kegiatan untuk setiap tim kerja
        foreach ($kegiatan as $item) {
            // Perbaikan di bagian ini untuk mengirimkan tiga parameter ke fungsi hitungWaktu
            $nilaiRealisasi = $this->hitungRealisasi($item->target_satker, $item->realisasi_satker);
            $nilaiWaktu = $this->hitungWaktu($item->target_satker, $item->realisasi_satker, $item->waktu_selesai);


            // Nilai akhir untuk kegiatan (gabungan antara realisasi dan waktu)
            $nilaiAkhirKegiatan = (0.7 * $nilaiRealisasi) + (0.3 * $nilaiWaktu);

            // Menyimpan nilai kegiatan untuk setiap tim
            if (!isset($nilaiTimKerja[$item->kode_satuan_kerja][$item->kode_tim])) {
                $nilaiTimKerja[$item->kode_satuan_kerja][$item->kode_tim] = [];
            }
            $nilaiTimKerja[$item->kode_satuan_kerja][$item->kode_tim][] = $nilaiAkhirKegiatan;
        }

        // Menghitung nilai rata-rata kinerja tim untuk setiap satuan kerja
        $nilaiSatuanKerja = [];

        foreach ($nilaiTimKerja as $kodeSatuanKerja => $timData) {
            $nilaiRataRataSatuanKerja = [];

            foreach ($timData as $kodeTim => $nilaiKegiatan) {
                // Menghitung rata-rata nilai kegiatan untuk tiap tim kerja di satuan kerja
                $nilaiRataRataSatuanKerja[$kodeTim] = array_sum($nilaiKegiatan) / count($nilaiKegiatan);
            }

            // Menghitung rata-rata nilai kinerja seluruh tim di satuan kerja
            $nilaiSatuanKerja[$kodeSatuanKerja] = array_sum($nilaiRataRataSatuanKerja) / count($nilaiRataRataSatuanKerja);
        }

        // Update atau buat data penilaian untuk setiap satuan kerja
        foreach ($nilaiSatuanKerja as $kodeSatuanKerja => $nilaiAkhir) {
            // Log untuk memeriksa apakah penilaian diperbarui
            Log::info('Update atau create penilaian untuk satuan kerja:', [
                'kode_satuan_kerja' => $kodeSatuanKerja,
                'nilai_kinerja' => $nilaiAkhir
            ]);

            $penilaian = Penilaian::updateOrCreate(
                [
                    'kode_satuan_kerja' => $kodeSatuanKerja,
                    'periode_kinerja' => $currentPeriod,
                ],
                [
                    'nilai_kinerja' => $nilaiAkhir,
                    'peringkat' => 0,
                ]
            );
        }

        // Mengupdate peringkat berdasarkan nilai kinerja
        $penilaianBulanIni = Penilaian::where('periode_kinerja', $currentPeriod)
            ->orderBy('nilai_kinerja', 'desc')
            ->get();

        $rank = 1;
        foreach ($penilaianBulanIni as $item) {
            $item->update(['peringkat' => $rank++]);

            // Log untuk memeriksa peringkat yang diperbarui
            Log::info('Peringkat diperbarui untuk satuan kerja:', [
                'kode_satuan_kerja' => $item->kode_satuan_kerja,
                'peringkat' => $item->peringkat
            ]);
        }

        // Mengenerate nomor sertifikat hanya untuk 3 satuan kerja dengan peringkat 1, 2, dan 3
        $topThreePenilaian = Penilaian::where('periode_kinerja', $currentPeriod)
            ->whereIn('peringkat', [1, 2, 3])
            ->get();

        foreach ($topThreePenilaian as $item) {
            // Cek apakah sertifikat sudah ada untuk periode yang sama
            $existingCertificate = Sertifikat::where('id_penilaian', $item->id)->first();

            if ($existingCertificate) {
                // Jika sertifikat sudah ada, update nomor sertifikat
                $existingCertificate->update([
                    'nomor_sertifikat' => 'SERT-' . strtoupper(Str::random(6)),
                ]);
            } else {
                // Jika sertifikat belum ada, buat sertifikat baru
                $nomorSertifikat = 'SERT-' . strtoupper(Str::random(6));

                Sertifikat::create([
                    'id_penilaian' => $item->id,  // Menyimpan ID penilaian
                    'nomor_sertifikat' => $nomorSertifikat,  // Menyimpan nomor sertifikat
                ]);
            }
        }

        return response()->json(['message' => 'Penilaian berhasil digenerate dan dirangking untuk periode ini.']);
    }

    private function hitungRealisasi($target, $realisasi)
    {
        $persentase = ($target > 0) ? ($realisasi / $target) * 100 : 0;

        if ($persentase >= 95) {
            return 4;
        } elseif ($persentase >= 80) {
            return 3;
        } elseif ($persentase >= 60) {
            return 2;
        } else {
            return 1;
        }
    }

    private function hitungWaktu($target, $realisasi, $waktuSelesai)
    {
        // Cek apakah persentase realisasi sudah mencapai 100%
        $persentase = ($target > 0) ? ($realisasi / $target) * 100 : 0;

        if ($persentase >= 100) {
            // Hitung selisih hari antara tanggal capai 100% dan batas waktu
            $hariSelisih = now()->diffInDays(\Carbon\Carbon::parse($waktuSelesai), false);

            // Tentukan nilai berdasarkan selisih hari
            if ($hariSelisih >= 2) {
                return 4; // Capaian 100% 2 hari sebelum batas waktu
            } elseif ($hariSelisih === 1) {
                return 3; // Capaian 100% 1 hari sebelum batas waktu
            } elseif ($hariSelisih === 0) {
                return 2; // Capaian 100% pada batas waktu
            } else {
                return 1; // Melewati batas waktu atau tidak mencapai 100%
            }
        } else {
            // Jika persentase tidak mencapai 100%, kembalikan nilai 1
            return 1;
        }
    }
}
