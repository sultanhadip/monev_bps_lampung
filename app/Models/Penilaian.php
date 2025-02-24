<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_satuan_kerja',
        'periode_kinerja',
        'nilai_kinerja',
        'peringkat',
    ];

    // Relasi dengan SatuanKerja
    public function satuanKerja()
    {
        return $this->belongsTo(SatuanKerja::class, 'kode_satuan_kerja', 'id');
    }

    // Relasi dengan model Sertifikat
    public function sertifikat()
    {
        return $this->hasMany(Sertifikat::class, 'id_penilaian');
    }
}
