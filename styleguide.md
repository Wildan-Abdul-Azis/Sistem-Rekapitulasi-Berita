# Styleguide & Aturan Kode

## 1. Prinsip Desain UI/UX
* **Mobile-First Design:** Karena pemindaian akan sering menggunakan kamera HP (kamera belakang), antarmuka halaman *scanner* harus dioptimalkan untuk layar *mobile* (responsif).
* **Keterbacaan & Kesederhanaan:** Halaman pratinjau (form koreksi OCR) harus bersih dari elemen yang mengganggu agar fokus pada perbaikan teks.
* **Aksesibilitas:** Gunakan kontras warna yang baik untuk tombol aksi (contoh: tombol Pindai dan Simpan).

## 2. Panduan Warna (Color Palette)
* **Primary (Biru):** `#007bff` (Untuk tombol aksi utama seperti 'Pindai Berita').
* **Success (Hijau):** `#28a745` (Untuk tombol 'Simpan' dan pesan sukses OCR).
* **Danger (Merah):** `#d32f2f` (Untuk pesan *error* atau gagal akses kamera).
* **Background (Abu-abu Terang):** `#f8f9fa` (Untuk warna latar web).
* **Text (Abu-abu Gelap/Hitam):** `#333333` (Warna teks standar).

## 3. Tipografi
* **Font-Family:** Arial, Helvetica, atau sans-serif standar untuk memastikan performa yang cepat tanpa *loading font* eksternal berlebihan.
* **Ukuran Font:** 
  * Heading: 20px - 24px
  * Body text: 16px
  * Helper/Status text: 14px

## 4. Panduan Penulisan Kode (Code Conventions)
* **Frontend (HTML/JS):**
  * Gunakan Vanilla JS (*modern ES6 syntax* seperti `const`, `let`, `arrow functions`).
  * Pemanggilan elemen DOM menggunakan ID yang deskriptif (misal: `btn-pindai`, `raw_text_ocr`).
* **Backend (Laravel/PHP):**
  * Patuhi standar PSR-12 untuk penulisan PHP.
  * Penamaan Controller: `[Nama]Controller` (contoh: `RekapController`).
  * Penamaan Model: PascalCase, singular (contoh: `MasterMedia`, `RekapBerita`).
* **Database (MySQL):**
  * Penamaan tabel dan kolom menggunakan `snake_case` huruf kecil semua.
