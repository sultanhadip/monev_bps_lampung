<?php

namespace App\Http\Controllers;

use App\Models\DataKegiatan;
use App\Models\MonitoringKegiatan;
use App\Models\target_realisasi_satker;
use App\Models\TimKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data tim
        $timNames = TimKerja::pluck('nama_tim', 'id');

        // Ambil semua data monitoring kegiatan beserta relasi datakegiatan
        $monitoringData = MonitoringKegiatan::with('datakegiatan')->get();

        // Total seluruh kegiatan survey
        $totalKegiatan = MonitoringKegiatan::count();

        // Dapatkan awal dan akhir bulan saat ini
        $startOfMonth = Carbon::now()->startOfMonth(); // Contoh: 2025-03-01 00:00:00
        $endOfMonth   = Carbon::now()->endOfMonth();   // Contoh: 2025-03-31 23:59:59

        // Ambil ID kegiatan yang berlangsung (overlap) di bulan ini
        $kegiatanIds = MonitoringKegiatan::where('waktu_mulai', '<=', $endOfMonth)
            ->where('waktu_selesai', '>=', $startOfMonth)
            ->pluck('id');

        // Hitung jumlah kegiatan yang berlangsung di bulan ini
        $totalKegiatanBulan = $kegiatanIds->count();

        // Hitung akumulasi target sampel untuk kegiatan yang berlangsung pada bulan ini
        $totalTargetSampel = DB::table('target_realisasi_satkers')
            ->whereIn('id', function ($query) use ($kegiatanIds) {
                // Ambil id target yang terkait dengan kegiatan yang sedang berlangsung
                $query->select('id')
                    ->from('target_realisasi_satkers')
                    ->whereIn('id_monitoring_kegiatan', $kegiatanIds);
            })
            ->sum('target_satker');

        // Hitung akumulasi realisasi sampel dengan update terakhir per target
        // Update_target_realisasis dihubungkan dengan target_realisasi_satkers melalui kolom id_target_realisasi
        $totalRealisasiSampel = DB::table('update_target_realisasis as utr')
            ->join(
                DB::raw("(SELECT id_target_realisasi, MAX(updated_at) as latest_update FROM update_target_realisasis GROUP BY id_target_realisasi) as latest"),
                function ($join) {
                    $join->on('utr.id_target_realisasi', '=', 'latest.id_target_realisasi')
                        ->on('utr.updated_at', '=', 'latest.latest_update');
                }
            )
            ->join('target_realisasi_satkers as trs', 'trs.id', '=', 'utr.id_target_realisasi')
            ->whereIn('trs.id_monitoring_kegiatan', $kegiatanIds)
            ->where('utr.status', 'diterima')
            ->sum('utr.realisasi_satker');

        return view('dashboard', [
            'timNames'              => $timNames,
            'monitoringData'        => $monitoringData,
            'totalKegiatan'         => $totalKegiatan,
            'totalKegiatanBulan'    => $totalKegiatanBulan,
            'totalTargetSampel'     => $totalTargetSampel,
            'totalRealisasiSampel'  => $totalRealisasiSampel,
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
            ->pluck('nama_kegiatan', 'id');

        Log::info('Nama Kegiatan:', $namaKegiatan->toArray());

        return response()->json($namaKegiatan);
    }

    public function getPeriodeKegiatan(Request $request)
    {
        $kodeKegiatan = $request->get('kode_kegiatan');
        if (! $kodeKegiatan) {
            return response()->json(['waktu_kegiatan' => []]);
        }

        // Peta nama bulan
        $mapBulan = [
            1  => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4  => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7  => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Ambil semua entry MonitoringKegiatan untuk kode ini
        $records = MonitoringKegiatan::where('kode_kegiatan', $kodeKegiatan)->get();

        $periodeList = [];
        $tipePeriode = optional(DataKegiatan::where('id', $kodeKegiatan)->first())
            ->periode_kegiatan;
        $tipePeriode = strtolower($tipePeriode);

        foreach ($records as $mk) {
            $dt    = Carbon::parse($mk->waktu_mulai);
            $month = $dt->month;
            $year  = $dt->year;

            switch ($tipePeriode) {
                case 'bulanan':
                    $label = "{$mapBulan[$month]} {$year}";
                    break;
                case 'triwulan':
                    $q = ceil($month / 3);
                    $label = "Triwulan {$q} {$year}";
                    break;
                case 'semesteran':
                    $sem = $month <= 6 ? 'I' : 'II';
                    $label = "Semester {$sem} {$year}";
                    break;
                default: // tahunan
                    $label = (string)$year;
            }

            if (! in_array($label, $periodeList)) {
                $periodeList[] = $label;
            }
        }

        return response()->json([
            'waktu_kegiatan' => $periodeList
        ]);
    }


    public function getFilteredData(Request $request)
    {
        $kodeKegiatan  = $request->input('kode_kegiatan');
        $waktuKegiatan = $request->input('waktu_kegiatan');

        if (! $kodeKegiatan || ! $waktuKegiatan) {
            return response()->json(['error' => 'Parameter tidak lengkap.'], 400);
        }

        // Mapping nama bulan ke angka
        $mapBulan = [
            'Januari'   => 1,
            'Februari' => 2,
            'Maret'     => 3,
            'April'     => 4,
            'Mei'      => 5,
            'Juni'      => 6,
            'Juli'      => 7,
            'Agustus'  => 8,
            'September' => 9,
            'Oktober'   => 10,
            'November' => 11,
            'Desember'  => 12,
        ];

        // Tentukan rentang tanggal berdasar format waktu_kegiatan
        $startDate = null;
        $endDate   = null;

        // 1) Bulanan: "Januari 2025"
        if (preg_match('/^(\p{L}+)\s+(\d{4})$/u', $waktuKegiatan, $m)) {
            [, $nmBulan, $th] = $m;
            $mo = $mapBulan[$nmBulan] ?? null;
            if ($mo) {
                $startDate = Carbon::create($th, $mo, 1)->startOfDay();
                $endDate   = (clone $startDate)->endOfMonth()->endOfDay();
            }
        }
        // 2) Triwulan: "Triwulan I 2025"
        elseif (preg_match('/^Triwulan\s+(I|II|III|IV)\s+(\d{4})$/i', $waktuKegiatan, $m)) {
            [, $rom, $th] = $m;
            $q = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4][strtoupper($rom)];
            $sm = ($q - 1) * 3 + 1;
            $em = $q * 3;
            $startDate = Carbon::create($th, $sm, 1)->startOfDay();
            $endDate   = Carbon::create($th, $em, 1)->endOfMonth()->endOfDay();
        }
        // 3) Semester: "Semester I 2025"
        elseif (preg_match('/^Semester\s+(I|II)\s+(\d{4})$/i', $waktuKegiatan, $m)) {
            [, $rom, $th] = $m;
            if (strtoupper($rom) === 'I') {
                $startDate = Carbon::create($th, 1, 1)->startOfDay();
                $endDate   = Carbon::create($th, 6, 1)->endOfMonth()->endOfDay();
            } else {
                $startDate = Carbon::create($th, 7, 1)->startOfDay();
                $endDate   = Carbon::create($th, 12, 1)->endOfMonth()->endOfDay();
            }
        }
        // 4) Tahunan: "2025"
        elseif (preg_match('/^(\d{4})$/', $waktuKegiatan, $m)) {
            $th = (int)$m[1];
            $startDate = Carbon::create($th, 1, 1)->startOfDay();
            $endDate   = Carbon::create($th, 12, 1)->endOfMonth()->endOfDay();
        }

        if (! $startDate || ! $endDate) {
            return response()->json(['error' => 'Format periode tidak valid.'], 400);
        }

        try {
            // Ambil satu MonitoringKegiatan yang kode dan waktu_mulainya masuk rentang
            $mk = MonitoringKegiatan::with([
                'targetRealisasiSatker.satuankerja',       // relasi ke SatuanKerja
                'targetRealisasiSatker.updateRealisasi'   // relasi ke update_target_realisasi
            ])
                ->where('kode_kegiatan', $kodeKegiatan)
                ->whereBetween('waktu_mulai', [$startDate, $endDate])
                ->firstOrFail();

            $targets     = $mk->targetRealisasiSatker;
            $totalTarget = $targets->sum('target_satker');
            $totalReal   = $targets->sum(function ($t) {
                $upd = $t->updateRealisasi()->where('status', 'diterima')->latest()->first();
                return $upd ? $upd->realisasi_satker : 0;
            });

            // Siapkan data persentase per satuan kerja
            $persData = $targets->map(function ($t) {
                $upd = $t->updateRealisasi()->where('status', 'diterima')->latest()->first();
                $real = $upd ? $upd->realisasi_satker : 0;
                $pct  = $t->target_satker > 0 ? ($real / $t->target_satker) * 100 : 0;
                return [
                    'nama'       => $t->satuankerja
                        ? "[{$t->satuankerja->kode_satuan_kerja}] {$t->satuankerja->nama_satuan_kerja}"
                        : 'N/A',
                    'realisasi'  => round($real),
                    'persentase' => round($pct, 2),
                ];
            });

            $highest = $persData->sortByDesc('persentase')->first() ?: ['nama' => '-', 'persentase' => '-'];
            $lowest  = $persData->sortBy('persentase')->first()     ?: ['nama' => '-', 'persentase' => '-'];

            return response()->json([
                'target'              => $totalTarget,
                'realisasi'           => $totalReal,
                'persentase'          => $totalTarget > 0 ? round($totalReal / $totalTarget * 100, 2) . '%' : '0%',
                'tertinggi'           => $highest,
                'terendah'            => $lowest,
                'chartCategories'     => $persData->pluck('nama')->toArray(),
                'chartTargetData'     => $targets->pluck('target_satker')->toArray(),
                'chartRealisasiData'  => $persData->pluck('realisasi')->toArray(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Tidak ada data untuk periode tersebut.'], 404);
        }
    }
}
