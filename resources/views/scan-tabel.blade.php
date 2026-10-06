@extends('layouts.app')

@section('title', 'Pindai Tabel Rekap Media (Multi-Berita / PDF)')
@section('breadcrumb', 'Beranda / Pindai Tabel Rekap Media')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    <!-- Switcher Mode Pindai -->
    <div style="display: flex; justify-content: center; margin-bottom: 20px;">
        <div style="background: #e2e8f0; padding: 4px; border-radius: 30px; display: inline-flex; gap: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);">
            <a href="{{ route('scan.index') }}" class="btn btn-sm" style="border-radius: 24px; padding: 7px 18px; font-weight: 600; color: #475569; background: transparent; text-decoration: none;">
                <i class="fa-solid fa-file-lines" style="margin-right: 6px;"></i> 1. Pindai Kliping Satuan (1 Berita)
            </a>
            <span class="btn btn-sm" style="border-radius: 24px; padding: 7px 18px; font-weight: 700; color: #ffffff; background: var(--primary); box-shadow: var(--shadow-sm); cursor: default;">
                <i class="fa-solid fa-table-cells" style="margin-right: 6px;"></i> 2. Pindai Tabel Rekap (Banyak Berita)
            </span>
        </div>
    </div>

    <!-- Judul & Deskripsi Halaman -->
    <div style="margin-bottom: 24px; text-align: center;">
        <h1 style="font-size: 1.65rem; color: #1e293b;">Pindai &amp; Ekstraksi Tabel Rekap Media</h1>
        <p class="text-muted text-sm" style="margin-top: 6px; max-width: 720px; margin-left: auto; margin-right: auto;">
            Unggah dokumen rekapitulasi (berkas <strong>PDF</strong> atau <strong>foto lembaran tabel</strong>) yang berisi daftar judul berita tayang dalam satu bulan. AI Gemini akan membaca seluruh baris tabel secara otomatis dan Anda dapat mengoreksinya sebelum disimpan.
        </p>
    </div>

    <!-- 1. Card Input Berkas & Pindai Dokumen -->
    <div class="card" style="padding: 24px; margin-bottom: 24px;">
        <div id="dropzone-area" class="scan-dropzone">
            <input type="file" id="file-input-tabel" accept=".pdf,image/*" multiple style="display: none;">
            <input type="file" id="native-camera-tabel" accept="image/*" capture="environment" style="display: none;">
            
            <div class="scan-dropzone-icon">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <div style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                Pilih File PDF atau Foto Lembaran Tabel
            </div>
            <p class="text-muted text-xs" style="margin-bottom: 18px; max-width: 520px; margin-left: auto; margin-right: auto;">
                Mendukung berkas <strong>PDF</strong> rekapitulasi, foto langsung dari <strong>kamera HP</strong>, atau gambar tabel (JPG, PNG, WebP).
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
                <!-- Tombol Ambil Foto Kamera HP Bawaan -->
                <button type="button" class="btn btn-primary btn-lg" onclick="document.getElementById('native-camera-tabel').click();" style="min-width: 220px;">
                    <i class="fa-solid fa-camera"></i> Ambil Foto Lembar (Kamera HP)
                </button>
                <!-- Tombol Jelajahi File / PDF / Galeri -->
                <button type="button" class="btn btn-outline btn-lg" onclick="document.getElementById('file-input-tabel').click();" style="min-width: 220px;">
                    <i class="fa-solid fa-folder-open"></i> Pilih Berkas PDF / Gambar
                </button>
            </div>
        </div>

        <!-- Galeri Berkas / Lembaran Terpilih -->
        <div id="files-gallery" style="display: none; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-weight: 700; font-size: 0.92rem; color: #1e293b;">
                    <i class="fa-solid fa-layer-group" style="color: var(--primary);"></i>
                    Berkas/Lembar Tabel Siap Diproses: <span id="files-count-badge" class="badge badge-blue">0</span>
                </span>
                <button type="button" id="btn-clear-files" class="btn btn-outline btn-sm text-danger" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-trash-can"></i> Hapus Semua
                </button>
            </div>
            <div id="files-list-container" style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px;"></div>
        </div>

        <!-- Tombol Aksi Ekstraksi AI -->
        <div style="margin-top: 20px; text-align: center;">
            <button type="button" id="btn-extract-tabel" class="btn btn-success btn-lg" style="min-width: 320px; display: none;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Ekstrak Semua Baris Tabel dengan AI
            </button>
        </div>

        <!-- Box Loading / Progress AI -->
        <div id="ai-tabel-progress" class="ocr-progress-box" style="display: none; margin-top: 18px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.88rem; font-weight: 700;">
                <span id="ai-tabel-status">Sedang membaca struktur tabel dan baris berita dengan AI...</span>
                <span id="ai-tabel-icon"><i class="fa-solid fa-spinner fa-spin"></i></span>
            </div>
            <div class="progress-bar-container" style="margin-top: 8px;">
                <div id="ai-tabel-bar" class="progress-bar-fill" style="width: 50%; animation: pulse-progress 1.5s ease-in-out infinite;"></div>
            </div>
        </div>

    </div>

    <!-- 2. Formulir Verifikasi & Koreksi Tabel (Muncul setelah ekstraksi AI berhasil) -->
    <div class="card" id="tabel-result-section" style="display: none;">
        <div class="card-header">
            <div>
                <h2>
                    <i class="fa-solid fa-table-list" style="color: var(--primary);"></i>
                    Hasil Ekstraksi Tabel Rekapitulasi
                </h2>
                <p class="text-muted text-sm" style="margin-top: 2px;">
                    Periksa dan perbaiki data di bawah ini jika terdapat ketidaksesuaian sebelum disimpan ke database.
                </p>
            </div>
            <span class="badge badge-green" id="badge-total-items" style="font-size: 0.85rem; padding: 6px 12px;">
                0 Berita Ditemukan
            </span>
        </div>

        <!-- Toolbar Kontrol Cepat -->
        <div style="background-color: var(--surface-soft); padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex: 1; min-width: 280px;">
                <label for="mass-nama-media" style="font-size: 0.85rem; font-weight: 700; color: #334155; white-space: nowrap;">
                    <i class="fa-solid fa-building"></i> Nama Media Bersama:
                </label>
                <input type="text" id="mass-nama-media" class="form-control form-control-sm" placeholder="Contoh: PT. BOGOR MEDIAPOLITAN / Metropolitan.id" style="max-width: 320px;">
                <button type="button" id="btn-apply-mass-media" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-check-double"></i> Terapkan ke Semua Baris
                </button>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="button" id="btn-add-table-row" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-plus"></i> Tambah Baris Manual
                </button>
            </div>
        </div>

        <!-- Form Penyimpanan Massal -->
        <form action="{{ route('scan.tabel.store') }}" method="POST" id="form-rekap-tabel">
            @csrf

            <!-- Tabel Data Interaktif -->
            <div class="table-responsive" style="overflow-x: auto; max-height: 560px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
                <table class="table" style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
                    <thead style="position: sticky; top: 0; background-color: #f8fafc; z-index: 2; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <tr style="border-bottom: 2px solid var(--border); font-size: 0.82rem; text-align: left;">
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="width: 140px;">Tgl Tayang <span style="color:var(--danger)">*</span></th>
                            <th style="width: 140px;">Tgl Kegiatan</th>
                            <th style="min-width: 280px;">Judul Berita / Iklan <span style="color:var(--danger)">*</span></th>
                            <th style="min-width: 200px;">Nama Media <span style="color:var(--danger)">*</span></th>
                            <th style="min-width: 220px;">Link Berita</th>
                            <th style="min-width: 160px;">Keterangan</th>
                            <th style="width: 60px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-rows-body">
                        <!-- Baris-baris data akan diisi secara dinamis oleh JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Tombol Submit Form -->
            <div style="margin-top: 24px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; align-items: center;">
                <div class="text-muted text-xs">
                    <i class="fa-solid fa-circle-info"></i> Pastikan seluruh baris berita yang ingin disimpan memiliki tanggal tayang, judul, dan nama media.
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" id="btn-reset-extracted" class="btn btn-outline">
                        <i class="fa-solid fa-rotate-left"></i> Kosongkan Hasil
                    </button>
                    <button type="submit" class="btn btn-success btn-lg" id="btn-submit-tabel-store">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Semua Data ke Database (<span id="btn-save-count">0</span> Berita)
                    </button>
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

    .file-item-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 8px;
        flex-shrink: 0;
        min-width: 180px;
        max-width: 240px;
        box-shadow: var(--shadow-xs);
        position: relative;
    }
    .file-item-card .icon-file {
        font-size: 1.5rem;
        color: var(--primary);
    }
    .file-item-card .icon-pdf {
        color: #ef4444;
    }
    .file-item-card .file-info {
        flex: 1;
        overflow: hidden;
    }
    .file-item-card .file-name {
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #1e293b;
    }
    .file-item-card .file-size {
        font-size: 0.7rem;
        color: var(--text-muted);
    }
    .file-item-card .btn-remove-file {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 0.85rem;
        padding: 4px;
        transition: color 0.15s;
    }
    .file-item-card .btn-remove-file:hover {
        color: #ef4444;
    }

    .tabel-input-field {
        font-size: 0.85rem;
        padding: 6px 8px;
        border-radius: 6px;
        border: 1px solid var(--border);
        width: 100%;
        background-color: #ffffff;
        transition: border-color 0.15s;
    }
    .tabel-input-field:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(0, 74, 173, 0.15);
    }

    .table td {
        padding: 8px 6px;
        vertical-align: top;
        border-bottom: 1px solid #f1f5f9;
    }
    .table tr:hover td {
        background-color: #f8fafc;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/scan-tabel.js') }}"></script>
@endpush
@endsection
