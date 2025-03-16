<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    //
    protected $fillable = [
        'nomor_surat', 'pengirim', 'perihal', 'tanggal_masuk', 'file_surat', 'status'
    ];
}
