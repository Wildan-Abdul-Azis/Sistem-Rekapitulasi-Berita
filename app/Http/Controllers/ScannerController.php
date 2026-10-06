<?php

namespace App\Http\Controllers;

use App\Models\RekapBerita;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScannerController extends Controller
{
    /**
     * Tampilkan antarmuka Scanner Live WebRTC & Multi-Halaman.
     */
    public function index()
    {
        return view('scan');
    }

    /**
     * Ekstrak data berita dari gambar multi-halaman menggunakan Gemini Vision AI.
     * Endpoint AJAX: POST /scan/extract
     */
    public function extract(Request $request, GeminiService $gemini)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|string',
        ]);

        $result = $gemini->extractBeritaFromImages($request->images);

        if (!$result) {
            $errorMessage = $gemini->getLastError() ?: 'Gagal mengekstrak data dari gambar. Pastikan GEMINI_API_KEY sudah benar dan gambar dapat dibaca.';
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Simpan hasil pemindaian dan verifikasi formulir ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_media' => 'required|string|max:255',
            'judul_berita' => 'required|string|max:500',
            'link_berita' => 'nullable|string|max:500',
            'tanggal_tayang' => 'required|date',
            'tanggal_kegiatan' => 'nullable|date',
            'keterangan' => 'nullable|string|max:1000',
            'raw_text_ocr' => 'nullable|string',
            'foto_base64' => 'nullable|array',
            'foto_base64.*' => 'nullable|string',
        ]);

        // Jika tanggal kegiatan tidak ada, otomatis diisi dengan tanggal tayang
        $tanggalKegiatan = !empty($validated['tanggal_kegiatan'])
            ? $validated['tanggal_kegiatan']
            : $validated['tanggal_tayang'];

        // Simpan foto multi-halaman
        $fotoPaths = [];
        if (!empty($request->foto_base64)) {
            foreach ($request->foto_base64 as $base64Image) {
                if (empty($base64Image) || !str_starts_with($base64Image, 'data:image')) {
                    continue;
                }

                [$type, $data] = explode(';', $base64Image);
                [, $data] = explode(',', $data);
                $decodedData = base64_decode($data);

                $extension = 'jpg';
                if (str_contains($type, 'png')) {
                    $extension = 'png';
                } elseif (str_contains($type, 'webp')) {
                    $extension = 'webp';
                }

                $fileName = 'kliping/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($fileName, $decodedData);
                $fotoPaths[] = $fileName;
            }
        }

        $rekap = RekapBerita::create([
            'nama_media' => $validated['nama_media'],
            'tanggal_tayang' => $validated['tanggal_tayang'],
            'tanggal_kegiatan' => $tanggalKegiatan,
            'judul_berita' => $validated['judul_berita'],
            'link_berita' => $validated['link_berita'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'raw_text_ocr' => $validated['raw_text_ocr'] ?? null,
            'foto_kliping' => !empty($fotoPaths) ? $fotoPaths : null,
        ]);

        return redirect()->route('rekap.index')
            ->with('success', 'Data berita berhasil diverifikasi dan disimpan ke database!');
    }
}
