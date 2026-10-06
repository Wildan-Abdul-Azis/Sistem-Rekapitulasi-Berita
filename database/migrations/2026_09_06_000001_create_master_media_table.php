<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_media', function (Blueprint $table) {
            $table->id('id_media');
            $table->string('nama_media');
            $table->enum('jenis_media', ['Cetak', 'Online', 'Elektronik'])->default('Cetak');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_media');
    }
};
