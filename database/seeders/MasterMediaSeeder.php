<?php

namespace Database\Seeders;

use App\Models\MasterMedia;
use Illuminate\Database\Seeder;

class MasterMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mediaList = [
            ['nama_media' => 'Radar Bogor', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Harian Metropolitan', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Harian Pelita', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Inilah Koran', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Pikiran Rakyat', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Pos Kota', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Koran Sindo', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Kompas', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Republika', 'jenis_media' => 'Cetak'],
            ['nama_media' => 'Bogor Daily', 'jenis_media' => 'Online'],
            ['nama_media' => 'Radar Bogor Online', 'jenis_media' => 'Online'],
            ['nama_media' => 'Metropolitan Online', 'jenis_media' => 'Online'],
            ['nama_media' => 'Antara News Jawa Barat', 'jenis_media' => 'Online'],
            ['nama_media' => 'Detikcom', 'jenis_media' => 'Online'],
            ['nama_media' => 'TVRI Jawa Barat', 'jenis_media' => 'Elektronik'],
            ['nama_media' => 'RRI Pro 1 Bogor', 'jenis_media' => 'Elektronik'],
            ['nama_media' => 'Intel Media', 'jenis_media' => 'Online'],
            ['nama_media' => 'Intelmedia Updates', 'jenis_media' => 'Online'],
        ];

        foreach ($mediaList as $item) {
            MasterMedia::firstOrCreate(
                ['nama_media' => $item['nama_media']],
                ['jenis_media' => $item['jenis_media']]
            );
        }
    }
}
