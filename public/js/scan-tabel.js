/**
 * Sistem Ekstraksi Data Berita Kemitraan - Diskominfo Kab. Bogor
 * Fitur 2: Pindai & Ekstraksi Tabel Rekapitulasi Media (Multi-Berita / PDF)
 *
 * Kode JavaScript dirancang sederhana, terstruktur, dan mudah dipelajari
 * untuk keperluan tugas/laporan mahasiswa PKL.
 */

document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // Berkas & Dropzone
    const dropzoneArea = document.getElementById('dropzone-area');
    const fileInput = document.getElementById('file-input-tabel');
    const nativeCameraTabel = document.getElementById('native-camera-tabel');
    const filesGallery = document.getElementById('files-gallery');
    const filesListContainer = document.getElementById('files-list-container');
    const filesCountBadge = document.getElementById('files-count-badge');
    const btnClearFiles = document.getElementById('btn-clear-files');

    // AI Ekstraksi & Progress
    const btnExtractTabel = document.getElementById('btn-extract-tabel');
    const aiProgressBox = document.getElementById('ai-tabel-progress');
    const aiStatusText = document.getElementById('ai-tabel-status');
    const aiProgressBar = document.getElementById('ai-tabel-bar');
    const aiProgressIcon = document.getElementById('ai-tabel-icon');

    // Bagian Hasil & Form Verifikasi
    const resultSection = document.getElementById('tabel-result-section');
    const badgeTotalItems = document.getElementById('badge-total-items');
    const btnSaveCount = document.getElementById('btn-save-count');
    const massNamaMedia = document.getElementById('mass-nama-media');
    const btnApplyMassMedia = document.getElementById('btn-apply-mass-media');
    const btnAddTableRow = document.getElementById('btn-add-table-row');
    const tableRowsBody = document.getElementById('tabel-rows-body');
    const btnResetExtracted = document.getElementById('btn-reset-extracted');
    const formRekapTabel = document.getElementById('form-rekap-tabel');
    const btnSubmitStore = document.getElementById('btn-submit-tabel-store');

    // CSRF Token Laravel untuk keamanan request AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // State data lokal
    let selectedFiles = []; // Array of object: { name, type, dataUrl }

    // -------------------------------------------------------------
    // 3. Logika Drag & Drop dan Upload File / Kamera HP Bawaan
    // -------------------------------------------------------------
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzoneArea.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzoneArea.classList.remove('dragover');
        });
    });

    dropzoneArea.addEventListener('drop', (e) => {
        const files = Array.from(e.dataTransfer.files);
        prosesFileUnggahan(files);
    });

    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        prosesFileUnggahan(files);
        fileInput.value = ''; // Reset input agar file yang sama bisa dipilih kembali jika perlu
    });

    if (nativeCameraTabel) {
        nativeCameraTabel.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            prosesFileUnggahan(files);
            nativeCameraTabel.value = '';
        });
    }

    function prosesFileUnggahan(files) {
        if (!files || files.length === 0) return;

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (event) => {
                addFileItem(file.name, file.type, event.target.result);
            };
            reader.readAsDataURL(file);
        });
    }

    // -------------------------------------------------------------
    // 4. Pengelolaan Berkas / Galeri Lembaran
    // -------------------------------------------------------------
    function addFileItem(name, type, dataUrl) {
        selectedFiles.push({ name, type, dataUrl });
        renderFilesGallery();
    }

    function removeFileItem(index) {
        selectedFiles.splice(index, 1);
        renderFilesGallery();
    }

    btnClearFiles.addEventListener('click', () => {
        if (confirm('Apakah Anda yakin ingin menghapus semua berkas yang telah dipilih?')) {
            selectedFiles = [];
            renderFilesGallery();
        }
    });

    function renderFilesGallery() {
        const total = selectedFiles.length;
        filesCountBadge.textContent = total;

        if (total > 0) {
            filesGallery.style.display = 'block';
            btnExtractTabel.style.display = 'inline-flex';
        } else {
            filesGallery.style.display = 'none';
            btnExtractTabel.style.display = 'none';
        }

        filesListContainer.innerHTML = '';
        selectedFiles.forEach((item, index) => {
            const isPdf = item.type.includes('pdf') || item.name.toLowerCase().endsWith('.pdf');
            const card = document.createElement('div');
            card.className = 'file-item-card';
            card.innerHTML = `
                <div class="icon-file ${isPdf ? 'icon-pdf' : ''}">
                    <i class="${isPdf ? 'fa-solid fa-file-pdf' : 'fa-solid fa-file-image'}"></i>
                </div>
                <div class="file-info">
                    <div class="file-name" title="${item.name}">${item.name}</div>
                    <div class="file-size">${isPdf ? 'Dokumen PDF' : 'Gambar Lembar ' + (index + 1)}</div>
                </div>
                <button type="button" class="btn-remove-file" title="Hapus berkas ini" data-index="${index}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            filesListContainer.appendChild(card);
        });

        // Event listener hapus item
        filesListContainer.querySelectorAll('.btn-remove-file').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const idx = parseInt(btn.getAttribute('data-index'));
                removeFileItem(idx);
            });
        });
    }

    // -------------------------------------------------------------
    // 5. Ekstraksi AI Gemini untuk Tabel
    // -------------------------------------------------------------
    btnExtractTabel.addEventListener('click', async () => {
        if (selectedFiles.length === 0) {
            alert('Silakan pilih minimal satu berkas PDF atau gambar lembaran tabel.');
            return;
        }

        // Tampilkan indikator proses
        aiProgressBox.style.display = 'block';
        aiStatusText.textContent = `Mengirim ${selectedFiles.length} berkas ke AI Gemini untuk membaca tabel...`;
        aiProgressBar.style.width = '45%';
        aiProgressBar.style.backgroundColor = 'var(--primary)';
        aiProgressIcon.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        btnExtractTabel.disabled = true;

        try {
            const fileDataList = selectedFiles.map(f => f.dataUrl);

            const response = await fetch('/scan-tabel/extract', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    files: fileDataList,
                }),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal membaca isi tabel.');
            }

            const data = result.data;
            const beritaList = data.berita || [];

            if (beritaList.length === 0) {
                throw new Error('AI tidak menemukan baris berita pada berkas ini. Pastikan foto/PDF tabel terbaca dengan jelas.');
            }

            // Jika ada nama media default dari AI, isi ke input massal
            if (data.nama_media_default && massNamaMedia) {
                massNamaMedia.value = data.nama_media_default;
            }

            // Render seluruh baris ke dalam tabel formulir
            renderTableRows(beritaList, data.nama_media_default);

            // Update status sukses
            aiStatusText.textContent = `✅ Berhasil mengekstrak ${beritaList.length} baris berita! Silakan koreksi sebelum disimpan.`;
            aiProgressBar.style.width = '100%';
            aiProgressBar.style.backgroundColor = '#10b981';
            aiProgressIcon.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #10b981;"></i>';

            // Tampilkan bagian hasil
            resultSection.style.display = 'block';
            resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

        } catch (err) {
            console.error('Ekstraksi Tabel Gagal:', err);
            aiStatusText.textContent = `❌ Kesalahan: ${err.message}`;
            aiProgressBar.style.backgroundColor = '#ef4444';
            aiProgressBar.style.width = '100%';
            aiProgressIcon.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: #ef4444;"></i>';
        } finally {
            btnExtractTabel.disabled = false;
        }
    });

    // -------------------------------------------------------------
    // 6. Render dan Manipulasi Baris Tabel Hasil Scan
    // -------------------------------------------------------------
    function renderTableRows(items, defaultMedia = '') {
        tableRowsBody.innerHTML = '';

        items.forEach((item, index) => {
            const row = createTableRowElement(index, item, defaultMedia);
            tableRowsBody.appendChild(row);
        });

        updateRowCounts();
    }

    function createTableRowElement(index, item = {}, defaultMedia = '') {
        const tr = document.createElement('tr');
        tr.className = 'tabel-data-row';

        const tanggalTayang = item.tanggal_tayang || new Date().toISOString().split('T')[0];
        const tanggalKegiatan = item.tanggal_kegiatan || tanggalTayang;
        const judul = item.judul_berita || '';
        const media = item.nama_media || defaultMedia || '';
        const link = item.link_berita || '';
        const keterangan = item.keterangan || '';

        tr.innerHTML = `
            <td style="text-align: center; font-weight: 700; color: var(--text-muted); padding-top: 12px;" class="row-number">
                ${index + 1}
            </td>
            <td>
                <input type="date" name="items[${index}][tanggal_tayang]" class="tabel-input-field input-tgl-tayang" value="${tanggalTayang}" required>
            </td>
            <td>
                <input type="date" name="items[${index}][tanggal_kegiatan]" class="tabel-input-field input-tgl-kegiatan" value="${tanggalKegiatan}">
            </td>
            <td>
                <textarea name="items[${index}][judul_berita]" class="tabel-input-field input-judul" rows="2" placeholder="Judul Berita" required style="resize: vertical;">${escapeHtml(judul)}</textarea>
            </td>
            <td>
                <input type="text" name="items[${index}][nama_media]" class="tabel-input-field input-nama-media" value="${escapeHtml(media)}" placeholder="Nama Media" required>
            </td>
            <td>
                <input type="url" name="items[${index}][link_berita]" class="tabel-input-field input-link" value="${escapeHtml(link)}" placeholder="https://...">
            </td>
            <td>
                <input type="text" name="items[${index}][keterangan]" class="tabel-input-field input-keterangan" value="${escapeHtml(keterangan)}" placeholder="Keterangan / Tayang">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn btn-outline btn-sm text-danger btn-hapus-baris" title="Hapus baris ini" style="padding: 6px 10px; border-radius: 6px;">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        // Event listener tombol hapus per baris
        tr.querySelector('.btn-hapus-baris').addEventListener('click', () => {
            tr.remove();
            reindexRows();
        });

        return tr;
    }

    // Fungsi utilitas untuk membersihkan karakter khusus HTML
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // Susun ulang penomoran indeks baris form
    function reindexRows() {
        const rows = tableRowsBody.querySelectorAll('.tabel-data-row');
        rows.forEach((row, i) => {
            row.querySelector('.row-number').textContent = i + 1;

            row.querySelector('.input-tgl-tayang').name = `items[${i}][tanggal_tayang]`;
            row.querySelector('.input-tgl-kegiatan').name = `items[${i}][tanggal_kegiatan]`;
            row.querySelector('.input-judul').name = `items[${i}][judul_berita]`;
            row.querySelector('.input-nama-media').name = `items[${i}][nama_media]`;
            row.querySelector('.input-link').name = `items[${i}][link_berita]`;
            row.querySelector('.input-keterangan').name = `items[${i}][keterangan]`;
        });

        updateRowCounts();
    }

    // Perbarui jumlah baris pada tombol dan badge
    function updateRowCounts() {
        const count = tableRowsBody.querySelectorAll('.tabel-data-row').length;
        badgeTotalItems.textContent = `${count} Berita Ditemukan`;
        btnSaveCount.textContent = count;

        if (count === 0) {
            btnSubmitStore.disabled = true;
        } else {
            btnSubmitStore.disabled = false;
        }
    }

    // -------------------------------------------------------------
    // 7. Aksi Toolbar Cepat (Terapkan Nama Media & Tambah Baris)
    // -------------------------------------------------------------
    // Terapkan nama media serentak ke semua baris
    btnApplyMassMedia.addEventListener('click', () => {
        const mediaVal = massNamaMedia.value.trim();
        if (!mediaVal) {
            alert('Silakan ketikkan nama media terlebih dahulu.');
            massNamaMedia.focus();
            return;
        }

        const mediaInputs = tableRowsBody.querySelectorAll('.input-nama-media');
        mediaInputs.forEach(input => {
            input.value = mediaVal;
        });

        alert(`Nama media berhasil diterapkan ke ${mediaInputs.length} baris.`);
    });

    // Tambah baris baru manual jika ada berita yang terlewat
    btnAddTableRow.addEventListener('click', () => {
        const currentCount = tableRowsBody.querySelectorAll('.tabel-data-row').length;
        const defaultMedia = massNamaMedia.value.trim();
        const newRow = createTableRowElement(currentCount, {}, defaultMedia);
        tableRowsBody.appendChild(newRow);
        updateRowCounts();

        // Scroll baris baru ke pandangan
        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    // Reset dan kosongkan hasil ekstraksi
    btnResetExtracted.addEventListener('click', () => {
        if (confirm('Apakah Anda yakin ingin mengosongkan hasil tabel ini?')) {
            tableRowsBody.innerHTML = '';
            updateRowCounts();
            resultSection.style.display = 'none';
            aiProgressBox.style.display = 'none';
        }
    });

    // Loading status saat form disubmit
    formRekapTabel.addEventListener('submit', () => {
        const rowsCount = tableRowsBody.querySelectorAll('.tabel-data-row').length;
        if (rowsCount === 0) {
            alert('Tidak ada data berita untuk disimpan.');
            return false;
        }

        btnSubmitStore.disabled = true;
        btnSubmitStore.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan ke Database...';
    });
});
