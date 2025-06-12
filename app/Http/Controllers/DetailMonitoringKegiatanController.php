<?php

namespace App\Http\Controllers;

use App\Exports\RiwayatRealisasiExport;
use App\Models\MonitoringKegiatan;
use App\Models\target_realisasi_satker;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DetailMonitoringKegiatanController extends Controller
{
    public function show($id)
    {
        // 1) Ambil data MonitoringKegiatan
        $monitoringKegiatan = MonitoringKegiatan::with(['datakegiatan', 'timkerja'])->findOrFail($id);

        // 2) Ambil user dan kode satuan kerja user
        $user = Auth::user();
        $userKodeSatuanKerja = $user->kode_satuan_kerja ?? null;

        // 3) Ambil semua target realisasi (status 'diterima') tanpa limit
        $allTargetRealisasiSatker = target_realisasi_satker::with([
            'satuankerja',
            'updateRealisasi' => function ($query) {
                $query->where('status', 'diterima')
                    ->orderBy('created_at', 'desc');
            }
        ])
            ->where('id_monitoring_kegiatan', $monitoringKegiatan->id)
            ->get();

        // 4) Transformasi data ...
        $allTargetRealisasiSatker->transform(function ($item) {
            $updatesCollection = $item->updateRealisasi instanceof \Illuminate\Support\Collection
                ? $item->updateRealisasi
                : collect($item->updateRealisasi ? [$item->updateRealisasi] : []);

            if ($updatesCollection->isNotEmpty()) {
                $latestUpdate = $updatesCollection->first();
                $item->latest_updated_at = Carbon::parse($latestUpdate->created_at)->format('d-m-Y H:i');
            } else {
                $item->latest_updated_at = '-';
            }

            return $item;
        });

        // 5) Hitung persentase berdasarkan update terbaru
        $targetRealisasiSatker = $allTargetRealisasiSatker->transform(function ($item) {
            $firstUpdate = $item->updateRealisasi instanceof \Illuminate\Support\Collection
                ? $item->updateRealisasi->first()
                : $item->updateRealisasi;

            $realisasi  = $firstUpdate->realisasi_satker ?? 0;
            $target     = $item->target_satker ?? 0;
            $persentase = $target ? round(($realisasi / $target) * 100, 2) : 0;

            $item->persentase       = $persentase . '%';
            $item->realisasi_satker = $realisasi;
            return $item;
        });

        // 6) Ambil data target realisasi dengan update terbaru apapun statusnya
        $preTargetRealisasiSatker = target_realisasi_satker::with([
            'satuankerja',
            'updateRealisasi' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }
        ])
            ->where('id_monitoring_kegiatan', $monitoringKegiatan->id)
            ->get();

        // 7) Transformasi preTargetRealisasiSatker
        $preTargetRealisasiSatker->transform(function ($item) {
            $firstUpdate = $item->updateRealisasi instanceof \Illuminate\Support\Collection
                ? $item->updateRealisasi->first()
                : $item->updateRealisasi;

            $item->pre_realisasi_satker = $firstUpdate->realisasi_satker ?? 0;
            $item->pre_bukti_dukung     = $firstUpdate->bukti_dukung_realisasi ?? '';
            $item->pre_keterangan       = $firstUpdate->keterangan ?? '';
            $item->pre_pesan            = $firstUpdate->pesan ?? '-';
            $item->pre_status           = $firstUpdate->status ?? 'Belum ada usulan';

            return $item;
        });

        // **Filter data hanya jika user adalah operator**
        if (Gate::allows('isOperator')) {
            $targetRealisasiSatker = $targetRealisasiSatker->filter(function ($item) use ($userKodeSatuanKerja) {
                return ($item->satuankerja->id ?? null) == $userKodeSatuanKerja;
            })->values();

            $preTargetRealisasiSatker = $preTargetRealisasiSatker->filter(function ($item) use ($userKodeSatuanKerja) {
                return ($item->satuankerja->id ?? null) == $userKodeSatuanKerja;
            })->values();
        }
        // Jika user bukan operator, tidak dilakukan filter sehingga semua data tampil

        // 8) Hak akses (opsional)
        $canAccessPengajuanKeterangan = Gate::any([
            'isOperator'
        ]);

        $canAccessVerifikasi = Gate::any([
            'isAdminProv',
            'isAdmin'
        ]);

        // Return view dengan data sudah terfilter sesuai role
        return view('detail-monitoring-kegiatan', compact(
            'monitoringKegiatan',
            'targetRealisasiSatker',
            'preTargetRealisasiSatker',
            'allTargetRealisasiSatker',
            'canAccessPengajuanKeterangan',
            'canAccessVerifikasi',
            'userKodeSatuanKerja'
        ));
    }

    public function getRiwayatRealisasi($satuanKerjaId, $monitoringKegiatanId)
    {
        try {
            \Log::info("Fetching data for Satuan Kerja ID: $satuanKerjaId and Monitoring Kegiatan ID: $monitoringKegiatanId");

            $monitoringKegiatan = MonitoringKegiatan::whereHas('targetRealisasiSatker', function ($query) use ($satuanKerjaId, $monitoringKegiatanId) {
                $query->where('kode_satuan_kerja', $satuanKerjaId)
                    ->where('id_monitoring_kegiatan', $monitoringKegiatanId);
            })->first();

            if (!$monitoringKegiatan) {
                \Log::error("Monitoring Kegiatan tidak ditemukan untuk Satuan Kerja ID: $satuanKerjaId dan Kegiatan ID: $monitoringKegiatanId");
                return response()->json(['error' => 'Data monitoring tidak ditemukan'], 404);
            }

            $waktuMulai = Carbon::parse($monitoringKegiatan->waktu_mulai)->startOfDay();
            $waktuSelesai = Carbon::parse($monitoringKegiatan->waktu_selesai)->endOfDay();

            // Buat daftar bulan dengan format nama bulan lengkap + tahun, contoh: May 2025
            $bulanOptions = [];
            $currentDate = $waktuMulai->copy();
            while ($currentDate->lte($waktuSelesai)) {
                $bulanOptions[] = $currentDate->format('F Y'); // Full month name
                $currentDate->addMonth();
            }

            \Log::info('Bulan Options yang dikirim:', $bulanOptions);

            $riwayatRealisasi = target_realisasi_satker::with([
                'updateRealisasi' => function ($query) {
                    $query->where('status', 'diterima')->orderBy('created_at', 'asc');
                }
            ])
                ->where('kode_satuan_kerja', $satuanKerjaId)
                ->where('id_monitoring_kegiatan', $monitoringKegiatanId)
                ->first();

            if (!$riwayatRealisasi) {
                \Log::warning("Riwayat realisasi tidak ditemukan untuk Satuan Kerja ID: $satuanKerjaId dan Kegiatan ID: $monitoringKegiatanId");
                return response()->json(['updates' => [], 'bulanOptions' => $bulanOptions]);
            }

            $updates = [];
            $akumulasi = 0;
            $targetTotal = $riwayatRealisasi->target_satker ?? 1;

            // Group updateRealisasi per tanggal
            $realisasiPerHari = collect($riwayatRealisasi->updateRealisasi)->groupBy(function ($update) {
                return Carbon::parse($update->created_at)->format('Y-m-d');
            });

            $previousRealisasi = 0;

            // Loop dari tgl mulai sampai tgl selesai, bikin array update lengkap
            for ($date = $waktuMulai; $date->lte($waktuSelesai); $date->addDay()) {
                $formattedDate = $date->format('d-m-Y');
                $realisasiHarian = 0;

                if ($realisasiPerHari->has($date->format('Y-m-d'))) {
                    $updatesForDay = $realisasiPerHari[$date->format('Y-m-d')];
                    $latestUpdate = $updatesForDay->max('realisasi_satker');

                    $realisasiHarian = $latestUpdate - $previousRealisasi;
                    if ($realisasiHarian < 0) {
                        $realisasiHarian = 0;
                    }
                }

                $previousRealisasi = $realisasiHarian == 0 ? $previousRealisasi : $latestUpdate ?? $previousRealisasi;
                $akumulasi += $realisasiHarian;
                $persentase = ($akumulasi / max($targetTotal, 1)) * 100;

                $updates[] = [
                    'tanggal' => $formattedDate,
                    'realisasi_harian' => $realisasiHarian,
                    'akumulasi' => $akumulasi,
                    'persentase' => number_format($persentase, 2) . '%',
                    'target_satker' => $targetTotal,
                ];
            }

            return response()->json([
                'updates' => $updates,
                'bulanOptions' => $bulanOptions,
                'riwayatRealisasi' => $riwayatRealisasi,
            ]);
        } catch (\Exception $e) {
            \Log::error("Terjadi kesalahan: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function exportRiwayatRealisasi($monitoringKegiatanId, $satuanKerjaId)
    {
        \Log::info("Exporting data for Satuan Kerja ID: " . $satuanKerjaId);

        $monitoringKegiatan = MonitoringKegiatan::findOrFail($monitoringKegiatanId);

        $riwayatRealisasi = $this->getRiwayatRealisasiData($monitoringKegiatanId, $satuanKerjaId);

        \Log::info('Riwayat Realisasi Data:', $riwayatRealisasi->toArray());

        return Excel::download(new RiwayatRealisasiExport($riwayatRealisasi, $monitoringKegiatan->waktu_mulai, $monitoringKegiatan->waktu_selesai), 'riwayat_realisasi.xlsx');
    }

    private function getRiwayatRealisasiData($monitoringKegiatanId, $satuanKerjaId)
    {
        \Log::info("Fetching data for Satuan Kerja ID: " . $satuanKerjaId);

        $riwayatRealisasi = target_realisasi_satker::with(['updateRealisasi' => function ($query) {
            $query->where('status', 'diterima')->orderBy('created_at', 'asc');
        }])
            ->where('id_monitoring_kegiatan', $monitoringKegiatanId)
            ->where('kode_satuan_kerja', $satuanKerjaId)
            ->get();

        \Log::info("Riwayat Realisasi for Satuan Kerja ID {$satuanKerjaId}:", $riwayatRealisasi->toArray());

        return $riwayatRealisasi;
    }

    public function getPendingVerifikasiNotifications()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'pending' => false,
                'count' => 0,
                'notifications' => [],
            ]);
        }

        // Ambil semua update_target_realisasi yang menunggu verifikasi tanpa filter user role
        $notifications = \App\Models\update_target_realisasi::whereRaw('LOWER(status) = ?', ['menunggu verifikasi'])
            ->with(['targetRealisasiSatker' => function ($query) {
                $query->with('satuankerja', 'monitoringKegiatan.datakegiatan');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Daftar nama bulan untuk periode bulanan, semestern, dll
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

        // Mapping data notifikasi dan menghitung periode
        $data = $notifications->map(function ($item) use ($bulanNames) {
            $trs = $item->targetRealisasiSatker;

            // Perhitungan periode berdasarkan waktu_mulai
            $dt    = Carbon::parse($trs->monitoringKegiatan->waktu_mulai);
            $mIdx  = $dt->month - 1; // Indeks bulan (0-11)
            $yr    = $dt->year;
            $tipe  = $trs->monitoringKegiatan->datakegiatan->periode_kegiatan ?? 'Tahunan';

            switch (strtolower($tipe)) {
                case 'bulanan':
                    $periode = $bulanNames[$mIdx] . " {$yr}";
                    break;
                case 'triwulan':
                    $q = ceil(($mIdx + 1) / 3);
                    $periode = "Triwulan {$q} {$yr}";
                    break;
                case 'semesteran':
                    $s = ($mIdx < 6) ? 'I' : 'II';
                    $periode = "Semester {$s} {$yr}";
                    break;
                default: // tahunan
                    $periode = (string)$yr;
            }

            return [
                'id_update_realisasi' => $item->id,
                'id_monitoring_kegiatan' => $trs->id_monitoring_kegiatan ?? null,
                'nama_kegiatan' => $trs->monitoringKegiatan->datakegiatan->nama_kegiatan ?? 'N/A',
                'periode_kegiatan' => $periode, // Menambahkan periode kegiatan
                'nama_satuan_kerja' => $trs->satuankerja->nama_satuan_kerja ?? 'N/A',
                'created_at' => $item->created_at->format('d-m-Y H:i'),
            ];
        });

        return response()->json([
            'pending' => $notifications->count() > 0,
            'count' => $notifications->count(),
            'notifications' => $data,
        ]);
    }
}
