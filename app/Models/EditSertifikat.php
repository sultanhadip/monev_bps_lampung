<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditSertifikat extends Model
{
    protected $table = 'edit_sertifikats';
    protected $fillable = ['nama', 'jabatan'];
}
