<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\RekapBeritaController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\ScannerTabelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Ekstraksi Data Berita Kemitraan Diskominfo
|--------------------------------------------------------------------------
*/

// Rute Autentikasi (Hanya untuk tamu/guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rute yang Dilindungi (Wajib Login)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Utama
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Fitur 1: Scanner Kliping Koran (1 per 1 - Multi Halaman)
    Route::get('/scan', [ScannerController::class, 'index'])->name('scan.index');
    Route::post('/scan/extract', [ScannerController::class, 'extract'])->name('scan.extract');
    Route::post('/scan', [ScannerController::class, 'store'])->name('scan.store');

    // Fitur 2: Scanner Tabel Rekap Media (Banyak Berita Sekaligus / Batch)
    Route::get('/scan-tabel', [ScannerTabelController::class, 'index'])->name('scan.tabel');
    Route::post('/scan-tabel/extract', [ScannerTabelController::class, 'extract'])->name('scan.tabel.extract');
    Route::post('/scan-tabel', [ScannerTabelController::class, 'store'])->name('scan.tabel.store');

    // Rekapitulasi Berita
    Route::get('/rekap', [RekapBeritaController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/{id}', [RekapBeritaController::class, 'show'])->name('rekap.show');
    Route::put('/rekap/{id}', [RekapBeritaController::class, 'update'])->name('rekap.update');
    Route::delete('/rekap/{id}', [RekapBeritaController::class, 'destroy'])->name('rekap.destroy');

    // Ekspor Laporan Excel
    Route::get('/export/excel', [ExportController::class, 'export'])->name('rekap.export');
});
