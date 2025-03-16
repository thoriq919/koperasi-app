<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    //
    protected $fillable = [
        'nomor_surat', 'tujuan', 'perihal', 'tanggal_keluar', 'file_surat', 'status'
    ];
}
