<?php

namespace Tests\Feature;

use App\Models\RekapBerita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScannerTabelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/scan-tabel');
        $response->assertRedirect('/login');
    }

    public function test_scan_tabel_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/scan-tabel');
        $response->assertStatus(200);
        $response->assertSee('Pindai');
        $response->assertSee('Ekstraksi Tabel Rekap Media');
        $response->assertSee('form-rekap-tabel');
        $response->assertSee('mass-nama-media');
    }

    public function test_scan_tabel_extract_validation_requires_files(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/scan-tabel/extract', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['files']);
    }

    public function test_scan_tabel_store_batch_saves_multiple_records(): void
    {
        $payload = [
            'items' => [
                [
                    'tanggal_tayang' => '2026-07-02',
                    'tanggal_kegiatan' => '2026-07-02',
                    'judul_berita' => 'Rudy Susmanto Hadir di HUT Bhayangkara, Tegaskan Sinergi Polri dan Pemkab Bogor Jadi Kunci Menjaga Keamanan Daerah',
                    'nama_media' => 'PT. BOGOR MEDIAPOLITAN',
                    'link_berita' => 'https://www.metropolitan.id/bogor-raya/95317321338/rudy-susmanto-hadir-di-hut-bhayangkara',
                    'keterangan' => 'Metropolitan.id',
                ],
                [
                    'tanggal_tayang' => '2026-07-02',
                    'tanggal_kegiatan' => '2026-07-02',
                    'judul_berita' => 'Rudy Susmanto Satukan Seluruh Jajaran Tata Babakan Madang, Tunjukkan Wajah Terbaik Kabupaten Bogor',
                    'nama_media' => 'PT. BOGOR MEDIAPOLITAN',
                    'link_berita' => 'https://www.metropolitan.id/bogor-raya/95317323862/rudy-susmanto-satukan-seluruh',
                    'keterangan' => 'Metropolitan.id',
                ],
                [
                    'tanggal_tayang' => '2026-07-03',
                    // tanggal kegiatan kosong untuk menguji fallback
                    'tanggal_kegiatan' => '',
                    'judul_berita' => 'Hadapi Musim Kemarau, Bupati Bogor Rudy Susmanto Gencarkan Program Normalisasi Saluran Air',
                    'nama_media' => 'PT. BOGOR MEDIAPOLITAN',
                    'link_berita' => 'https://www.metropolitan.id/bogor-raya/95317327456/hadapi-musim-kemarau',
                    'keterangan' => 'Metropolitan.id',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post('/scan-tabel', $payload);

        $response->assertRedirect('/rekap');
        $response->assertSessionHas('success');

        // Pastikan ketiga data berhasil disimpan ke database
        $this->assertEquals(3, RekapBerita::count());

        $this->assertDatabaseHas('rekap_berita', [
            'nama_media' => 'PT. BOGOR MEDIAPOLITAN',
            'judul_berita' => 'Rudy Susmanto Hadir di HUT Bhayangkara, Tegaskan Sinergi Polri dan Pemkab Bogor Jadi Kunci Menjaga Keamanan Daerah',
            'link_berita' => 'https://www.metropolitan.id/bogor-raya/95317321338/rudy-susmanto-hadir-di-hut-bhayangkara',
        ]);

        $itemPertama = RekapBerita::where('judul_berita', 'like', 'Rudy Susmanto Hadir%')->first();
        $this->assertNotNull($itemPertama);
        $this->assertEquals('2026-07-02', $itemPertama->tanggal_tayang->format('Y-m-d'));

        $itemKetiga = RekapBerita::where('judul_berita', 'like', 'Hadapi Musim Kemarau%')->first();
        $this->assertNotNull($itemKetiga);
        $this->assertEquals('2026-07-03', $itemKetiga->tanggal_tayang->format('Y-m-d'));
        // Fallback tanggal_kegiatan dari tanggal_tayang
        $this->assertEquals('2026-07-03', $itemKetiga->tanggal_kegiatan->format('Y-m-d'));
    }
}
