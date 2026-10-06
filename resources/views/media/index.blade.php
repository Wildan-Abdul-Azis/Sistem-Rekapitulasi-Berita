@extends('layouts.app')

@section('title', 'Kelola Media Mitra')

@section('content')
<div>
    <!-- Header Halaman -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 1.5rem; color: #1e293b;">Daftar Media Mitra</h1>
            <p class="text-muted text-sm">Kelola daftar surat kabar, portal berita online, dan media elektronik mitra Diskominfo Kabupaten Bogor.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openAddMediaModal()">
                <i class="fa-solid fa-plus"></i> Tambah Media Baru
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding: 16px;">
        <form method="GET" action="{{ route('media.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label class="form-label text-xs"><i class="fa-solid fa-magnifying-glass"></i> Cari Nama Media</label>
                <input type="text" name="q" class="form-control" placeholder="Ketik nama media..." value="{{ request('q') }}">
            </div>

            <div class="form-group" style="margin-bottom: 0; width: 180px;">
                <label class="form-label text-xs"><i class="fa-solid fa-filter"></i> Jenis Media</label>
                <select name="jenis_media" class="form-control">
                    <option value="">Semua Jenis</option>
                    <option value="Cetak" {{ request('jenis_media') == 'Cetak' ? 'selected' : '' }}>Cetak (Koran/Majalah)</option>
                    <option value="Online" {{ request('jenis_media') == 'Online' ? 'selected' : '' }}>Online (Portal Web)</option>
                    <option value="Elektronik" {{ request('jenis_media') == 'Elektronik' ? 'selected' : '' }}>Elektronik (TV/Radio)</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                @if (request()->hasAny(['q', 'jenis_media']))
                    <a href="{{ route('media.index') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Media Mitra -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama Media Mitra</th>
                        <th style="text-align: center; width: 160px;">Jenis Media</th>
                        <th style="text-align: center; width: 160px;">Total Publikasi</th>
                        <th style="text-align: center; width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mediaList as $index => $media)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);">
                                {{ $mediaList->firstItem() + $index }}
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 1rem; color: #1e293b;">
                                    {{ $media->nama_media }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $badge = match($media->jenis_media) {
                                        'Cetak' => 'badge-blue',
                                        'Online' => 'badge-green',
                                        default => 'badge-yellow',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">
                                    {{ $media->jenis_media }}
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #1e293b;">
                                {{ $media->rekap_berita_count }} Berita
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 6px;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openEditMediaModal({{ json_encode($media) }})" title="Edit Media">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('media.destroy', $media->id_media) }}" method="POST" onsubmit="return confirm('Hapus media ini? Semua berita terkait media ini akan ikut terhapus.')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background-color: var(--danger-light); color: var(--danger); border: none;" title="Hapus Media">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                <span class="text-muted">Tidak ada data media mitra yang cocok.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mediaList->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
                {{ $mediaList->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Media -->
<div id="modal-add-media" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="card-header">
            <h3><i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i> Tambah Media Mitra Baru</h3>
            <button type="button" onclick="closeModal('modal-add-media')" style="background:none; border:none; font-size:1.4rem; cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('media.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="add_nama_media">Nama Media Mitra <span style="color: var(--danger);">*</span></label>
                <input type="text" name="nama_media" id="add_nama_media" class="form-control" placeholder="Contoh: Radar Bogor" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="add_jenis_media">Jenis Media <span style="color: var(--danger);">*</span></label>
                <select name="jenis_media" id="add_jenis_media" class="form-control" required>
                    <option value="Cetak">Cetak (Koran/Kliping/Majalah)</option>
                    <option value="Online">Online (Portal Berita)</option>
                    <option value="Elektronik">Elektronik (TV/Radio)</option>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-add-media')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Media</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Media -->
<div id="modal-edit-media" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="card-header">
            <h3><i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i> Edit Media Mitra</h3>
            <button type="button" onclick="closeModal('modal-edit-media')" style="background:none; border:none; font-size:1.4rem; cursor:pointer;">&times;</button>
        </div>
        <form id="edit-media-form" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label" for="edit_media_nama">Nama Media Mitra <span style="color: var(--danger);">*</span></label>
                <input type="text" name="nama_media" id="edit_media_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="edit_media_jenis">Jenis Media <span style="color: var(--danger);">*</span></label>
                <select name="jenis_media" id="edit_media_jenis" class="form-control" required>
                    <option value="Cetak">Cetak (Koran/Kliping/Majalah)</option>
                    <option value="Online">Online (Portal Berita)</option>
                    <option value="Elektronik">Elektronik (TV/Radio)</option>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit-media')">Batal</button>
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddMediaModal() {
        document.getElementById('modal-add-media').classList.add('show');
    }

    function openEditMediaModal(media) {
        const form = document.getElementById('edit-media-form');
        form.action = '{{ url("/media") }}/' + media.id_media;
        document.getElementById('edit_media_nama').value = media.nama_media;
        document.getElementById('edit_media_jenis').value = media.jenis_media;
        document.getElementById('modal-edit-media').classList.add('show');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('show');
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            event.target.classList.remove('show');
        }
    };
</script>
@endpush
