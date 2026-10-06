/**
 * Sistem Ekstraksi Data Berita Kemitraan - WebRTC & Gemini Vision AI
 * Diskominfo Kabupaten Bogor
 * Versi 3.0: Multi-Halaman + AI Gemini Vision (Menggantikan Tesseract.js OCR)
 */

document.addEventListener('DOMContentLoaded', () => {
    // Multi-Page Gallery Elements
    const pagesGallery = document.getElementById('pages-gallery');
    const pagesThumbnails = document.getElementById('pages-thumbnails');
    const pageCount = document.getElementById('page-count');
    const btnClearPages = document.getElementById('btn-clear-pages');
    const btnExtract = document.getElementById('btn-extract');

    // AI Progress Elements
    const aiProgressBox = document.getElementById('ai-progress-box');
    const aiStatusText = document.getElementById('ai-status-text');
    const aiProgressBar = document.getElementById('ai-progress-bar');
    const aiProgressIcon = document.getElementById('ai-progress-icon');

    // Input Elements
    const fileUpload = document.getElementById('file-upload');
    const nativeCameraUpload = document.getElementById('native-camera-upload');
    const dropzoneSingle = document.getElementById('dropzone-single');

    // Form Elements
    const previewBox = document.getElementById('preview-box');
    const previewPlaceholder = document.getElementById('preview-placeholder');
    const rawTextOcr = document.getElementById('raw_text_ocr');
    const namaMedia = document.getElementById('nama_media');
    const judulBerita = document.getElementById('judul_berita');
    const linkBerita = document.getElementById('link_berita');
    const tanggalTayang = document.getElementById('tanggal_tayang');
    const tanggalKegiatan = document.getElementById('tanggal_kegiatan');
    const keterangan = document.getElementById('keterangan');
    const hiddenPhotosContainer = document.getElementById('hidden-photos-container');
    const btnResetForm = document.getElementById('btn-reset-form');

    // State
    let capturedPages = []; // Array of base64 images

    // References for UX elements
    const verificationSection = document.getElementById('verification-form-section');
    const btnSubmit = document.getElementById('btn-submit-rekap');
    const formRekap = document.getElementById('form-rekap');

    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Drag and drop pada dropzone single kliping
    if (dropzoneSingle) {
        ['dragenter', 'dragover'].forEach(name => {
            dropzoneSingle.addEventListener(name, (e) => {
                e.preventDefault();
                dropzoneSingle.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(name => {
            dropzoneSingle.addEventListener(name, (e) => {
                e.preventDefault();
                dropzoneSingle.classList.remove('dragover');
            });
        });
        dropzoneSingle.addEventListener('drop', (e) => {
            const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            if (files.length > 0) {
                handleUploadedFiles(files);
            }
        });
    }

    // ============================================================
    // 1. Multi-Page Capture Management
    // ============================================================

    /**
     * Tambah halaman baru ke galeri dari data URL (base64)
     */
    function addPage(dataUrl) {
        capturedPages.push(dataUrl);
        // Dismiss scanner hint setelah foto pertama diambil
        if (!hintDismissed && scannerHint) {
            scannerHint.style.opacity = '0';
            setTimeout(() => { if (scannerHint) scannerHint.style.display = 'none'; }, 400);
            hintDismissed = true;
        }
        updateGalleryUI();
    }

    /**
     * Hapus halaman tertentu dari galeri
     */
    function removePage(index) {
        capturedPages.splice(index, 1);
        updateGalleryUI();
    }

    /**
     * Hapus semua halaman
     */
    function clearAllPages() {
        capturedPages = [];
        hintDismissed = false;
        // Tampilkan kembali scanner hint
        if (scannerHint) {
            scannerHint.style.display = 'block';
            scannerHint.style.opacity = '1';
        }
        updateGalleryUI();
    }

    /**
     * Perbarui tampilan galeri thumbnail dan tombol
     */
    function updateGalleryUI() {
        const count = capturedPages.length;
        pageCount.textContent = count;

        if (count > 0) {
            pagesGallery.style.display = 'block';
            btnExtract.style.display = 'inline-flex';
        } else {
            pagesGallery.style.display = 'none';
            btnExtract.style.display = 'none';
        }

        // Render thumbnail strip
        pagesThumbnails.innerHTML = '';
        capturedPages.forEach((dataUrl, i) => {
            const thumb = document.createElement('div');
            thumb.className = 'page-thumb';
            thumb.innerHTML = `
                <img src="${dataUrl}" alt="Halaman ${i + 1}">
                <span class="page-number">Hlm ${i + 1}</span>
                <button type="button" class="btn-remove-page" title="Hapus halaman ini" data-index="${i}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            pagesThumbnails.appendChild(thumb);
        });

        // Event listener untuk tombol hapus per-halaman
        pagesThumbnails.querySelectorAll('.btn-remove-page').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const idx = parseInt(btn.getAttribute('data-index'));
                removePage(idx);
            });
        });

        // Update preview box
        updatePreviewBox();

        // Update hidden form inputs
        updateHiddenPhotoInputs();
    }

    /**
     * Update pratinjau foto di form verifikasi
     */
    function updatePreviewBox() {
        // Hapus gambar preview lama (tapi jangan hapus placeholder)
        previewBox.querySelectorAll('.preview-img-item').forEach(el => el.remove());

        if (capturedPages.length > 0) {
            if (previewPlaceholder) previewPlaceholder.style.display = 'none';
            capturedPages.forEach((dataUrl, i) => {
                const img = document.createElement('img');
                img.src = dataUrl;
                img.alt = `Halaman ${i + 1}`;
                img.className = 'preview-img-item';
                previewBox.appendChild(img);
            });
        } else {
            if (previewPlaceholder) previewPlaceholder.style.display = 'block';
        }
    }

    /**
     * Update hidden inputs untuk form submit
     */
    function updateHiddenPhotoInputs() {
        hiddenPhotosContainer.innerHTML = '';
        capturedPages.forEach((dataUrl, i) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `foto_base64[${i}]`;
            input.value = dataUrl;
            hiddenPhotosContainer.appendChild(input);
        });
    }



    // ============================================================
    // 4. Upload dari File / Galeri HP & Native Camera Capture
    // ============================================================
    function handleUploadedFiles(files) {
        if (!files || !files.length) return;

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (event) => {
                addPage(event.target.result);
            };
            reader.readAsDataURL(file);
        });
    }

    if (fileUpload) {
        fileUpload.addEventListener('change', (e) => {
            handleUploadedFiles(Array.from(e.target.files));
            fileUpload.value = '';
        });
    }

    if (nativeCameraUpload) {
        nativeCameraUpload.addEventListener('change', (e) => {
            handleUploadedFiles(Array.from(e.target.files));
            nativeCameraUpload.value = '';
        });
    }

    // ============================================================
    // 5. Hapus Semua Halaman
    // ============================================================
    btnClearPages.addEventListener('click', () => {
        if (confirm('Hapus semua halaman yang telah difoto?')) {
            clearAllPages();
        }
    });

    // ============================================================
    // 6. Ekstraksi AI Gemini
    // ============================================================
    btnExtract.addEventListener('click', async () => {
        if (capturedPages.length === 0) {
            alert('Belum ada halaman yang difoto. Ambil foto minimal 1 halaman terlebih dahulu.');
            return;
        }

        // Tampilkan progress
        aiProgressBox.style.display = 'block';
        aiStatusText.textContent = `Mengirim ${capturedPages.length} halaman ke AI Gemini...`;
        aiProgressBar.style.width = '40%';
        aiProgressBar.style.backgroundColor = 'var(--primary)';
        aiProgressIcon.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        btnExtract.disabled = true;
        btnFoto.disabled = true;

        try {
            const response = await fetch('/scan/extract', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    images: capturedPages,
                }),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal mengekstrak data dari gambar.');
            }

            const data = result.data;

            // Auto-fill form fields
            if (data.nama_media && namaMedia) namaMedia.value = data.nama_media;
            if (data.judul_berita && judulBerita) judulBerita.value = data.judul_berita;
            if (data.tanggal_tayang && tanggalTayang) tanggalTayang.value = data.tanggal_tayang;
            if (data.tanggal_kegiatan && tanggalKegiatan) tanggalKegiatan.value = data.tanggal_kegiatan;
            if (data.link_berita && linkBerita) linkBerita.value = data.link_berita;
            if (data.keterangan && keterangan) keterangan.value = data.keterangan;
            if (data.raw_text && rawTextOcr) rawTextOcr.value = data.raw_text;

            // Update progress: sukses
            aiStatusText.textContent = '✅ Ekstraksi berhasil! Silakan periksa dan koreksi data di bawah sebelum disimpan.';
            aiProgressBar.style.width = '100%';
            aiProgressBar.style.backgroundColor = '#10b981';
            aiProgressIcon.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #10b981;"></i>';

            // Tampilkan form verifikasi (hanya saat berhasil)
            if (verificationSection) {
                verificationSection.style.display = 'block';
                verificationSection.style.animation = 'fadeInUp 0.4s ease';
            }

            // Scroll ke form verifikasi
            document.getElementById('verification-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });

        } catch (error) {
            console.error("AI Extract Error:", error);
            aiStatusText.textContent = `❌ Gagal: ${error.message}`;
            aiProgressBar.style.backgroundColor = '#ef4444';
            aiProgressBar.style.width = '100%';
            aiProgressIcon.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: #ef4444;"></i>';
        } finally {
            btnExtract.disabled = false;
            btnFoto.disabled = false;
        }
    });

    // ============================================================
    // 7. Reset Formulir
    // ============================================================
    btnResetForm.addEventListener('click', () => {
        document.getElementById('form-rekap').reset();
        clearAllPages();
        aiProgressBox.style.display = 'none';
        tanggalTayang.value = new Date().toISOString().split('T')[0];
        // Sembunyikan kembali form verifikasi
        if (verificationSection) verificationSection.style.display = 'none';
    });

    // ============================================================
    // 8. Loading State pada Submit Form
    // ============================================================
    if (formRekap && btnSubmit) {
        formRekap.addEventListener('submit', () => {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        });
    }
});
