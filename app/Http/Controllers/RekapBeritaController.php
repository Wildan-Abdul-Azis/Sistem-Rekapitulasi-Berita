<?php

namespace App\Http\Controllers;

use App\Models\RekapBerita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RekapBeritaController extends Controller
{
    /**
     * Tampilkan daftar data rekapitulasi berita beserta filter.
     */
    public function index(Request $request)
    {
        $query = RekapBerita::orderBy('tanggal_tayang', 'desc');

        // Filter kata kunci judul
        if ($request->filled('q')) {
            $query->where('judul_berita', 'like', '%'.$request->q.'%');
        }

        // Filter media (berdasarkan nama_media string)
        if ($request->filled('nama_media')) {
            $query->where('nama_media', $request->nama_media);
        }

        // Filter bulan (Format: YYYY-MM)
        if ($request->filled('bulan')) {
            $parts = explode('-', $request->bulan);
            if (count($parts) === 2) {
                $query->whereYear('tanggal_tayang', $parts[0])
                    ->whereMonth('tanggal_tayang', $parts[1]);
            }
        }

        $rekapList = $query->paginate(10)->withQueryString();

        // Daftar unik nama media dari data yang sudah ada
        $mediaList = RekapBerita::select('nama_media')
            ->distinct()
            ->orderBy('nama_media', 'asc')
            ->pluck('nama_media');

        // Daftar unik bulan publikasi berita dari database
        $dateFormatSql = config('database.default') === 'sqlite'
            ? "strftime('%Y-%m', tanggal_tayang) as ym"
            : 'DATE_FORMAT(tanggal_tayang, "%Y-%m") as ym';

        $storedMonths = RekapBerita::whereNotNull('tanggal_tayang')
            ->selectRaw($dateFormatSql)
            ->distinct()
            ->pluck('ym')
            ->toArray();

        // Selalu sertakan bulan berjalan saat ini (meskipun belum ada berita yang di-scan di bulan ini)
        $currentYm = now()->format('Y-m');
        if (! in_array($currentYm, $storedMonths)) {
            $storedMonths[] = $currentYm;
        }

        // Urutkan dari bulan terbaru ke yang terlama
        rsort($storedMonths);

        $bulanList = collect($storedMonths)->mapWithKeys(function ($ym) {
            try {
                $carbon = Carbon::createFromFormat('Y-m', $ym)->locale('id');

                return [$ym => $carbon->translatedFormat('F Y')];
            } catch (\Exception $e) {
                return [$ym => $ym];
            }
        });

        return view('rekap.index', compact('rekapList', 'mediaList', 'bulanList'));
    }

    /**
     * Tampilkan detail rekapan untuk modal pratinjau (AJAX / JSON).
     */
    public function show($id)
    {
        $rekap = RekapBerita::findOrFail($id);

        return response()->json($rekap);
    }

    /**
     * Perbarui data rekap berita yang telah disimpan.
     */
    public function update(Request $request, $id)
    {
        $rekap = RekapBerita::findOrFail($id);

        $validated = $request->validate([
            'nama_media' => 'required|string|max:255',
            'judul_berita' => 'required|string|max:500',
            'link_berita' => 'nullable|string|max:500',
            'tanggal_tayang' => 'required|date',
            'tanggal_kegiatan' => 'nullable|date',
            'keterangan' => 'nullable|string|max:1000',
            'raw_text_ocr' => 'nullable|string',
        ]);

        if (empty($validated['tanggal_kegiatan'])) {
            $validated['tanggal_kegiatan'] = $validated['tanggal_tayang'];
        }

        $rekap->update($validated);

        return redirect()->route('rekap.index')
            ->with('success', 'Data berita berhasil diperbarui.');
    }

    /**
     * Hapus data rekap berita beserta berkas foto klipingnya jika ada.
     */
    public function destroy($id)
    {
        $rekap = RekapBerita::findOrFail($id);

        // Hapus semua file foto multi-halaman
        if ($rekap->foto_kliping && is_array($rekap->foto_kliping)) {
            foreach ($rekap->foto_kliping as $fotoPath) {
                if (Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }
            }
        }

        $rekap->delete();

        return redirect()->route('rekap.index')
            ->with('success', 'Data rekapitulasi berhasil dihapus.');
    }
}
