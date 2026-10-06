<?php

namespace App\Http\Controllers;

use App\Models\RekapBerita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard dan metrik statistik.
     */
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalBerita = RekapBerita::count();

        // Hitung jumlah media unik dari data rekap
        $totalMedia = RekapBerita::distinct('nama_media')->count('nama_media');

        $beritaBulanIni = RekapBerita::whereYear('tanggal_tayang', $currentYear)
            ->whereMonth('tanggal_tayang', $currentMonth)
            ->count();

        // Statistik per nama media (top 5 berdasarkan jumlah berita)
        $mediaStats = RekapBerita::select('nama_media', DB::raw('COUNT(*) as rekap_berita_count'))
            ->groupBy('nama_media')
            ->orderByDesc('rekap_berita_count')
            ->take(5)
            ->get();

        // 5 Berita terbaru
        $recentBerita = RekapBerita::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalBerita',
            'totalMedia',
            'beritaBulanIni',
            'mediaStats',
            'recentBerita'
        ));
    }
}
