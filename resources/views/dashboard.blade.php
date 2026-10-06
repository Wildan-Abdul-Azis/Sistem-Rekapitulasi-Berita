@extends('layouts.app')

@section('title', 'Dashboard Beranda')
@section('breadcrumb', 'Beranda / Dashboard')

@section('content')
<div>
    <!-- Hero / Welcome Banner (Diskominfo Theme) -->
    <div class="diskominfo-hero">
        <div>
            <div class="hero-badge">
                <span class="hero-badge-pill">Kabupaten Bogor</span>
                <span>Bidang Informasi & Komunikasi Publik (IKP)</span>
            </div>
            <h1 class="hero-title">Sistem Rekapitulasi Berita Kemitraan Media</h1>
            <p class="hero-desc">
                Digitalisasi kliping berita koran cetak secara instan menggunakan kamera WebRTC dan kecerdasan buatan <strong>Gemini Vision AI</strong>. Otomatisasi pembacaan judul, tanggal, nama media, dan pembuatan laporan Excel (.xlsx).
            </p>
            <div class="hero-actions" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <a href="{{ route('scan.index') }}" class="btn btn-primary btn-lg" id="btn-hero-scan">
                    <i class="fa-solid fa-camera"></i> Pindai Kliping (1 per 1)
                </a>
                <a href="{{ route('scan.tabel') }}" class="btn btn-outline btn-lg" style="background: rgba(255,255,255,0.15); color: #ffffff; border-color: rgba(255,255,255,0.4);" id="btn-hero-scan-tabel">
                    <i class="fa-solid fa-table-cells"></i> Pindai Tabel Rekap (Banyak)
                </a>
                <div class="hero-secondary-actions">
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-table-list"></i> Data Rekap
                    </a>
                    <a href="{{ route('rekap.export') }}" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-file-excel" style="color: #34d399;"></i> Unduh Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Langkah Alur Kerja Cepat (Membuat Sistem Sangat Mudah Dipahami) -->
    <div class="guide-strip">
        <div class="guide-strip-title">
            <div>
                <h4 style="font-size: 1.05rem; color: var(--primary-deep); font-weight: 800;">
                    Alur Kerja Digitalisasi Berita (3 Langkah Cepat)
                </h4>
                <p class="text-muted text-xs" style="margin-top: 2px;">
                    Panduan praktis bagi staf peliputan dan operator kemitraan media Diskominfo.
                </p>
            </div>
            <span class="badge badge-gray">Otomatis</span>
        </div>

        <div class="guide-steps-grid">
            <div class="step-item">
                <div class="step-number"><i class="fa-solid fa-camera"></i></div>
                <div class="step-info">
                    <h5>Arahkan Kamera ke Koran</h5>
                    <p>Buka menu Pindai, posisikan artikel koran pada kotak panduan kamera, dan ambil foto kliping.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <div class="step-info">
                    <h5>AI Menganalisis Gambar</h5>
                    <p>Gemini Vision AI seketika mengekstrak judul berita, tanggal terbit, nama media, dan ringkasan isi.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number"><i class="fa-solid fa-file-excel"></i></div>
                <div class="step-info">
                    <h5>Tersimpan &amp; Ekspor Excel</h5>
                    <p>Periksa hasil ekstraksi, simpan ke database arsip, lalu unduh laporan berkala format .xlsx.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Utama -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-newspaper"></i>
            </div>
            <div class="stat-info">
                <h4>Total Berita Terarsip</h4>
                <p>{{ number_format($totalBerita) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-handshake"></i>
            </div>
            <div class="stat-info">
                <h4>Media Mitra Terdaftar</h4>
                <p>{{ number_format($totalMedia) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h4>Publikasi Bulan Ini</h4>
                <p>{{ number_format($beritaBulanIni) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-file-excel"></i>
            </div>
            <div class="stat-info">
                <h4>Format Laporan</h4>
                <p style="font-size: 1.25rem; font-weight: 700; padding-top: 4px; color: #334155;">.XLSX Standar</p>
            </div>
        </div>
    </div>

    <!-- Pindaian Berita Terbaru (Full Width) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div>
                <h3>Pindaian Berita Terbaru</h3>
                <p class="text-muted text-xs">5 berita kliping cetak yang terakhir diekstraksi ke sistem.</p>
            </div>
            <a href="{{ route('rekap.index') }}" class="btn btn-outline btn-sm">
                Lihat Semua ({{ number_format($totalBerita) }})
            </a>
        </div>

        @if ($recentBerita->isEmpty())
            <div style="text-align: center; padding: 48px 20px; color: var(--text-muted); background-color: var(--surface-soft); border-radius: var(--radius-sm); border: 1px dashed var(--border);">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #ffffff; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: var(--shadow-sm); color: var(--primary);">
                    <i class="fa-solid fa-newspaper" style="font-size: 1.5rem;"></i>
                </div>
                <div style="font-weight: 700; color: var(--primary-deep); font-size: 1rem; margin-bottom: 4px;">Belum Ada Data Berita</div>
                <p style="font-size: 0.85rem; max-width: 320px; margin: 0 auto 16px;">
                    Silakan mulai memindai koran fisik pertama Anda dengan kamera atau unggah foto kliping.
                </p>
                <a href="{{ route('scan.index') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-camera"></i> Mulai Pindai Sekarang
                </a>
            </div>
        @else
            <div class="news-feed-list">
                @foreach ($recentBerita as $item)
                    <a href="{{ route('rekap.index', ['q' => $item->judul_berita]) }}" class="news-feed-item">
                        @if ($item->foto_kliping && is_array($item->foto_kliping) && count($item->foto_kliping) > 0)
                            <img src="{{ asset('storage/' . $item->foto_kliping[0]) }}" alt="Kliping" class="news-feed-thumb">
                        @else
                            <div class="news-feed-thumb">
                                <i class="fa-regular fa-newspaper"></i>
                            </div>
                        @endif
                        
                        <div class="news-feed-info">
                            <div class="news-feed-title" title="{{ $item->judul_berita }}">
                                {{ $item->judul_berita }}
                            </div>
                            <div class="news-feed-meta">
                                <span class="badge badge-blue">
                                    <i class="fa-solid fa-newspaper" style="margin-right: 4px;"></i> {{ $item->nama_media ?? 'Tanpa Media' }}
                                </span>
                                <span>
                                    <i class="fa-regular fa-calendar" style="margin-right: 3px;"></i>
                                    {{ \Carbon\Carbon::parse($item->tanggal_tayang)->format('d M Y') }}
                                </span>
                                <span class="badge badge-green" style="font-size: 0.68rem; padding: 1px 6px;">
                                    <i class="fa-solid fa-check" style="margin-right: 3px;"></i> AI Selesai
                                </span>
                            </div>
                        </div>

                        <div style="color: var(--text-muted); font-size: 0.85rem; padding-left: 8px;">
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Media Mitra Teraktif (Full Width di bawah Pindaian Berita) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div>
                <h3>Media Mitra Teraktif</h3>
                <p class="text-muted text-xs">Peringkat publikasi berita kemitraan teratas.</p>
            </div>
            <a href="{{ route('rekap.index') }}" class="btn btn-outline btn-sm">Rincian</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse ($mediaStats as $index => $stat)
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; margin-bottom: 5px;">
                        <span style="font-weight: 700; color: var(--primary-deep); display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-flex; width: 20px; height: 20px; border-radius: 50%; background-color: {{ $index === 0 ? 'var(--accent-yellow)' : ($index === 1 ? '#cbd5e1' : '#f1f5f9') }}; color: {{ $index === 0 ? '#072a63' : '#334155' }}; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800;">
                                {{ $index + 1 }}
                            </span>
                            {{ $stat->nama_media }}
                        </span>
                        <span class="badge badge-gray" style="font-weight: 700;">
                            {{ $stat->rekap_berita_count }} Berita
                        </span>
                    </div>
                    @php
                        $percentage = $totalBerita > 0 ? round(($stat->rekap_berita_count / $totalBerita) * 100) : 0;
                    @endphp
                    <div style="width: 100%; height: 7px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="width: {{ max($percentage, 6) }}%; height: 100%; background-color: var(--primary); border-radius: 4px;"></div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 0.88rem;">
                    <i class="fa-solid fa-chart-simple" style="font-size: 1.8rem; margin-bottom: 8px; display: block; opacity: 0.4;"></i>
                    Belum ada statistik publikasi media mitra.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Bagian Bawah: Ekspor Laporan & Tips Pindaian -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        
        <!-- Pusat Unduh Laporan Excel Standar Kantor -->
        <div class="card" style="margin-bottom: 0; background-color: #ffffff; border: 1px solid var(--border);">
            <div style="display: flex; gap: 14px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background-color: var(--accent-green-light); color: var(--accent-green-dark); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">
                    <i class="fa-solid fa-file-excel"></i>
                </div>
                <div style="flex: 1;">
                    <h4 style="font-size: 0.98rem; font-weight: 800; color: var(--primary-deep); margin-bottom: 3px;">
                        Ekspor Laporan Rekapitulasi
                    </h4>
                    <p class="text-muted text-xs" style="line-height: 1.4; margin-bottom: 12px;">
                        Unduh seluruh arsip data yang telah terekstraksi ke berkas format Excel (.xlsx) dengan lembar per-media untuk keperluan pelaporan resmi kantor.
                    </p>
                    <a href="{{ route('rekap.export') }}" class="btn btn-success btn-sm btn-block" style="font-weight: 700;">
                        <i class="fa-solid fa-download"></i> Unduh File Laporan (.XLSX)
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Tips Pengambilan Foto Kliping -->
        <div class="card" style="margin-bottom: 0; padding: 18px; border-left: 4px solid var(--accent-yellow-dark);">
            <div style="font-size: 0.88rem; font-weight: 700; color: var(--primary-deep); margin-bottom: 8px;">
                Tips Pindaian Koran
            </div>
            <ul style="font-size: 0.8rem; color: var(--text-muted); padding-left: 18px; line-height: 1.5; margin: 0;">
                <li>Pastikan pencahayaan cukup dan tidak terdapat bayangan gelap di atas teks berita.</li>
                <li>Pastikan nama koran, tanggal tayang, dan judul utama berada dalam bidang foto.</li>
                <li>Untuk berita bersambung, gunakan fitur multi-foto (hingga 3 halaman).</li>
            </ul>
        </div>

    </div>
</div>
@endsection

