<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sertifikat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_sertifikat',
        'id_penilaian'
    ];

    // Relasi dengan model Penilaian
    public function penilaian()
    {
        return $this->belongsTo(Penilaian::class, 'id_penilaian', 'id');
    }
}
