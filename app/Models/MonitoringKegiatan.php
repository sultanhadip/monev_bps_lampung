<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringKegiatan extends Model
{
    use HasFactory;

    protected $table = 'monitoring_kegiatans';

    protected $fillable = [
        'id_data_kegiatan',
        'kode_tim',
        'kode_kegiatan',
        'tahun_kegiatan',
        'bulan',
        'triwulan',
        'semester',
        'waktu_mulai',
        'waktu_selesai'
    ];

    // Relasi dengan DataKegiatan
    public function datakegiatan()
    {
        return $this->belongsTo(DataKegiatan::class, 'id_data_kegiatan', 'id');
    }

    // Relasi dengan target_realisasi_satker
    public function targetRealisasiSatker()
    {
        return $this->hasMany(target_realisasi_satker::class, 'id_monitoring_kegiatan', 'id');
    }

    // Relasi dengan TimKerja
    public function timkerja()
    {
        return $this->belongsTo(TimKerja::class, 'kode_tim', 'id');
    }

    // Relasi dengan update_target_realisasi melalui target_realisasi_satker
    public function updateRealisasi()
    {
        return $this->hasManyThrough(
            update_target_realisasi::class,
            target_realisasi_satker::class,
            'id_monitoring_kegiatan', // FK pada target_realisasi_satker
            'id_target_realisasi', // FK pada update_target_realisasi
            'id', // PK pada monitoring_kegiatans
            'id' // PK pada target_realisasi_satker
        );
    }
}
