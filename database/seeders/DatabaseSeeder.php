<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat akun petugas default jika belum ada
        User::firstOrCreate(
            ['email' => 'admin@diskominfo.bogorkab.go.id'],
            [
                'name' => 'Petugas Kemitraan Diskominfo',
                'password' => bcrypt('diskominfo123'),
            ]
        );

        $this->call([
            MasterMediaSeeder::class,
        ]);
    }
}
