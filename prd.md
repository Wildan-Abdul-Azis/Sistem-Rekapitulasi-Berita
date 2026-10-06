# Product Requirements Document (PRD)
## 1. Informasi Proyek
* **Nama Proyek:** Sistem Informasi Otomatisasi Ekstraksi Data Berita Kemitraan Media Berbasis Web.
* **Klien/Lokasi Studi:** Diskominfo Kabupaten Bogor (Bidang Kemitraan dan Media).
* **Tujuan Proyek:** Menggantikan proses rekapitulasi data publikasi media dari manual (pure-input) menjadi terotomatisasi menggunakan teknologi Optical Character Recognition (OCR).
* **Estimasi Waktu:** 3 Minggu.

## 2. Latar Belakang Masalah
Saat ini, proses rekapitulasi berita fisik/koran memakan waktu lama, melelahkan, dan rawan *human error* karena staf/mahasiswa PKL harus mengetik ulang data dari berkas fisik (Nama Media, Judul, Tanggal) ke Microsoft Excel.

## 3. Solusi yang Diusulkan
Aplikasi berbasis web responsif yang dilengkapi fitur *Live Camera* dan *OCR Engine*. Sistem memungkinkan pengguna untuk memindai berita secara langsung melalui kamera HP/Laptop, mengekstrak teksnya ke dalam sistem, menyimpannya ke *database*, dan mengekspor rekapitulasinya ke dalam format Excel standar kantor.

## 4. Alur Kerja Sistem (Workflow)
1. **Akses Sistem:** Pengguna membuka aplikasi via *browser* di HP/Laptop.
2. **Live View Scanner:** Mengaktifkan kamera secara *real-time* via WebRTC API.
3. **Ekstraksi Instan:** Pengguna memotret (*capture*) berita, lalu Tesseract.js memproses gambar di latar belakang untuk mendapatkan teks.
4. **Verifikasi Data:** Hasil OCR ditampilkan pada form pratinjau untuk proses koreksi (jika ada kesalahan pembacaan).
5. **Penyimpanan:** Data disimpan ke dalam basis data MySQL.
6. **Ekspor Data:** Rekapitulasi dapat diunduh (di-download) menjadi file `.xlsx` untuk pelaporan.

## 5. Kebutuhan Teknologi (Tech Stack)
* **Frontend:** HTML5, CSS3, Vanilla JavaScript (WebRTC API).
* **Library OCR:** Tesseract.js (Bahasa Indonesia).
* **Backend:** Laravel (PHP).
* **Database:** MySQL.
* **Library Ekspor:** Maatwebsite / Laravel-Excel.

## 6. Struktur Basis Data (Database Schema)
* **Tabel `master_media`:** Menyimpan daftar media mitra (`id_media`, `nama_media`, `jenis_media`).
* **Tabel `rekap_berita`:** Menyimpan data hasil *scan* (`id_rekap`, `media_id`, `tanggal_tayang`, `tanggal_kegiatan`, `judul_berita`, `raw_text_ocr`, `created_at`).
