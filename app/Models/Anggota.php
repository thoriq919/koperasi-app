<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    //
    protected $fillable = [
        'name', 'contact', 'address', 'wilayah_komda', 'tanggal_masuk', 'status'
    ];
}
