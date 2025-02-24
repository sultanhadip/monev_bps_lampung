<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    protected $table = 'users';

    protected $fillable = [
        'nama',
        'username',
        'password',
        'role',
        'kode_satuan_kerja',
        'kode_tim',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi dengan SatuanKerja
    public function satuanKerja()
    {
        return $this->belongsTo(SatuanKerja::class, 'kode_satuan_kerja', 'id');
    }

    // Relasi dengan TimKerja
    public function timkerja()
    {
        return $this->belongsTo(TimKerja::class, 'kode_tim', 'id');
    }
}
