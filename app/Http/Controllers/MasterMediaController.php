<?php

namespace App\Http\Controllers;

use App\Models\MasterMedia;
use Illuminate\Http\Request;

class MasterMediaController extends Controller
{
    /**
     * Tampilkan daftar media mitra.
     */
    public function index(Request $request)
    {
        $query = MasterMedia::withCount('rekapBerita')->orderBy('nama_media', 'asc');

        if ($request->filled('q')) {
            $query->where('nama_media', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('jenis_media')) {
            $query->where('jenis_media', $request->jenis_media);
        }

        $mediaList = $query->paginate(15)->withQueryString();

        return view('media.index', compact('mediaList'));
    }

    /**
     * Tambah media mitra baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_media' => 'required|string|max:255|unique:master_media,nama_media',
            'jenis_media' => 'required|in:Cetak,Online,Elektronik',
        ]);

        MasterMedia::create($validated);

        return redirect()->route('media.index')
            ->with('success', 'Media mitra baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data media mitra.
     */
    public function update(Request $request, $id)
    {
        $media = MasterMedia::findOrFail($id);

        $validated = $request->validate([
            'nama_media' => 'required|string|max:255|unique:master_media,nama_media,' . $id . ',id_media',
            'jenis_media' => 'required|in:Cetak,Online,Elektronik',
        ]);

        $media->update($validated);

        return redirect()->route('media.index')
            ->with('success', 'Data media mitra berhasil diperbarui.');
    }

    /**
     * Hapus media mitra.
     */
    public function destroy($id)
    {
        $media = MasterMedia::findOrFail($id);
        $media->delete();

        return redirect()->route('media.index')
            ->with('success', 'Media mitra berhasil dihapus.');
    }
}
