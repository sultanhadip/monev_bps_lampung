<?php

namespace App\Http\Controllers;

use App\Models\DataKegiatan;
use App\Models\MonitoringKegiatan;
use App\Models\target_realisasi_satker;
use App\Models\TimKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil tim yang memiliki kegiatan, dan tampilkan nama timnya dari relasi dengan tim_kerjas
        $timNames = TimKerja::pluck('nama_tim', 'id');  // Ambil nama_tim dari tabel tim_kerjas

        // Ambil semua data monitoring kegiatan
        $monitoringData = MonitoringKegiatan::with('datakegiatan')->get();

        return view('dashboard', [
            'timNames' => $timNames,  // Kirim data nama tim ke view
            'monitoringData' => $monitoringData, // Kirim data monitoring ke view
        ]);
    }


    public function getObjek(Request $request)
    {
        $timId = $request->get('tim_id');

        if (!$timId) {
            return response()->json([]);
        }

        // Ambil objek yang terkait dengan tim dari tabel DataKegiatan
        $objekList = DataKegiatan::where('id_tim_kerja', $timId)
            ->distinct()
            ->pluck('objek_kegiatan');

        Log::info('Objek List:', $objekList->toArray());

        return response()->json($objekList);
    }

    public function getPeriode(Request $request)
    {
        $timId = $request->get('tim_id');
        $objek = $request->get('objek');

        if (!$timId || !$objek) {
            return response()->json([]);
        }

        // Ambil periode kegiatan berdasarkan tim dan objek
        $periodeList = DataKegiatan::where('id_tim_kerja', $timId)
            ->where('objek_kegiatan', $objek)
            ->distinct()
            ->pluck('periode_kegiatan');

        Log::info('Periode List:', $periodeList->toArray());

        return response()->json($periodeList);
    }

    public function getNamaKegiatan(Request $request)
    {
        $timId = $request->get('tim_id');
        $objek = $request->get('objek');
        $periode = $request->get('periode');

        if (!$timId || !$objek || !$periode) {
            return response()->json([]);
        }

        // Ambil nama kegiatan berdasarkan tim, objek, dan periode
        $namaKegiatan = DataKegiatan::where('id_tim_kerja', $timId)
            ->where('objek_kegiatan', $objek)
            ->where('periode_kegiatan', $periode)
            ->distinct()
            ->pluck('nama_kegiatan', 'kode_kegiatan');

        Log::info('Nama Kegiatan:', $namaKegiatan->toArray());

        return response()->json($namaKegiatan);
    }

    public function getPeriodeKegiatan(Request $request)
    {
        $kodeKegiatan = $request->get('kode_kegiatan');

        if (!$kodeKegiatan) {
            return response()->json([]);
        }

        // Ambil data dari DataKegiatan berdasarkan kode_kegiatan
        $dataKegiatan = DataKegiatan::where('kode_kegiatan', $kodeKegiatan)
            ->with('monitoringKegiatan') // Memuat relasi monitoringKegiatan
            ->first(); // Mengambil satu data sesuai kode_kegiatan

        if (!$dataKegiatan) {
            return response()->json([
                'error' => 'Data kegiatan tidak ditemukan untuk kode kegiatan ini.'
            ], 404);
        }

        // Ambil data monitoring kegiatan yang terkait
        $monitoringKegiatan = $dataKegiatan->monitoringKegiatan;

        // Tentukan periode kegiatan dari data kegiatan
        $periodeKegiatan = $dataKegiatan->periode_kegiatan;

        // Format data untuk response
        $response = [
            'waktu_kegiatan' => [] // Menyimpan waktu kegiatan yang relevan
        ];

        // Tentukan format periode_kegiatan sesuai aturan yang diberikan
        foreach ($monitoringKegiatan as $item) {
            if ($item->tahun_kegiatan) {
                // Hanya menampilkan sesuai dengan periode yang relevan
                if ($periodeKegiatan == 'Bulanan' && $item->bulan) {
                    $item->periode_kegiatan = $item->bulan . ' ' . $item->tahun_kegiatan;
                    if (!in_array($item->periode_kegiatan, $response['waktu_kegiatan'])) {
                        $response['waktu_kegiatan'][] = $item->periode_kegiatan;
                    }
                } elseif ($periodeKegiatan == 'Tahunan') {
                    // Jika periode tahunan, tampilkan hanya tahun
                    $item->periode_kegiatan = $item->tahun_kegiatan;
                    if (!in_array($item->periode_kegiatan, $response['waktu_kegiatan'])) {
                        $response['waktu_kegiatan'][] = $item->periode_kegiatan;
                    }
                } elseif ($periodeKegiatan == 'Triwulan' && $item->triwulan) {
                    // Jika periode triwulan, tampilkan triwulan dan tahun
                    $item->periode_kegiatan = 'Triwulan ' . $item->triwulan . ' ' . $item->tahun_kegiatan;
                    if (!in_array($item->periode_kegiatan, $response['waktu_kegiatan'])) {
                        $response['waktu_kegiatan'][] = $item->periode_kegiatan;
                    }
                } elseif ($periodeKegiatan == 'Semesteran' && $item->semester) {
                    // Jika periode semesteran, tampilkan semester dan tahun
                    $item->periode_kegiatan = 'Semester ' . $item->semester . ' ' . $item->tahun_kegiatan;
                    if (!in_array($item->periode_kegiatan, $response['waktu_kegiatan'])) {
                        $response['waktu_kegiatan'][] = $item->periode_kegiatan;
                    }
                }
            }
        }

        // Menambahkan waktu kegiatan yang relevan ke response
        Log::info('Response Periode Kegiatan:', $response);

        return response()->json($response);
    }

    public function getFilteredData(Request $request)
    {
        $kodeKegiatan = $request->input('kode_kegiatan');
        $waktuKegiatan = $request->input('waktu_kegiatan');
        Log::info('Request Filtered Data:', ['waktu_kegiatan' => $waktuKegiatan]);
        Log::info('Request Filtered Data:', ['kode_kegiatan' => $kodeKegiatan]);

        // Memisahkan berdasarkan spasi
        $parts = explode(' ', $waktuKegiatan);
        $periode = null;
        $identifikasi = null;
        $bulan = null;
        $tahun = null;

        // Mengecek jumlah kata dan memisahkan
        if (count($parts) === 3) {
            list($periode, $identifikasi, $tahun) = $parts;  // Triwulan I 2020
        } elseif (count($parts) === 2) {
            list($bulan, $tahun) = $parts;  // Semester I 2020
        } else {
            list($tahun) = $parts;
        }
        $tahun = (int) $tahun;

        Log::info('Bulan:', ['bulan' => $bulan]);
        Log::info('Tahun:', ['tahun' => $tahun]);
        Log::info('Periode:', ['periode' => $periode]);
        Log::info('Identifikasi:', ['identifikasi' => $identifikasi]);

        if (!$kodeKegiatan) {
            return response()->json([
                'error' => 'Kode kegiatan tidak boleh kosong.',
            ], 400);
        }

        try {
            // Cari data kegiatan berdasarkan kode_kegiatan
            $dataKegiatan = DataKegiatan::with('targetRealisasiSatker.updateRealisasi', 'targetRealisasiSatker.satuankerja')
                ->where('kode_kegiatan', $kodeKegiatan)
                ->first();
            Log::info('Data slur', ['data ' => $dataKegiatan]);

            // Menghitung akumulasi target dari semua satuan kerja
            if ($bulan != null) {
                $monitoringSatker = MonitoringKegiatan::with('dataKegiatan')
                    ->where('id_data_kegiatan', $dataKegiatan->id,)->where('tahun_kegiatan', $tahun)->where("bulan", $bulan)
                    ->first();
            } else if ($periode == "Semester" and $identifikasi != null) {
                $monitoringSatker = MonitoringKegiatan::with('dataKegiatan')
                    ->where('id_data_kegiatan', $dataKegiatan->id,)->where('tahun_kegiatan', $tahun)->where("semester", $identifikasi)
                    ->first();
            } else if ($periode == "Triwulan" and $identifikasi != null) {
                $monitoringSatker = MonitoringKegiatan::with('dataKegiatan')
                    ->where('id_data_kegiatan', $dataKegiatan->id,)->where('tahun_kegiatan', $tahun)->where("triwulan", $identifikasi)
                    ->first();
            } else if ($periode == null and $bulan == null and $identifikasi == null) {
                $monitoringSatker = MonitoringKegiatan::with('dataKegiatan')
                    ->where('id_data_kegiatan', $dataKegiatan->id,)->where('tahun_kegiatan', $tahun)
                    ->first();
            }

            //  AMBIL TARGET REALISASI YANG ID
            // Menghitung akumulasi target dari semua satuan kerja
            $targetRealisasiSatker = target_realisasi_satker::with('updateRealisasi')
                ->where('id_monitoring_kegiatan', $monitoringSatker->id)
                ->get();
            Log::info('Data slur', ['data1' => $monitoringSatker]);
            Log::info('Data slur', ['data2' => $targetRealisasiSatker]);


            // Hitung total target dan realisasi
            $totalTarget = $targetRealisasiSatker->sum('target_satker');
            $totalRealisasi = $targetRealisasiSatker->sum(function ($item) {
                return $item->updateRealisasi->realisasi_satker ?? 0;
            });

            // Persentase data untuk chart
            $persentaseData = $targetRealisasiSatker->map(function ($item) {
                $realisasi = $item->updateRealisasi->realisasi_satker ?? 0;
                $persentase = $item->target_satker > 0 ? ($realisasi / $item->target_satker) * 100 : 0;
                return [
                    'nama' => $item->satuankerja?->nama_satuan_kerja ?? 'N/A',
                    'persentase' => round($persentase, 2),
                    'realisasi' => round($realisasi)
                ];
            });

            // Data tertinggi dan terendah
            $tertinggi = $persentaseData->sortByDesc('persentase')->first() ?? ['nama' => '-', 'persentase' => '-'];
            $terendah = $persentaseData->sortBy('persentase')->first() ?? ['nama' => '-', 'persentase' => '-'];

            return response()->json([
                'target' => $totalTarget,
                'realisasi' => $totalRealisasi,
                'persentase' => $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) . '%' : '0%',
                'tertinggi' => $tertinggi,
                'terendah' => $terendah,
                'chartCategories' => $persentaseData->pluck('nama')->toArray(),
                'chartTargetData' => $targetRealisasiSatker->pluck('target_satker')->toArray(),
                'chartRealisasiData' => $persentaseData->pluck('realisasi')->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching filtered data:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Terjadi kesalahan pada server.'], 500);
        }
    }
}
