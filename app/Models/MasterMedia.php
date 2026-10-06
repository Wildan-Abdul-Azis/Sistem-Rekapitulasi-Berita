<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterMedia extends Model
{
    use HasFactory;

    protected $table = 'master_media';
    protected $primaryKey = 'id_media';

    protected $fillable = [
        'nama_media',
        'jenis_media',
    ];

    /**
     * Relasi ke rekap berita.
     */
    public function rekapBerita(): HasMany
    {
        return $this->hasMany(RekapBerita::class, 'media_id', 'id_media');
    }
}
