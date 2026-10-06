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
        Schema::table('rekap_berita', function (Blueprint $table) {
            $table->string('link_berita', 500)->nullable()->after('judul_berita');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_berita', function (Blueprint $table) {
            $table->dropColumn('link_berita');
        });
    }
};
