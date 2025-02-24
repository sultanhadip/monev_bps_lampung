<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SatuanKerja extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode_satuan_kerja',
        'nama_satuan_kerja'
    ];
}
