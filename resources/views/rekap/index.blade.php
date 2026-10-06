@extends('layouts.app')

@section('title', 'Data Rekapitulasi Berita Media Mitra')
@section('breadcrumb', 'Beranda / Data Rekapitulasi Berita')

@section('content')
<div>
    <!-- Header Halaman -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 1.5rem; color: #1e293b;">Rekapitulasi Berita Kemitraan</h1>
            <p class="text-muted text-sm">Daftar publikasi berita koran & media mitra Diskominfo Kabupaten Bogor.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('rekap.export', request()->query()) }}" class="btn btn-success">
                <i class="fa-solid fa-file-excel"></i> Unduh Rekap Excel (.xlsx)
            </a>
            <a href="{{ route('scan.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-camera"></i> Pindai Berita Baru
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card" style="padding: 16px;">
        <form method="GET" action="{{ route('rekap.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto; gap: 12px; align-items: end;">
            <!-- Filter Bulan -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label text-xs"><i class="fa-solid fa-calendar" style="margin-right: 5px"></i> Filter Bulan</label>
                <select name="bulan" class="form-control">
                    <option value="">Semua Bulan</option>
                    @foreach ($bulanList as $ym => $namaBulan)
                        <option value="{{ $ym }}" {{ request('bulan') == $ym ? 'selected' : '' }}>
                            {{ $namaBulan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Media -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label text-xs"><i class="fa-solid fa-newspaper" style="margin-right: 5px"></i> Media Mitra</label>
                <select name="nama_media" class="form-control">
                    <option value="">Semua Media</option>
                    @foreach ($mediaList as $media)
                        <option value="{{ $media }}" {{ request('nama_media') == $media ? 'selected' : '' }}>
                            {{ $media }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pencarian Judul -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label text-xs"><i class="fa-solid fa-magnifying-glass" style="margin-right: 5px"></i> Cari Judul Berita</label>
                <input type="text" name="q" class="form-control" placeholder="Ketik kata kunci judul..." value="{{ request('q') }}">
            </div>

            <!-- Tombol Filter & Reset -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" title="Terapkan Filter">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if (request()->hasAny(['bulan', 'nama_media', 'q']))
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline" title="Reset Filter">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Rekap Data -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama Media</th>
                        <th>Tanggal Tayang</th>
                        <th>Judul Berita</th>
                        <th>Keterangan</th>
                        <th style="text-align: center;">Kliping</th>
                        <th style="text-align: center; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapList as $index => $item)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);">
                                {{ $rekapList->firstItem() + $index }}
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">
                                    {{ $item->nama_media }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;">
                                    {{ \Carbon\Carbon::parse($item->tanggal_tayang)->format('d/m/Y') }}
                                </div>
                                @if ($item->tanggal_kegiatan)
                                    <div class="text-xs text-muted">
                                        Keg: {{ \Carbon\Carbon::parse($item->tanggal_kegiatan)->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b; max-width: 350px;">
                                    {{ $item->judul_berita }}
                                </div>
                                @if ($item->link_berita)
                                    <div style="margin-top: 3px;">
                                        <a href="{{ $item->link_berita }}" target="_blank" rel="noopener noreferrer" style="color: var(--primary); font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ Str::limit($item->link_berita, 40) }}
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm" style="max-width: 180px;">
                                    {{ $item->keterangan ?? '-' }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if ($item->foto_kliping && is_array($item->foto_kliping) && count($item->foto_kliping) > 0)
                                    <a href="{{ asset('storage/' . $item->foto_kliping[0]) }}" target="_blank" title="Lihat Foto Kliping Asli">
                                        <img src="{{ asset('storage/' . $item->foto_kliping[0]) }}" alt="Thumbnail" style="width: 42px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                                    </a>
                                    @if (count($item->foto_kliping) > 1)
                                        <div class="text-xs text-muted" style="margin-top: 2px;">+{{ count($item->foto_kliping) - 1 }} hlm</div>
                                    @endif
                                @else
                                    <span class="text-muted text-xs">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 6px;">
                                    <!-- Tombol Detail -->
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openDetailModal({{ json_encode($item) }})" title="Lihat Detail Lengkap">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <!-- Tombol Edit -->
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openEditModal({{ json_encode($item) }})" title="Edit Data">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('rekap.destroy', $item->id_rekap) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data berita ini?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background-color: var(--danger-light); color: var(--danger); border: none;" title="Hapus Data">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px 16px;">
                                <div style="color: var(--text-muted);">
                                    <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                                    <strong>Belum ada data rekapitulasi berita.</strong>
                                    <p class="text-sm" style="margin-top: 4px;">Gunakan tombol "Pindai Berita Baru" untuk mulai memindai koran fisik menggunakan AI.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($rekapList->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-summary">
                    Menampilkan <strong>{{ $rekapList->firstItem() }}</strong> &ndash; <strong>{{ $rekapList->lastItem() }}</strong> dari <strong>{{ $rekapList->total() }}</strong> data berita
                </div>
                <div>
                    {{ $rekapList->links('partials.pagination') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Detail Berita -->
<div id="modal-detail" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="card-header">
            <h3><i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Detail Berita Kemitraan</h3>
            <button type="button" onclick="closeModal('modal-detail')" style="background:none; border:none; font-size:1.4rem; cursor:pointer;">&times;</button>
        </div>
        <div id="detail-content" style="font-size: 0.92rem;">
            <div style="margin-bottom: 12px;">
                <span class="text-muted text-xs">Judul Berita:</span>
                <h4 id="detail-judul" style="font-size: 1.1rem; color: #1e293b; margin-top: 2px;"></h4>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <span class="text-muted text-xs">Nama Media:</span>
                    <div id="detail-media" style="font-weight: 600;"></div>
                </div>
                <div>
                    <span class="text-muted text-xs">Tanggal Tayang:</span>
                    <div id="detail-tanggal" style="font-weight: 600;"></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <span class="text-muted text-xs">Tanggal Kegiatan:</span>
                    <div id="detail-tanggal-kegiatan" style="font-weight: 600;"></div>
                </div>
                <div>
                    <span class="text-muted text-xs">Keterangan:</span>
                    <div id="detail-keterangan" style="font-weight: 600;"></div>
                </div>
            </div>

            <div id="detail-link-box" style="margin-bottom: 14px; display: none;">
                <span class="text-muted text-xs">Link Berita:</span>
                <div>
                    <a id="detail-link" href="#" target="_blank" rel="noopener noreferrer" style="color: var(--primary); font-size: 0.85rem; word-break: break-all;"></a>
                </div>
            </div>

            <div id="detail-foto-box" style="margin-bottom: 14px; display: none;">
                <span class="text-muted text-xs" style="display: block; margin-bottom: 4px;">Foto Kliping Koran:</span>
                <div id="detail-foto-gallery" style="display: flex; gap: 8px; flex-wrap: wrap;"></div>
            </div>

            <div>
                <span class="text-muted text-xs">Teks Lengkap Hasil AI:</span>
                <pre id="detail-ocr" style="white-space: pre-wrap; font-family: inherit; background-color: #f8fafc; padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); max-height: 200px; overflow-y: auto; font-size: 0.85rem; margin-top: 4px;"></pre>
            </div>
        </div>
        <div style="margin-top: 20px; text-align: right;">
            <button type="button" class="btn btn-outline" onclick="closeModal('modal-detail')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Edit Berita -->
<div id="modal-edit" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="card-header">
            <h3><i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i> Edit Data Berita</h3>
            <button type="button" onclick="closeModal('modal-edit')" style="background:none; border:none; font-size:1.4rem; cursor:pointer;">&times;</button>
        </div>
        <form id="edit-form" method="POST" action="">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label" for="edit_nama_media">Nama Media</label>
                <input type="text" name="nama_media" id="edit_nama_media" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_judul">Judul Berita</label>
                <input type="text" name="judul_berita" id="edit_judul" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_link_berita">Link Berita / URL</label>
                <input type="text" name="link_berita" id="edit_link_berita" class="form-control" placeholder="https://...">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label" for="edit_tanggal_tayang">Tanggal Tayang</label>
                    <input type="date" name="tanggal_tayang" id="edit_tanggal_tayang" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_tanggal_kegiatan">Tanggal Kegiatan</label>
                    <input type="date" name="tanggal_kegiatan" id="edit_tanggal_kegiatan" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_keterangan">Keterangan</label>
                <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="2" placeholder="Contoh: Bupati Bogor"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_raw_text">Teks AI Mentah</label>
                <textarea name="raw_text_ocr" id="edit_raw_text" class="form-control" rows="4"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit')">Batal</button>
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDetailModal(item) {
        document.getElementById('detail-judul').textContent = item.judul_berita;
        document.getElementById('detail-media').textContent = item.nama_media || '-';
        document.getElementById('detail-tanggal').textContent = item.tanggal_tayang;
        document.getElementById('detail-tanggal-kegiatan').textContent = item.tanggal_kegiatan || '-';
        document.getElementById('detail-keterangan').textContent = item.keterangan || '-';
        document.getElementById('detail-ocr').textContent = item.raw_text_ocr || '(Tidak ada teks AI)';
        
        const linkBox = document.getElementById('detail-link-box');
        const linkElem = document.getElementById('detail-link');
        if (item.link_berita) {
            linkElem.href = item.link_berita;
            linkElem.textContent = item.link_berita;
            linkBox.style.display = 'block';
        } else {
            linkBox.style.display = 'none';
        }

        // Foto multi-halaman
        const fotoBox = document.getElementById('detail-foto-box');
        const fotoGallery = document.getElementById('detail-foto-gallery');
        fotoGallery.innerHTML = '';
        if (item.foto_kliping && Array.isArray(item.foto_kliping) && item.foto_kliping.length > 0) {
            item.foto_kliping.forEach((path, idx) => {
                const img = document.createElement('img');
                img.src = '{{ asset("storage") }}/' + path;
                img.alt = 'Halaman ' + (idx + 1);
                img.style.cssText = 'max-width: 150px; max-height: 200px; border-radius: 6px; border: 1px solid var(--border); cursor: pointer;';
                img.onclick = () => window.open(img.src, '_blank');
                fotoGallery.appendChild(img);
            });
            fotoBox.style.display = 'block';
        } else {
            fotoBox.style.display = 'none';
        }

        document.getElementById('modal-detail').classList.add('show');
    }

    function openEditModal(item) {
        const form = document.getElementById('edit-form');
        form.action = '{{ url("/rekap") }}/' + item.id_rekap;
        document.getElementById('edit_nama_media').value = item.nama_media || '';
        document.getElementById('edit_judul').value = item.judul_berita;
        document.getElementById('edit_link_berita').value = item.link_berita || '';
        document.getElementById('edit_tanggal_tayang').value = item.tanggal_tayang ? item.tanggal_tayang.substring(0, 10) : '';
        document.getElementById('edit_tanggal_kegiatan').value = item.tanggal_kegiatan ? item.tanggal_kegiatan.substring(0, 10) : '';
        document.getElementById('edit_keterangan').value = item.keterangan || '';
        document.getElementById('edit_raw_text').value = item.raw_text_ocr || '';

        document.getElementById('modal-edit').classList.add('show');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('show');
    }

    // Tutup modal jika klik di luar box
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            event.target.classList.remove('show');
        }
    };
</script>
@endpush
