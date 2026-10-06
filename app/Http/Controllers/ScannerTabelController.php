<?php

namespace App\Http\Controllers;

use App\Models\RekapBerita;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerTabelController extends Controller
{
    /**
     * Tampilkan halaman formulir dan pemindai tabel rekapitulasi media.
     * Fitur 2: Pindai dokumen/gambar tabel yang berisi banyak berita sekaligus.
     */
    public function index()
    {
        return view('scan-tabel');
    }

    /**
     * Ekstrak seluruh baris berita dari gambar atau PDF tabel rekap media.
     * Endpoint AJAX: POST /scan-tabel/extract
     */
    public function extract(Request $request, GeminiService $gemini)
    {
        // Validasi: pastikan ada gambar atau file dokumen yang dikirim
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|string',
        ]);

        // Ambil array file dari body request (gunakan ->input('files') agar tidak bentrok dengan property Symfony FileBag)
        $fileList = $request->input('files');

        // Kirim berkas ke Gemini AI untuk membaca tabel
        $hasil = $gemini->extractTabelFromImages($fileList);

        // Jika AI gagal membaca atau terjadi kesalahan koneksi/API
        if (! $hasil) {
            $pesanError = $gemini->getLastError() ?: 'Gagal mengekstrak data dari tabel. Pastikan dokumen tabel jelas dan terbaca.';

            return response()->json([
                'success' => false,
                'message' => $pesanError,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $hasil,
        ]);
    }

    /**
     * Simpan seluruh data baris berita hasil pemindaian tabel ke database.
     * Endpoint POST /scan-tabel
     */
    public function store(Request $request)
    {
        // Validasi array baris berita yang telah dikoreksi pengguna
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.nama_media' => 'required|string|max:255',
            'items.*.judul_berita' => 'required|string|max:500',
            'items.*.tanggal_tayang' => 'required|date',
            'items.*.tanggal_kegiatan' => 'nullable|date',
            'items.*.link_berita' => 'nullable|string|max:500',
            'items.*.keterangan' => 'nullable|string|max:1000',
        ]);

        $jumlahTersimpan = 0;

        // Gunakan transaksi database agar data tersimpan secara aman dan utuh
        DB::transaction(function () use ($validated, &$jumlahTersimpan) {
            foreach ($validated['items'] as $item) {
                // Jika tanggal kegiatan kosong, gunakan tanggal tayang
                $tanggalKegiatan = ! empty($item['tanggal_kegiatan'])
                    ? $item['tanggal_kegiatan']
                    : $item['tanggal_tayang'];

                RekapBerita::create([
                    'nama_media' => trim($item['nama_media']),
                    'tanggal_tayang' => $item['tanggal_tayang'],
                    'tanggal_kegiatan' => $tanggalKegiatan,
                    'judul_berita' => trim($item['judul_berita']),
                    'link_berita' => ! empty($item['link_berita']) ? trim($item['link_berita']) : null,
                    'keterangan' => ! empty($item['keterangan']) ? trim($item['keterangan']) : null,
                    'raw_text_ocr' => 'Hasil Scan Tabel Rekapitulasi Media',
                    'foto_kliping' => null,
                ]);

                $jumlahTersimpan++;
            }
        });

        // Alihkan ke halaman rekap dengan pesan notifikasi sukses
        return redirect()->route('rekap.index')
            ->with('success', "Berhasil menyimpan {$jumlahTersimpan} data berita hasil scan tabel ke database!");
    }
}
