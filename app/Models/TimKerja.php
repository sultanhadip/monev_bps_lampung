<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimKerja extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_tim'
    ];

    public function dataKegiatan()
    {
        return $this->hasMany(DataKegiatan::class, 'id_tim_kerja', 'id');
    }

    // Relasi dengan MonitoringKegiatan
    public function monitoringKegiatan()
    {
        return $this->hasMany(MonitoringKegiatan::class, 'id_data_kegiatan', 'id');
    }
}
