<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /** @var array<string> */
    protected array $apiKeys = [];

    protected string $model;

    protected ?string $lastError = null;

    public function __construct()
    {
        $keys = config('services.gemini.api_keys');
        if (is_array($keys) && ! empty($keys)) {
            $this->apiKeys = $keys;
        } else {
            $single = config('services.gemini.api_key') ?? '';
            $this->apiKeys = $single ? array_values(array_filter(array_map('trim', explode(',', $single)))) : [];
        }

        $this->model = config('services.gemini.model', 'gemini-flash-latest');
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Ekstrak informasi berita tunggal dari gambar multi-halaman (Fitur 1: Scan 1 per 1).
     *
     * @param  array  $base64Images  Array gambar base64
     */
    public function extractBeritaFromImages(array $base64Images): ?array
    {
        $this->lastError = null;

        if (empty($base64Images)) {
            $this->lastError = 'Tidak ada gambar yang dikirim untuk dianalisis.';

            return null;
        }

        // Siapkan bagian prompt teks dan data gambar
        $parts = [
            ['text' => $this->buildPrompt()],
        ];

        $parts = array_merge($parts, $this->formatInlineData($base64Images));

        return $this->callGeminiApi($parts);
    }

    /**
     * Ekstrak data banyak berita sekaligus dari tabel rekapitulasi media (Fitur 2: Scan Tabel Rekap).
     * Mendukung berkas gambar lembaran tabel maupun dokumen PDF.
     *
     * @param  array  $base64Files  Array file base64 (gambar atau PDF)
     */
    public function extractTabelFromImages(array $base64Files): ?array
    {
        $this->lastError = null;

        if (empty($base64Files)) {
            $this->lastError = 'Tidak ada berkas/gambar tabel yang dikirim untuk dianalisis.';

            return null;
        }

        // Siapkan prompt instruksi khusus pembacaan tabel
        $parts = [
            ['text' => $this->buildPromptTabel()],
        ];

        $parts = array_merge($parts, $this->formatInlineData($base64Files));

        $result = $this->callGeminiApi($parts);

        if (! $result) {
            return null;
        }

        // Normalisasi format respons agar selalu memiliki array 'berita'
        if (isset($result['berita']) && is_array($result['berita'])) {
            return $result;
        }

        if (isset($result['items']) && is_array($result['items'])) {
            return [
                'nama_media_default' => $result['nama_media_default'] ?? '',
                'berita' => $result['items'],
            ];
        }

        if (is_array($result) && array_is_list($result)) {
            return [
                'nama_media_default' => '',
                'berita' => $result,
            ];
        }

        return $result;
    }

    /**
     * Format array file base64 menjadi part inline_data untuk Google Gemini API.
     */
    protected function formatInlineData(array $files): array
    {
        $inlineParts = [];

        foreach ($files as $file) {
            $mimeType = 'image/jpeg';
            $fileData = $file;

            // Jika file memiliki prefix data URL (contoh: data:image/jpeg;base64,... atau data:application/pdf;base64,...)
            if (str_starts_with($file, 'data:')) {
                if (preg_match('/^data:([a-zA-Z0-9\/\+\.-]+);base64,(.+)$/i', $file, $matches)) {
                    $mimeType = $matches[1];
                    $fileData = $matches[2];
                }
            }

            $inlineParts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $fileData,
                ],
            ];
        }

        return $inlineParts;
    }

    /**
     * Panggil Google Gemini API dengan rotasi kunci API dan model cadangan otomatis.
     */
    protected function callGeminiApi(array $parts): ?array
    {
        if (empty($this->apiKeys)) {
            $this->lastError = 'GEMINI_API_KEY belum dikonfigurasi di file .env';
            Log::error('GeminiService: '.$this->lastError);

            return null;
        }

        $totalKeys = count($this->apiKeys);
        $candidateModels = array_values(array_unique([
            $this->model,
            'gemini-flash-latest',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-3.1-flash-lite',
        ]));

        foreach ($candidateModels as $currentModel) {
            // Rotasi API Key jika kuota limit (429)
            foreach ($this->apiKeys as $index => $apiKey) {
                $keyNumber = $index + 1;
                $maskedKey = substr($apiKey, 0, 6).'...'.substr($apiKey, -4);
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent?key={$apiKey}";

                try {
                    $response = Http::withOptions([
                        'force_ip_resolve' => 'v4',
                        'version' => 1.1,
                    ])
                        ->withHeaders([
                            'Expect' => '',
                        ])
                        ->timeout(90)
                        ->post($url, [
                            'contents' => [
                                [
                                    'parts' => $parts,
                                ],
                            ],
                            'generationConfig' => [
                                'temperature' => 0.1,
                                'responseMimeType' => 'application/json',
                            ],
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        // Ekstrak teks balasan dari Gemini
                        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                        if (! $text) {
                            $this->lastError = 'Respons dari Gemini AI kosong atau tidak mengandung teks.';
                            Log::warning('GeminiService: Respons Gemini tidak mengandung teks', $data);

                            return null;
                        }

                        // Parse format JSON
                        $result = json_decode($text, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $this->lastError = 'Gagal membaca format JSON dari AI.';
                            Log::warning('GeminiService: Gagal parse JSON dari respons Gemini', [
                                'raw_text' => $text,
                            ]);

                            return null;
                        }

                        if ($currentModel !== $this->model) {
                            Log::info("GeminiService: Berhasil menggunakan model '{$currentModel}' (Key #{$keyNumber}).");
                        }

                        return $result;
                    }

                    $status = $response->status();
                    $body = $response->body();
                    $errorData = json_decode($body, true);
                    $apiMsg = $errorData['error']['message'] ?? $body;

                    // Jika kuota habis / rate limit (429)
                    if ($status === 429) {
                        Log::warning("GeminiService: Key #{$keyNumber} ({$maskedKey}) terkena kuota limit (429). Beralih ke key cadangan...");
                        if ($keyNumber < $totalKeys) {
                            continue;
                        }

                        $this->lastError = "Semua {$totalKeys} API Key Gemini sedang mencapai batas kuota (Rate Limit/429). Mohon tunggu beberapa saat.";

                        return null;
                    }

                    // Jika model 404 / 503, coba model cadangan
                    if ($status === 404 || $status === 503) {
                        Log::warning("GeminiService: Model '{$currentModel}' status {$status}. Mencoba model cadangan...");
                        break;
                    }

                    $this->lastError = "Google Gemini API Error ($status): $apiMsg";
                    Log::error('GeminiService: API error', [
                        'model' => $currentModel,
                        'key_number' => $keyNumber,
                        'status' => $status,
                        'body' => $body,
                    ]);

                    return null;

                } catch (\Exception $e) {
                    Log::error("GeminiService: Terjadi kesalahan pada Model '{$currentModel}' Key #{$keyNumber} ({$maskedKey})", [
                        'message' => $e->getMessage(),
                    ]);

                    if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'timeout')) {
                        Log::warning("GeminiService: Model '{$currentModel}' mengalami timeout. Mencoba model lain...");
                        break;
                    }

                    if ($keyNumber < $totalKeys) {
                        continue;
                    }

                    $this->lastError = 'Koneksi ke Gemini AI gagal: '.$e->getMessage();
                }
            }
        }

        return null;
    }

    /**
     * Prompt instruksi untuk Gemini AI membaca 1 berita kliping koran.
     */
    protected function buildPrompt(): string
    {
        return <<<'PROMPT'
Kamu adalah asisten AI untuk Dinas Komunikasi dan Informatika (Diskominfo) Kabupaten Bogor.

Tugasmu: Dari gambar-gambar berikut (bisa 1 sampai 3 halaman dari satu artikel berita yang sama), ekstrak informasi berita kliping/advertorial dengan teliti.

Gambar-gambar ini bisa berupa:
- Foto kliping koran fisik / hardfile advertorial
- Screenshot berita dari website
- Dokumen dengan format template iklan media

PENTING:
- Semua gambar yang diberikan adalah HALAMAN BERBEDA dari SATU ARTIKEL BERITA YANG SAMA
- Link berita biasanya ada di halaman TERAKHIR
- Baca SEMUA halaman dengan teliti sebelum mengekstrak data
- Jika tanggal kegiatan tidak ditemukan, gunakan tanggal tayang
- Keterangan biasanya berisi lokasi/instansi terkait (contoh: "Bupati Bogor", "Pemkab Bogor", dll)
- Untuk nama media, cari dari header koran, domain URL, byline, atau watermark

Kembalikan HANYA objek JSON dengan format berikut (tanpa penjelasan tambahan):

{
  "nama_media": "Nama media/koran/portal (contoh: Reformasi Aktual, Rekam24.com, Radar Bogor)",
  "judul_berita": "Judul lengkap berita",
  "tanggal_tayang": "YYYY-MM-DD (tanggal terbit/tayang)",
  "tanggal_kegiatan": "YYYY-MM-DD (tanggal kegiatan, jika tidak ada samakan dengan tanggal tayang)",
  "link_berita": "URL lengkap berita (jika ada, biasanya di halaman terakhir)",
  "keterangan": "Keterangan singkat tentang tokoh/instansi terkait (contoh: Bupati Bogor)",
  "raw_text": "Seluruh teks yang terbaca dari semua halaman, digabung menjadi satu paragraf"
}

Jika field tidak ditemukan, isi dengan string kosong "".
Pastikan format tanggal selalu YYYY-MM-DD.
PROMPT;
    }

    /**
     * Prompt instruksi untuk Gemini AI membaca tabel rekapitulasi publikasi media (banyak berita).
     */
    protected function buildPromptTabel(): string
    {
        return <<<'PROMPT'
Kamu adalah asisten entri data kemitraan media untuk Dinas Komunikasi dan Informatika (Diskominfo) Kabupaten Bogor.

Tugasmu: Dari gambar atau berkas PDF berikut, baca dan ekstrak SELURUH BARIS BERITA / IKLAN yang ada pada TABEL REKAPITULASI MEDIA.
Dokumen ini bisa memiliki 1 halaman atau beberapa halaman (multi-page).

PANDUAN PEMBACAAN TABEL:
1. Baca dan teliti seluruh baris pada tabel dari halaman pertama sampai halaman terakhir. Jangan sampai ada baris yang terlewat!
2. Kolom tabel umumnya memuat:
   - Nomor Urut
   - Tanggal (misal: "02-Jul-26", "03-Jul-26", "13-Jul-26", dll)
   - Judul Iklan / Berita (misal: "Rudy Susmanto Hadir di HUT Bhayangkara...")
   - Nama PT / Alamat (misal: "PT. BOGOR MEDIAPOLITAN")
   - Tayangan Iklan / Portal Media (misal: "Metropolitan.id")
   - Link Berita / URL (misal: "https://www.metropolitan.id/...")
   - Keterangan lain jika ada

3. Normalisasi field:
   - "nama_media": Ambil nama media utama dari header dokumen atau kolom Nama PT / Tayangan Iklan (contoh: "PT. BOGOR MEDIAPOLITAN" atau "Metropolitan.id").
   - "tanggal_tayang": Format tanggal wajib selalu diubah ke format standar ISO "YYYY-MM-DD" (contoh: "02-Jul-26" diubah menjadi "2026-07-02").
   - "tanggal_kegiatan": Jika tidak ada tanggal kegiatan terpisah, samakan dengan "tanggal_tayang".
   - "judul_berita": Judul lengkap berita/iklan tanpa nomor urut.
   - "link_berita": URL lengkap dari kolom link jika tersedia.
   - "keterangan": Isi dengan portal tayangan iklan (contoh: "Metropolitan.id") atau tokoh terkait (contoh: "Bupati Bogor").

KEMBALIKAN HANYA OBJEK JSON DENGAN FORMAT BERIKUT:
{
  "nama_media_default": "Nama Media Utama",
  "berita": [
    {
      "tanggal_tayang": "YYYY-MM-DD",
      "tanggal_kegiatan": "YYYY-MM-DD",
      "judul_berita": "Judul lengkap berita",
      "nama_media": "Nama Media",
      "link_berita": "https://...",
      "keterangan": "Keterangan / Media Tayang"
    }
  ]
}
PROMPT;
    }
}
