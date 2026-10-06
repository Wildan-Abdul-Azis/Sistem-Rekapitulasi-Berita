<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Redesign tabel rekap_berita:
     * - Hapus kolom media_id (FK ke master_media)
     * - Tambah kolom nama_media (string, langsung dari hasil ekstraksi AI)
     * - Tambah kolom keterangan
     * - Ubah foto_kliping menjadi JSON (multi-foto)
     */
    public function up(): void
    {
        Schema::table('rekap_berita', function (Blueprint $table) {
            // Hapus foreign key dan kolom media_id
            $table->dropForeign(['media_id']);
            $table->dropColumn('media_id');

            // Tambah kolom nama_media (string, langsung dari AI)
            $table->string('nama_media', 255)->after('id_rekap');

            // Tambah kolom keterangan
            $table->text('keterangan')->nullable()->after('link_berita');

            // Ubah foto_kliping menjadi JSON (array path multi-foto)
            $table->json('foto_kliping')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_berita', function (Blueprint $table) {
            $table->dropColumn('nama_media');
            $table->dropColumn('keterangan');

            $table->unsignedBigInteger('media_id')->after('id_rekap');
            $table->foreign('media_id')
                  ->references('id_media')
                  ->on('master_media')
                  ->onDelete('cascade');

            $table->string('foto_kliping')->nullable()->change();
        });
    }
};
