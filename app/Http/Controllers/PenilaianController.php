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
        $filterBulan = $request->get('filter_bulan');
        $filterTahun = $request->get('filter_tahun');
        $perPage     = $request->get('per_page', 10);

        // ambil 4 karakter terakhir, cast ke integer, lalu unik & urutkan
        $years = Penilaian::selectRaw(
            "DISTINCT CAST(RIGHT(periode_kinerja, 4) AS UNSIGNED) AS year"
        )
            ->orderByDesc('year')
            ->pluck('year');


        $qb = Penilaian::with('satuanKerja')
            // filter Bulan
            ->when($filterBulan, function ($q) use ($filterBulan) {
                // cast ke int sebelum masuk ke Carbon
                $m = (int) $filterBulan;
                // buat Carbon dengan bulan m, tanggal 1
                $monthName = \Carbon\Carbon::createFromDate(null, $m, 1)
                    ->locale('id')
                    ->isoFormat('MMMM');
                $q->where('periode_kinerja', 'like', "{$monthName}%");
            })
            // filter Tahun
            ->when($filterTahun, function ($q) use ($filterTahun) {
                $q->where('periode_kinerja', 'like', "%{$filterTahun}");
            })
            ->orderBy('kode_satuan_kerja', 'asc');

        $penilaian = $qb
            ->paginate($perPage)
            ->appends([
                'filter_bulan' => $filterBulan,
                'filter_tahun' => $filterTahun,
                'per_page'     => $perPage,
            ]);

        return view('penilaian', compact(
            'penilaian',
            'filterBulan',
            'filterTahun',
            'years'
        ));
    }

    public function generate()
    {
        // Mendapatkan bulan dan tahun saat ini
        $currentMonth = now()->format('m');  // Mendapatkan bulan saat ini
        $currentYear = now()->format('Y');   // Mendapatkan tahun saat ini

        // Menggunakan Carbon untuk mengubah format bulan menjadi nama bulan
        $bulanNama = now()->monthName; // Carbon menghasilkan nama bulan dalam bahasa Inggris
        $bulanNamaIndo = $this->getNamaBulanIndo($bulanNama); // Mengubah bulan Inggris menjadi Indonesia
        $currentPeriod = $bulanNamaIndo . ' ' . $currentYear; // Format menjadi "Maret 2025"

        // Log untuk memverifikasi format periode
        Log::info('Periode Kinerja: ' . $currentPeriod);


        $romawiMonths = [
            '01' => 'I',
            '02' => 'II',
            '03' => 'III',
            '04' => 'IV',
            '05' => 'V',
            '06' => 'VI',
            '07' => 'VII',
            '08' => 'VIII',
            '09' => 'IX',
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII'
        ];
        $monthInRoman = $romawiMonths[$currentMonth];

        // Mengambil data kegiatan
        $kegiatan = DB::table('target_realisasi_satkers as trs')
            ->join('update_target_realisasis as ur', 'ur.id_target_realisasi', '=', 'trs.id')
            ->join('monitoring_kegiatans as mk', 'mk.id', '=', 'trs.id_monitoring_kegiatan')
            ->select('trs.kode_satuan_kerja', 'trs.target_satker', 'ur.realisasi_satker', 'ur.updated_at', 'mk.waktu_selesai', 'mk.kode_tim', 'ur.status')
            ->whereRaw('MONTH(mk.waktu_selesai) = ?', [now()->month])
            ->whereRaw('YEAR(mk.waktu_selesai) = ?', [now()->year])
            ->where('ur.status', 'diterima')
            ->whereIn('ur.id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('update_target_realisasis')
                    ->where('status', 'diterima')
                    ->groupBy('id_target_realisasi');
            })
            ->orderBy('ur.updated_at', 'desc')
            ->get();

        if ($kegiatan->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data kegiatan untuk dihitung.'], 400);
        }

        // Step 1: Menghitung nilai kegiatan
        $nilaiTimKerja = [];

        foreach ($kegiatan as $item) {
            $nilaiRealisasi = $this->hitungRealisasi($item->target_satker, $item->realisasi_satker);
            $nilaiWaktu = $this->hitungWaktu($item->target_satker, $item->realisasi_satker, $item->updated_at, $item->waktu_selesai);

            // Log untuk melihat nilai capaian realisasi dan ketepatan waktu
            Log::info("Nilai Capaian Realisasi: " . $nilaiRealisasi);
            Log::info("Nilai Ketepatan Waktu: " . $nilaiWaktu);

            // Nilai akhir untuk kegiatan
            $nilaiAkhirKegiatan = (0.7 * $nilaiRealisasi) + (0.3 * $nilaiWaktu);
            Log::info("Nilai Akhir Kegiatan: " . $nilaiAkhirKegiatan);

            // Simpan nilai kegiatan per tim kerja
            if (!isset($nilaiTimKerja[$item->kode_satuan_kerja][$item->kode_tim])) {
                $nilaiTimKerja[$item->kode_satuan_kerja][$item->kode_tim] = [];
            }
            $nilaiTimKerja[$item->kode_satuan_kerja][$item->kode_tim][] = $nilaiAkhirKegiatan;
        }


        // Step 3: Menghitung nilai rata-rata tim kerja per satuan kerja
        $nilaiSatuanKerja = [];

        foreach ($nilaiTimKerja as $kodeSatuanKerja => $timData) {
            $nilaiRataRataSatuanKerja = [];

            foreach ($timData as $kodeTim => $nilaiKegiatan) {
                $nilaiRataRataSatuanKerja[$kodeTim] = array_sum($nilaiKegiatan) / count($nilaiKegiatan);
            }

            // Menghitung rata-rata nilai kinerja seluruh tim di satuan kerja
            $nilaiSatuanKerja[$kodeSatuanKerja] = array_sum($nilaiRataRataSatuanKerja) / count($nilaiRataRataSatuanKerja);
        }

        // Update atau buat data penilaian untuk setiap satuan kerja
        foreach ($nilaiSatuanKerja as $kodeSatuanKerja => $nilaiAkhir) {
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
            Log::info('Peringkat diperbarui untuk satuan kerja:', [
                'kode_satuan_kerja' => $item->kode_satuan_kerja,
                'peringkat' => $item->peringkat
            ]);
        }

        // Generate nomor sertifikat untuk peringkat 1, 2, dan 3
        $topThreePenilaian = Penilaian::where('periode_kinerja', $currentPeriod)
            ->whereIn('peringkat', [1, 2, 3])
            ->orderBy('peringkat', 'asc')
            ->get();

        foreach ($topThreePenilaian as $index => $item) {
            $nomorSertifikat = ($index + 1) . '/BPS/KINERJA/' . $monthInRoman . '/' . $currentYear;

            // Cek sertifikat sudah ada
            $existingCertificate = Sertifikat::where('id_penilaian', $item->id)->first();
            if ($existingCertificate) {
                $existingCertificate->update(['nomor_sertifikat' => $nomorSertifikat]);
            } else {
                Sertifikat::create([
                    'id_penilaian' => $item->id,
                    'nomor_sertifikat' => $nomorSertifikat,
                ]);
            }
        }

        return response()->json(['message' => 'Penilaian berhasil digenerate dan dirangking untuk periode ini.']);
    }

    private function hitungRealisasi($target, $realisasi)
    {
        // Menghindari pembagian dengan 0
        if ($target <= 0) {
            return 0; // Jika target tidak valid, kembalikan nilai 0
        }

        // Menghitung persentase realisasi
        $persentase = ($realisasi / $target) * 100;

        // Pembulatan persentase untuk memastikan akurasi perhitungan
        $persentase = round($persentase, 2);

        // Memberikan nilai berdasarkan rentang persentase
        if ($persentase == 100) {
            return 4; // Jika realisasi mencapai atau melebihi target, nilai maksimal
        } elseif ($persentase >= 80) {
            return 3; // Jika realisasi 80% atau lebih
        } elseif ($persentase >= 60) {
            return 2; // Jika realisasi 60% atau lebih
        } else {
            return 1; // Jika realisasi kurang dari 60%
        }
    }

    private function hitungWaktu($target, $realisasi, $updatedAt, $waktuSelesai)
    {
        // Menghitung persentase realisasi terlebih dahulu
        $persentase = ($target > 0) ? ($realisasi / $target) * 100 : 0;

        // Cek apakah capaian sudah 100%
        if ($persentase == 100) {
            // Hitung hanya tanggal (tanpa memperhitungkan jam)
            $updatedAtDate = \Carbon\Carbon::parse($updatedAt)->startOfDay(); // Mengabaikan waktu dan hanya fokus pada tanggal
            $waktuSelesaiDate = \Carbon\Carbon::parse($waktuSelesai)->startOfDay();

            // Hitung selisih hari antara tanggal updated_at dan waktu_selesai
            $selisihHari = $updatedAtDate->diffInDays($waktuSelesaiDate, false);

            // Log untuk memverifikasi perhitungan selisih waktu dalam hari
            Log::info("Selisih waktu antara updated_at dan waktu_selesai dalam hari: " . $selisihHari);

            // Tentukan nilai berdasarkan selisih waktu
            if ($selisihHari >= 2) {
                return 4; // Capaian lebih dari 1 hari lebih cepat
            } elseif ($selisihHari == 1) {
                return 3; // Tepat pada waktu selesai
            } elseif ($selisihHari == 0) {
                return 2; // Selesai 1 hari lebih cepat
            } else {
                return 1; // Melewati batas waktu
            }
        } else {
            // Jika persentase tidak mencapai 100%, kembalikan nilai 1
            return 1;
        }
    }

    // Fungsi untuk mengubah nama bulan dalam bahasa Inggris menjadi bahasa Indonesia
    private function getNamaBulanIndo($bulan)
    {
        $bulanIndo = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];

        return $bulanIndo[$bulan] ?? $bulan; // Mengembalikan bulan dalam bahasa Indonesia
    }
}
