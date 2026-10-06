# Task Breakdown & Timeline (Kanban)
Estimasi Pengerjaan: 3 Minggu.

## Minggu 1: Persiapan & Basis Data
- [ ] **Task 1.1:** Setup *environment* lokal (XAMPP/Laragon, Node.js, Composer).
- [ ] **Task 1.2:** Instalasi framework Laravel terbaru.
- [ ] **Task 1.3:** Membuat struktur basis data di MySQL (`master_media` & `rekap_berita`).
- [ ] **Task 1.4:** Membuat Migration dan Model di Laravel untuk kedua tabel tersebut.
- [ ] **Task 1.5:** Membuat data contoh (*Seeder*) untuk tabel `master_media` agar siap digunakan.

## Minggu 2: Frontend & Ekstraksi OCR
- [ ] **Task 2.1:** Membuat halaman/view UI untuk form scanner (`scan.blade.php`).
- [ ] **Task 2.2:** Integrasi HTML5 WebRTC API untuk mengakses dan menampilkan kamera belakang (`navigator.mediaDevices`).
- [ ] **Task 2.3:** Implementasi tombol untuk menangkap frame dari `<video>` ke dalam elemen `<canvas>`.
- [ ] **Task 2.4:** Mengintegrasikan *library* Tesseract.js dan mengatur agar menggunakan bahasa Indonesia (`'ind'`).
- [ ] **Task 2.5:** Menghubungkan *output* Tesseract.js ke form `textarea` agar pengguna bisa mengoreksi hasilnya.

## Minggu 3: Backend, Integrasi, & Ekspor Excel
- [ ] **Task 3.1:** Membuat *route* dan metode `store` di Controller untuk menyimpan hasil *scan* dan data formulir ke dalam *database*.
- [ ] **Task 3.2:** Menginstall *library* `Maatwebsite/Laravel-Excel`.
- [ ] **Task 3.3:** Membuat class `RekapBeritaExport` untuk mengatur format *query* dan *heading* Excel.
- [ ] **Task 3.4:** Membuat antarmuka/halaman untuk memilih bulan dan tombol *download* rekap.
- [ ] **Task 3.5:** Pengujian (*testing*) keseluruhan aplikasi, penanganan *error* kamera (HTTPS/*localhost* requirement), dan *bug fixing*.
- [ ] **Task 3.6:** Persiapan laporan PKL dan presentasi.
