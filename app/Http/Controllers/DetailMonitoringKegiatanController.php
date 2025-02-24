<?php

namespace App\Http\Controllers;

use App\Models\MonitoringKegiatan;
use App\Models\target_realisasi_satker;
use Illuminate\Support\Facades\Gate;
use Log;

class DetailMonitoringKegiatanController extends Controller
{
    public function show($id)
    {
        $monitoringKegiatan = MonitoringKegiatan::with(['datakegiatan', 'timkerja'])->findOrFail($id);

        $targetRealisasiSatker = target_realisasi_satker::with(['satuankerja', 'updateRealisasi' => function ($query) {
            $query->where('status', 'diterima')->orderBy('created_at', 'desc')->limit(1);
        }])
            ->where('id_monitoring_kegiatan', $monitoringKegiatan->id)
            ->get();

        $preTargetRealisasiSatker = target_realisasi_satker::with(['satuankerja', 'updateRealisasi' => function ($query) {
            $query->where('status', 'menunggu verifikasi')->orderBy('created_at', 'desc')->limit(1);
        }])
            ->where('id_monitoring_kegiatan', $monitoringKegiatan->id)
            ->get();

        $targetRealisasiSatker->transform(function ($item) {
            $realisasi = $item->updateRealisasi->realisasi_satker ?? 0;
            $target = $item->target_satker ?? 0;
            $persentase = $target ? round(($realisasi / $target) * 100, 2) : 0;

            $item->persentase = $persentase . '%';
            $item->realisasi_satker = $realisasi;
            return $item;
        });

        $preTargetRealisasiSatker->transform(function ($item) {
            $item->pre_realisasi_satker = $item->updateRealisasi->realisasi_satker ?? 0;
            $item->pre_bukti_dukung = $item->updateRealisasi->bukti_dukung_realisasi ?? '';
            $item->pre_keterangan = $item->updateRealisasi->keterangan ?? '';
            return $item;
        });

        $canAccessPengajuanKeterangan = Gate::any([
            'isNeracaKaKo',
            'isSosialKaKo',
            'isProduksiKaKo',
            'isDistribusiKaKo',
            'isIPDSKaKo'
        ]);

        $canAccessVerifikasi = Gate::any([
            'isNeracaProv',
            'isSosialProv',
            'isProduksiProv',
            'isDistribusiProv',
            'isIPDSProv',
            'isAdmin'
        ]);

        return view('detail-monitoring-kegiatan', compact(
            'monitoringKegiatan',
            'targetRealisasiSatker',
            'preTargetRealisasiSatker',
            'canAccessPengajuanKeterangan',
            'canAccessVerifikasi'
        ));
    }
}
