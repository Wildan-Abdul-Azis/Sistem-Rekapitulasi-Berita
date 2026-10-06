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
        Schema::create('rekap_berita', function (Blueprint $table) {
            $table->id('id_rekap');
            $table->unsignedBigInteger('media_id');
            $table->date('tanggal_tayang');
            $table->date('tanggal_kegiatan')->nullable();
            $table->string('judul_berita', 500);
            $table->longText('raw_text_ocr')->nullable();
            $table->string('foto_kliping')->nullable();
            $table->timestamps();

            $table->foreign('media_id')
                  ->references('id_media')
                  ->on('master_media')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_berita');
    }
};
