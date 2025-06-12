<?php

namespace App\Http\Controllers;

use App\Imports\MonitoringKegiatanImport;
use App\Imports\MultiSheetImport;
use App\Models\MonitoringKegiatan;
use App\Models\SatuanKerja;
use App\Models\TimKerja;
use App\Models\DataKegiatan;
use App\Models\target_realisasi_satker;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringKegiatanController extends Controller
{
    public function index(Request $request)
    {
        // ambil filter tahun dan bulan
        $filterBulan = $request->input('filter_bulan');
        $filterTahun = $request->input('filter_tahun');

        // daftar tahun unik untuk dropdown
        $years = MonitoringKegiatan::selectRaw('YEAR(waktu_mulai) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // cek akses verifikasi
        $canAccessVerifikasi = Gate::any(['isAdminProv', 'isAdmin']);

        // ambil data master
        $satuankerja  = SatuanKerja::all();
        $timkerja     = TimKerja::whereHas('datakegiatan')->get();
        $datakegiatan = DataKegiatan::all();

        // pagination & search
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        // tim kerjanya user (operator/provinsi)
        $timKerja = Auth::user()->timkerja
            ? Auth::user()->timkerja->nama_tim
            : null;

        // bangun query utama
        $qb = MonitoringKegiatan::with(['timkerja', 'datakegiatan', 'targetRealisasiSatker'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('timkerja', function ($q2) use ($search) {
                    $q2->where('nama_tim', 'like', "%{$search}%");
                })
                    ->orWhereHas('datakegiatan', function ($q2) use ($search) {
                        $q2->where('nama_kegiatan', 'like', "%{$search}%");
                    });
            })
            ->when(Gate::allows('isOperator') || Gate::allows('isAdminProv'), function ($q) use ($timKerja) {
                if ($timKerja) {
                    $q->whereHas('timkerja', function ($q2) use ($timKerja) {
                        $q2->where('nama_tim', 'like', "%{$timKerja}%");
                    });
                }
            })
            ->when($filterTahun, function ($q) use ($filterTahun) {
                $q->whereYear('waktu_mulai', $filterTahun);
            })
            ->when($filterBulan, fn($q) => $q->whereMonth('waktu_mulai', $filterBulan));

        // paginate & append query string
        $monitoringKegiatan = $qb
            ->paginate($perPage)
            ->appends([
                'search'       => $search,
                'per_page'     => $perPage,
                'filter_tahun' => $filterTahun,
                'filter_bulan' => $filterBulan,
            ]);

        // properti bantu
        $currentDate = Carbon::now();
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

        foreach ($monitoringKegiatan as $item) {
            $mulai = Carbon::parse($item->waktu_mulai)->startOfDay();
            $selesai = Carbon::parse($item->waktu_selesai)->endOfDay();

            $item->waktu_kegiatan = $mulai->format('d-m-Y') . ' - ' . $selesai->format('d-m-Y');
            $item->waktu_selesai  = $selesai->format('d-m-Y');

            if ($currentDate->lt($mulai)) {
                $item->status = 'BELUM DIMULAI';
            } elseif ($currentDate->gt($selesai)) {
                $item->status = 'SELESAI';
            } else {
                $item->status = 'ON PROGRESS';
            }

            $trs = target_realisasi_satker::with('updateRealisasi')
                ->where('id_monitoring_kegiatan', $item->id)
                ->get();

            $item->target = $trs->sum('target_satker');

            $item->realisasi = $trs->sum(function ($satker) {
                $u = $satker->updateRealisasi()
                    ->where('status', 'diterima')
                    ->latest()
                    ->first();
                return $u ? $u->realisasi_satker : 0;
            });

            $item->persentase = $item->target > 0
                ? round($item->realisasi / $item->target * 100, 2) . '%'
                : '0%';

            $dt = Carbon::parse($item->waktu_mulai);
            $mIdx = $dt->month - 1;
            $yr = $dt->year;
            $tipe = $item->datakegiatan->periode_kegiatan ?? 'Tahunan';

            switch (strtolower($tipe)) {
                case 'bulanan':
                    $item->periode_kegiatan = $bulanNames[$mIdx] . " {$yr}";
                    break;
                case 'triwulan':
                    $q = ceil(($mIdx + 1) / 3);
                    $item->periode_kegiatan = "Triwulan {$q} {$yr}";
                    break;
                case 'semesteran':
                    $s = ($mIdx < 6) ? 'I' : 'II';
                    $item->periode_kegiatan = "Semester {$s} {$yr}";
                    break;
                default:
                    $item->periode_kegiatan = (string)$yr;
            }
        }

        if ($request->ajax()) {
            // Pastikan variabel master tetap diambil
            $timkerja = TimKerja::whereHas('datakegiatan')->get();
            $datakegiatan = DataKegiatan::all();
            $satuankerja = SatuanKerja::all();  // <== Tambahkan ini!

            $tableHtml = view('monitoringkegiatan.table', compact(
                'monitoringKegiatan',
                'canAccessVerifikasi',
                'timkerja',
                'datakegiatan',
                'satuankerja'  // <== Kirim ke view juga
            ))->render();

            $paginationHtml = view('monitoringkegiatan.pagination', compact('monitoringKegiatan'))->render();

            $showingText = 'Showing ' . $monitoringKegiatan->firstItem() . '-' . $monitoringKegiatan->lastItem() . ' of ' . $monitoringKegiatan->total() . ' records';

            return response()->json([
                'table' => $tableHtml,
                'paginationHtml' => $paginationHtml,
                'showingText' => $showingText,
            ]);
        }

        // Render view full untuk request biasa
        return view('monitoring-kegiatan', compact(
            'satuankerja',
            'timkerja',
            'datakegiatan',
            'monitoringKegiatan',
            'canAccessVerifikasi',
            'years',
            'filterTahun',
            'filterBulan'
        ));
    }

    public function getKegiatanByTim(Request $request)
    {
        $tim_id = $request->input('tim_id');

        // Ambil data kegiatan berdasarkan id_tim_kerja
        $kegiatan = DataKegiatan::where('id_tim_kerja', $tim_id)->get();

        // Debugging: Log ID tim yang dipilih dan data kegiatan yang diterima
        Log::info('Tim ID:', ['tim_id' => $tim_id]);
        Log::info('Kegiatan yang terkait dengan tim ini:', $kegiatan->toArray());

        if ($kegiatan->isEmpty()) {
            return response()->json(['message' => 'Tidak ada kegiatan untuk tim ini']);
        }

        return response()->json($kegiatan);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_tim'       => 'required',
            'kode_kegiatan'  => 'required',
            'waktu_mulai'    => 'required|date',
            'waktu_selesai'  => 'required|date|after_or_equal:waktu_mulai',
            'satuan_kerja'   => 'required|array',
            'target_sampel'  => 'required|array',
        ]);

        // Definisi nama bulan untuk label duplikat
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

        // Ambil tipe periode dari DataKegiatan
        $dataKeg = DataKegiatan::findOrFail($validated['kode_kegiatan']);
        $tipe   = strtolower($dataKeg->periode_kegiatan);
        $start  = Carbon::parse($validated['waktu_mulai']);
        $month  = $start->month;
        $year   = $start->year;

        // Cek duplikat dengan filter kode_kegiatan
        switch ($tipe) {
            case 'bulanan':
                $exists = MonitoringKegiatan::whereMonth('waktu_mulai', $month)
                    ->whereYear('waktu_mulai', $year)
                    ->where('kode_kegiatan', $dataKeg->id)  // **Perbaikan di sini**
                    ->exists();
                $label = "{$bulanNames[$month - 1]} {$year}";
                break;
            case 'triwulan':
                $q = ceil($month / 3);
                $range = [($q - 1) * 3 + 1, $q * 3];
                $exists = MonitoringKegiatan::whereYear('waktu_mulai', $year)
                    ->whereBetween(DB::raw('MONTH(waktu_mulai)'), $range)
                    ->where('kode_kegiatan', $dataKeg->id)  // **Perbaikan di sini**
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
                    ->where('kode_kegiatan', $dataKeg->id)  // **Perbaikan di sini**
                    ->exists();
                $label = "Semester {$sem} {$year}";
                break;
            default: // tahunan
                $exists = MonitoringKegiatan::whereYear('waktu_mulai', $year)
                    ->where('kode_kegiatan', $dataKeg->id)  // **Perbaikan di sini**
                    ->exists();
                $label = (string)$year;
        }

        if (!empty($exists)) {
            return redirect()->route('monitoring-kegiatan')
                ->with('error', "Kegiatan untuk periode {$label} sudah ada.");
        }

        // Simpan data
        try {
            $mk = MonitoringKegiatan::create([
                'kode_tim'       => $validated['kode_tim'],
                'kode_kegiatan'  => $validated['kode_kegiatan'],
                'waktu_mulai'    => $validated['waktu_mulai'],
                'waktu_selesai'  => $validated['waktu_selesai'],
                'realisasi_kegiatan' => 0,
            ]);

            foreach ($validated['satuan_kerja'] as $idSatker) {
                target_realisasi_satker::create([
                    'id_monitoring_kegiatan' => $mk->id,
                    'kode_satuan_kerja'      => $idSatker,
                    'target_satker'          => $validated['target_sampel'][$idSatker] ?? 0,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('Error saat menyimpan Monitoring Kegiatan', ['error' => $th->getMessage()]);
            return back()->with('error', 'Gagal menyimpan data');
        }

        return redirect()->route('monitoring-kegiatan')
            ->with('success', 'Data berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'kode_tim' => 'required',
                'kode_kegiatan' => 'required',
                'waktu_mulai' => 'required|date',
                'waktu_selesai' => 'required|date|after_or_equal:waktu_mulai',
                'target_sampel' => 'required|array',
            ]);

            $monitoringKegiatan = MonitoringKegiatan::findOrFail($id);
            $monitoringKegiatan->update([
                'kode_tim' => $validatedData['kode_tim'],
                'kode_kegiatan' => $validatedData['kode_kegiatan'],
                'waktu_mulai' => $validatedData['waktu_mulai'],
                'waktu_selesai' => $validatedData['waktu_selesai'],
            ]);

            foreach ($validatedData['target_sampel'] as $satuanKerjaId => $target) {
                if ($target > 0) {
                    target_realisasi_satker::updateOrCreate(
                        [
                            'id_monitoring_kegiatan' => $monitoringKegiatan->id,
                            'kode_satuan_kerja' => $satuanKerjaId,
                        ],
                        [
                            'target_satker' => $target,
                        ]
                    );
                }
            }

            return redirect()->route('monitoring-kegiatan')->with('success', 'Data berhasil diperbarui');
        } catch (\Throwable $th) {
            Log::error('Error saat memperbarui Monitoring Kegiatan', ['error' => $th->getMessage()]);
            return redirect()->route('monitoring-kegiatan')->with('error', 'Terjadi kesalahan saat memperbarui data');
        }
    }

    public function destroy($id)
    {
        $monitoringkegiatan = MonitoringKegiatan::findOrFail($id);
        $monitoringkegiatan->delete();

        return redirect()->route('monitoring-kegiatan')->with('success', 'Kegiatan berhasil dihapus');
    }

    public function downloadFormat()
    {
        $filePath = base_path('app/Imports/format_monitoring_kegiatan.xlsx');
        return response()->download($filePath);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xls,xlsx',
        ]);

        try {
            // Buat instance import agar bisa akses hasil import
            $import = new MultiSheetImport();  // Ganti dengan MonitoringKegiatanImport jika perlu
            Excel::import($import, $request->file('excel_file'));

            // Ambil data hasil import
            $successCount = (method_exists($import, 'getSuccessCount') && $import->getSuccessCount() !== null)
                ? $import->getSuccessCount()
                : 0;
            $duplicateRows = (method_exists($import, 'getDuplicateRows'))
                ? $import->getDuplicateRows()
                : [];

            // Pesan sukses hanya jika ada yang berhasil disimpan
            $successMsg = $successCount > 0
                ? "{$successCount} kegiatan berhasil disimpan."
                : '';

            // Siapkan redirect
            $redirect = redirect()->route('monitoring-kegiatan');

            // Cek jika semua baris duplikat
            if (count($duplicateRows) > 0 && $successCount == 0) {
                $lines = array_map(function ($row) {
                    return "- Kegiatan {$row['nama_kegiatan']} pada periode {$row['periode']}";
                }, $duplicateRows);
                $duplicateMsg = "Semua kegiatan sudah ditambahkan\n" . implode("\n", $lines);

                // Hanya tampilkan pesan warning duplikat
                return $redirect->with('duplicate_errors', $duplicateMsg);
            }

            // Jika ada data yang berhasil disimpan
            if ($successCount > 0) {
                // Menambahkan pesan sukses jika ada yang berhasil disimpan
                $redirect = $redirect->with('success', $successMsg);
            }

            // Tampilkan pesan duplikat jika ada
            if (count($duplicateRows) > 0) {
                $lines = array_map(function ($row) {
                    return "- Kegiatan {$row['nama_kegiatan']} pada periode {$row['periode']}";
                }, $duplicateRows);
                $duplicateMsg = "Kegiatan duplikat ditemukan:\n" . implode("\n", $lines);
                $redirect = $redirect->with('duplicate_errors', $duplicateMsg);
            }

            return $redirect;
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengimpor data Excel: ' . $e->getMessage());
            return redirect()->route('monitoring-kegiatan')->with('error', 'Terjadi kesalahan saat mengimpor data!');
        }
    }
}
