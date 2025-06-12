<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class target_realisasi_satker extends Model
{
    use HasFactory;

    protected $table = 'target_realisasi_satkers'; // Nama tabel sesuai di database

    protected $fillable = [
        'id_monitoring_kegiatan',
        'kode_satuan_kerja',
        'target_satker',
    ];

    // Relasi dengan update_target_realisasi
    public function updateRealisasi()
    {
        return $this->hasMany(update_target_realisasi::class, 'id_target_realisasi', 'id');
    }

    public function satuankerja()
    {
        return $this->belongsTo(SatuanKerja::class, 'kode_satuan_kerja', 'id');
    }

    public function dataKegiatan()
    {
        return $this->belongsTo(DataKegiatan::class, 'id_data_kegiatan', 'id');
    }

    public function monitoringKegiatan()
    {
        return $this->belongsTo(MonitoringKegiatan::class, 'id_monitoring_kegiatan', 'id');
    }
}
