@extends('layouts.app')

@section('title', 'Pindai Berita Kliping (Multi-Halaman + AI)')
@section('breadcrumb', 'Beranda / Pindai Berita Koran')

@section('content')
<div style="max-width: 960px; margin: 0 auto;">

    <!-- Switcher Mode Pindai -->
    <div style="display: flex; justify-content: center; margin-bottom: 20px;">
        <div style="background: #e2e8f0; padding: 4px; border-radius: 30px; display: inline-flex; gap: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);">
            <span class="btn btn-sm" style="border-radius: 24px; padding: 7px 18px; font-weight: 700; color: #ffffff; background: var(--primary); box-shadow: var(--shadow-sm); cursor: default;">
                <i class="fa-solid fa-file-lines" style="margin-right: 6px;"></i> 1. Pindai Kliping Satuan (1 Berita)
            </span>
            <a href="{{ route('scan.tabel') }}" class="btn btn-sm" style="border-radius: 24px; padding: 7px 18px; font-weight: 600; color: #475569; background: transparent; text-decoration: none;">
                <i class="fa-solid fa-table-cells" style="margin-right: 6px;"></i> 2. Pindai Tabel Rekap (Banyak Berita)
            </a>
        </div>
    </div>

    <!-- Judul Halaman -->
    <div style="margin-bottom: 20px; text-align: center;">
        <h1 style="font-size: 1.6rem; color: #1e293b;">Pindai & Ekstraksi Berita Koran (1 per 1)</h1>
        <p class="text-muted" style="margin-top: 4px;">
            Foto beberapa halaman kliping koran/berita fisik (1-3 halaman), lalu ekstrak otomatis menggunakan AI Gemini Vision.
        </p>
    </div>

    <!-- 1. Input Berkas & Kamera HP -->
    <div class="card" style="padding: 24px;">
        <div id="dropzone-single" class="scan-dropzone">
            <input type="file" id="file-upload" accept="image/*" multiple style="display: none;">
            <input type="file" id="native-camera-upload" accept="image/*" capture="environment" style="display: none;">
            
            <div class="scan-dropzone-icon">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <div style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                Pilih Gambar Kliping Berita
            </div>
            <p class="text-muted text-xs" style="margin-bottom: 18px; max-width: 480px; margin-left: auto; margin-right: auto;">
                Ambil foto langsung dengan kamera HP Anda, pilih berkas dari galeri/komputer, atau seret foto koran ke area ini (Mendukung JPG, PNG, WebP).
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
                <!-- Tombol Ambil Foto Kamera HP Bawaan (Kualitas Maksimal & Autofocus HP) -->
                <button type="button" class="btn btn-primary btn-lg" onclick="document.getElementById('native-camera-upload').click();" style="min-width: 220px;">
                    <i class="fa-solid fa-camera"></i> Ambil Foto (Kamera HP)
                </button>
                <!-- Tombol Pilih dari Galeri / Berkas Komputer -->
                <button type="button" class="btn btn-outline btn-lg" onclick="document.getElementById('file-upload').click();" style="min-width: 220px;">
                    <i class="fa-solid fa-images"></i> Pilih dari Galeri / File
                </button>
            </div>
        </div>

        <!-- Galeri Multi-Halaman (Thumbnail Strip) -->
        <div id="pages-gallery" style="display: none; margin-top: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">
                    <i class="fa-solid fa-layer-group" style="color: var(--primary);"></i>
                    Halaman Terfoto: <span id="page-count" style="color: var(--primary);">0</span>
                </span>
                <button type="button" id="btn-clear-pages" class="btn btn-outline btn-sm" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-trash-can"></i> Hapus Semua
                </button>
            </div>
            <div id="pages-thumbnails" style="display: flex; gap: 10px; overflow-x: auto; padding: 8px 0;"></div>
        </div>

        <!-- Tombol Ekstrak AI -->
        <div style="margin-top: 14px; text-align: center;">
            <button type="button" id="btn-extract" class="btn btn-success btn-lg" style="min-width: 300px; display: none;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Ekstrak dengan AI Gemini
            </button>
        </div>

        <!-- Kotak Progres Ekstraksi AI -->
        <div id="ai-progress-box" class="ocr-progress-box" style="display: none;">
            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600;">
                <span id="ai-status-text">Mengirim gambar ke AI Gemini...</span>
                <span id="ai-progress-icon"><i class="fa-solid fa-spinner fa-spin"></i></span>
            </div>
            <div class="progress-bar-container">
                <div id="ai-progress-bar" class="progress-bar-fill" style="width: 40%; animation: pulse-progress 1.5s ease-in-out infinite;"></div>
            </div>
        </div>
    </div>

    <!-- 2. Formulir Verifikasi Hasil Ekstraksi AI (tersembunyi hingga AI selesai) -->
    <div class="card" id="verification-form-section" style="margin-top: 24px; display: none;">
        <div class="card-header">
            <div>
                <h2><i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i> Verifikasi & Koreksi Data</h2>
                <p class="text-muted text-sm">Periksa kembali data yang diekstrak oleh AI sebelum disimpan ke database.</p>
            </div>
            <span class="badge badge-blue">Formulir Rekapitulasi</span>
        </div>

        <form action="{{ route('scan.store') }}" method="POST" id="form-rekap">
            @csrf
            <!-- Hidden fields untuk foto base64 (akan diisi via JS) -->
            <div id="hidden-photos-container"></div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <!-- Kolom Kiri: Pratinjau Halaman Terfoto -->
                <div>
                    <label class="form-label"><i class="fa-solid fa-images"></i> Pratinjau Halaman Kliping</label>
                    <div id="preview-box" style="border: 2px dashed var(--border); border-radius: var(--radius-sm); padding: 8px; text-align: center; background-color: #f1f5f9; min-height: 240px; display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 8px;">
                        <span id="preview-placeholder" class="text-muted text-sm">
                            <i class="fa-solid fa-camera" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.5;"></i>
                            Foto halaman kliping akan muncul di sini setelah Anda mengambil foto atau mengunggah gambar.
                        </span>
                    </div>

                    <!-- Teks OCR Mentah (Collapsible) -->
                    <div style="margin-top: 14px;">
                        <button type="button" id="btn-toggle-ocr" style="background: none; border: none; cursor: pointer; font-size: 0.85rem; color: var(--primary); font-weight: 600; padding: 0;">
                            <i class="fa-solid fa-chevron-right" id="ocr-chevron"></i> Lihat Teks Hasil AI (Opsional)
                        </button>
                        <div id="ocr-text-wrapper" style="display: none; margin-top: 8px;">
                            <textarea name="raw_text_ocr" id="raw_text_ocr" class="form-control" rows="6" placeholder="Teks hasil pembacaan AI akan muncul di sini secara otomatis..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Field Data Berita -->
                <div>
                    <!-- Nama Media -->
                    <div class="form-group">
                        <label class="form-label" for="nama_media">
                            <i class="fa-solid fa-newspaper"></i> Nama Media <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" name="nama_media" id="nama_media" class="form-control" placeholder="Contoh: Reformasi Aktual, Radar Bogor, dll" required>
                        <small class="text-muted text-xs">Otomatis terisi dari hasil AI. Koreksi jika diperlukan.</small>
                    </div>

                    <!-- Judul Berita -->
                    <div class="form-group">
                        <label class="form-label" for="judul_berita">
                            <i class="fa-solid fa-heading"></i> Judul Berita <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" name="judul_berita" id="judul_berita" class="form-control" placeholder="Contoh: Bupati Bogor Resmikan Jembatan Baru di Cisarua" required>
                    </div>

                    <!-- Link Berita -->
                    <div class="form-group">
                        <label class="form-label" for="link_berita">
                            <i class="fa-solid fa-link"></i> Link Berita / URL
                        </label>
                        <input type="text" name="link_berita" id="link_berita" class="form-control" placeholder="https://...">
                        <small class="text-muted text-xs">Link biasanya ditemukan di halaman terakhir kliping.</small>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <!-- Tanggal Tayang -->
                        <div class="form-group">
                            <label class="form-label" for="tanggal_tayang">
                                <i class="fa-solid fa-calendar-day"></i> Tanggal Tayang <span style="color: var(--danger);">*</span>
                            </label>
                            <input type="date" name="tanggal_tayang" id="tanggal_tayang" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Tanggal Kegiatan -->
                        <div class="form-group">
                            <label class="form-label" for="tanggal_kegiatan">
                                <i class="fa-solid fa-calendar-check"></i> Tanggal Kegiatan
                            </label>
                            <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan" class="form-control">
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="form-group">
                        <label class="form-label" for="keterangan">
                            <i class="fa-solid fa-comment-dots"></i> Keterangan
                        </label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Contoh: Bupati Bogor, Pemkab Bogor, dll"></textarea>
                        <small class="text-muted text-xs">Keterangan singkat tokoh/instansi terkait berita.</small>
                    </div>

                    <!-- Tombol Aksi Simpan -->
                    <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                        <button type="submit" class="btn btn-success btn-lg btn-block" id="btn-submit-rekap">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan ke Database
                        </button>
                        <button type="button" id="btn-reset-form" class="btn btn-outline btn-block">
                            <i class="fa-solid fa-rotate-left"></i> Kosongkan Formulir
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

@push('styles')
<style>
    @keyframes pulse-progress {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .page-thumb {
        position: relative;
        flex-shrink: 0;
        width: 90px;
        height: 120px;
        border-radius: 6px;
        overflow: hidden;
        border: 2px solid var(--border);
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .page-thumb:hover {
        border-color: var(--primary);
    }
    .page-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .page-thumb .page-number {
        position: absolute;
        top: 4px;
        left: 4px;
        background: var(--primary);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 3px;
    }
    .page-thumb .btn-remove-page {
        position: absolute;
        top: 3px;
        right: 3px;
        background: rgba(220, 38, 38, 0.85);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.6rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .page-thumb:hover .btn-remove-page {
        opacity: 1;
    }

    .preview-img-item {
        max-height: 160px;
        border-radius: 6px;
        border: 1px solid var(--border);
    }
</style>
@endpush
@endsection

@push('scripts')
<!-- Client-Side Scanner Logic (Multi-Halaman + Gemini AI) -->
<script src="{{ asset('js/scanner.js') }}"></script>
<script>
    // Toggle OCR raw text accordion
    const btnToggleOcr = document.getElementById('btn-toggle-ocr');
    const ocrTextWrapper = document.getElementById('ocr-text-wrapper');
    const ocrChevron = document.getElementById('ocr-chevron');
    if (btnToggleOcr) {
        btnToggleOcr.addEventListener('click', () => {
            const isHidden = ocrTextWrapper.style.display === 'none';
            ocrTextWrapper.style.display = isHidden ? 'block' : 'none';
            ocrChevron.className = isHidden ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right';
        });
    }
</script>
@endpush
