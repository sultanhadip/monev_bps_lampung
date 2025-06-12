<?php

namespace App\Exports;

use App\Models\target_realisasi_satker;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RealisasiExport implements FromCollection, WithHeadings
{
    protected $id_kegiatan;

    // Menambahkan konstruktor untuk menerima id_kegiatan
    public function __construct($id_kegiatan)
    {
        $this->id_kegiatan = $id_kegiatan;
    }

    public function collection()
    {
        // Melakukan join antar tabel dan memilih data terbaru dengan status diterima untuk setiap satuan kerja
        return DB::table('target_realisasi_satkers')
            ->join('satuan_kerjas', 'satuan_kerjas.id', '=', 'target_realisasi_satkers.kode_satuan_kerja')
            ->join('update_target_realisasis', 'update_target_realisasis.id_target_realisasi', '=', 'target_realisasi_satkers.id')
            ->join('monitoring_kegiatans', 'monitoring_kegiatans.id', '=', 'target_realisasi_satkers.id_monitoring_kegiatan') // Join dengan monitoring_kegiatans
            ->select(
                DB::raw('CONCAT("[", satuan_kerjas.kode_satuan_kerja, "]", satuan_kerjas.nama_satuan_kerja) as satuan_kerja'), // Menggabungkan kode_satuan_kerja dan nama_satuan_kerja
                'target_realisasi_satkers.target_satker',
                'update_target_realisasis.realisasi_satker',
                DB::raw('(update_target_realisasis.realisasi_satker / target_realisasi_satkers.target_satker) * 100 as persentase'),
                'update_target_realisasis.created_at'
            )
            ->where('update_target_realisasis.status', 'diterima')
            ->whereIn('update_target_realisasis.id', function ($query) {
                $query->select(DB::raw('max(id)'))
                    ->from('update_target_realisasis')
                    ->where('update_target_realisasis.status', 'diterima')
                    ->groupBy('id_target_realisasi');
            })
            ->where('monitoring_kegiatans.id', $this->id_kegiatan) // Menggunakan id_kegiatan dari tabel monitoring_kegiatans
            ->get();
    }

    public function headings(): array
    {
        return [
            'Satuan Kerja',
            'Target',
            'Realisasi',
            'Persentase',
            'Tanggal Update'
        ];
    }
}
