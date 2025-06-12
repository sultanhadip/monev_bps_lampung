<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataKegiatan extends Model
{
    use HasFactory;

    protected $table = 'data_kegiatans'; // Nama tabel di database

    protected $fillable = [
        'nama_kegiatan',
        'objek_kegiatan',
        'periode_kegiatan',
        'id_tim_kerja'
    ];

    // Relasi dengan TimKerja
    public function timKerja()
    {
        return $this->belongsTo(TimKerja::class, 'id_tim_kerja', 'id');
    }

    // Relasi dengan MonitoringKegiatan
    public function monitoringKegiatan()
    {
        return $this->hasMany(MonitoringKegiatan::class, 'id_data_kegiatan', 'id');
    }

    // Relasi dengan target_realisasi_satker
    public function targetRealisasiSatker()
    {
        return $this->hasMany(target_realisasi_satker::class, 'id_monitoring_kegiatan', 'id');
    }
}
