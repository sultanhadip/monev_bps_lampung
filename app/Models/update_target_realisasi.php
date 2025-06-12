<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class update_target_realisasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_target_realisasi',
        'realisasi_satker',
        'bukti_dukung_realisasi',
        'keterangan',
        'pesan',
        'status'
    ];

    // Relasi dengan target_realisasi_satker
    public function targetRealisasiSatker()
    {
        return $this->belongsTo(target_realisasi_satker::class, 'id_target_realisasi', 'id')
            ->withDefault(); // Tambahkan default untuk menghindari error jika relasi tidak ditemukan
    }
}
