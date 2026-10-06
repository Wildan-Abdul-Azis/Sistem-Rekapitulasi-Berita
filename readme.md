# Sistem Informasi Ekstraksi Data Berita Kemitraan (Diskominfo Kab. Bogor)

Aplikasi berbasis web ini dikembangkan untuk mengotomatisasi proses input data rekapitulasi berita media mitra pada Diskominfo Kabupaten Bogor. Menggunakan teknologi Live Camera dan Optical Character Recognition (OCR), aplikasi ini mengonversi gambar koran atau berkas fisik menjadi teks digital yang kemudian disimpan ke database dan diekspor ke Microsoft Excel.

## Fitur Utama
1. **Live Camera Scanner:** Membuka kamera langsung dari halaman web menggunakan WebRTC API.
2. **Tesseract.js OCR:** Membaca teks bahasa Indonesia dari tangkapan kamera secara *real-time*.
3. **Smart Preview & Edit:** Data teks mentah dapat diedit atau dikoreksi sebelum disimpan.
4. **Excel Report Generator:** Ekspor laporan rekapitulasi bulanan ke dalam format `.xlsx` dengan cepat.

## Prasyarat (Prerequisites)
Pastikan sistem Anda sudah terinstal:
* PHP (>= 8.1 direkomendasikan)
* Composer
* MySQL Server (bisa melalui XAMPP/Laragon)
* Node.js & NPM (Opsional untuk asset bundling Laravel)

## Panduan Instalasi (Development)
1. *Clone* atau salin direktori repositori ini ke komputer Anda.
2. Buka terminal di dalam folder proyek.
3. Jalankan perintah `composer install` untuk mengunduh semua *dependencies* Laravel.
4. Salin file `.env.example` menjadi `.env` dan atur konfigurasi *database*:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database_anda
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Jalankan `php artisan key:generate`.
6. Lakukan migrasi *database* dengan menjalankan `php artisan migrate`.
7. Jika ada, jalankan seeder dengan `php artisan db:seed` untuk mengisi data awal media.
8. Jalankan server lokal: `php artisan serve`.

## Catatan Penting Mengenai Fitur Kamera
Untuk alasan keamanan browser modern, API `navigator.mediaDevices.getUserMedia` **hanya dapat berjalan** pada koneksi aman (**HTTPS**) atau lingkungan pengembangan lokal (**localhost**). Jika diakses dari HP via jaringan lokal (misal: `192.168.x.x`), browser akan menolak akses kamera kecuali disajikan dengan HTTPS.

## Kredit
Proyek ini dibuat sebagai bagian dari pelaksanaan Praktik Kerja Lapangan (PKL) Tema Rekayasa Perangkat Lunak (RPL) di Diskominfo Kabupaten Bogor.
