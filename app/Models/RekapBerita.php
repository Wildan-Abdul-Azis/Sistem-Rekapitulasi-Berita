<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapBerita extends Model
{
    use HasFactory;

    protected $table = 'rekap_berita';
    protected $primaryKey = 'id_rekap';

    protected $fillable = [
        'nama_media',
        'tanggal_tayang',
        'tanggal_kegiatan',
        'judul_berita',
        'link_berita',
        'keterangan',
        'raw_text_ocr',
        'foto_kliping',
    ];

    protected $casts = [
        'tanggal_tayang' => 'date',
        'tanggal_kegiatan' => 'date',
        'foto_kliping' => 'array',
    ];
}
