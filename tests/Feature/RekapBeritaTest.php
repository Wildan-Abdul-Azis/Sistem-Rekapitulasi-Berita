<?php

namespace Tests\Feature;

use App\Models\MasterMedia;
use App\Models\RekapBerita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapBeritaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_dashboard_can_be_rendered(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_scan_page_can_be_rendered(): void
    {
        $response = $this->get('/scan');
        $response->assertStatus(200);
        $response->assertSee('link_berita');
    }

    public function test_rekap_index_shows_stored_data(): void
    {
        RekapBerita::create([
            'nama_media' => 'Intel Media',
            'judul_berita' => 'Kabupaten Bogor Didorong Jadi Role Model Transformasi Masjid Digital',
            'link_berita' => 'https://intelmediaupdates.com/kabupaten-bogor-didorong-jadi-role-model-transformasi-masjid-digital/',
            'tanggal_tayang' => '2026-02-23',
            'tanggal_kegiatan' => '2026-02-23',
        ]);

        $response = $this->get('/rekap');
        $response->assertStatus(200);
        $response->assertSee('Kabupaten Bogor Didorong Jadi Role Model Transformasi Masjid Digital');
        $response->assertSee('intelmediaupdates.com');
    }

    public function test_export_excel_can_be_downloaded(): void
    {
        $response = $this->get('/export/excel');
        $response->assertStatus(200);
    }

    public function test_scanner_store_auto_fills_tanggal_kegiatan_from_tanggal_tayang(): void
    {
        $postData = [
            'nama_media' => 'Radar Bogor',
            'judul_berita' => 'Uji Coba Berita Fallback Tanggal Kegiatan',
            'link_berita' => 'https://radarbogor.id/berita-uji-coba',
            'tanggal_tayang' => '2026-02-23',
            // tanggal_kegiatan sengaja dikosongkan
            'tanggal_kegiatan' => '',
            'raw_text_ocr' => 'Teks kliping uji coba.',
        ];

        $response = $this->post('/scan', $postData);
        $response->assertRedirect('/rekap');

        $rekap = RekapBerita::where('judul_berita', 'Uji Coba Berita Fallback Tanggal Kegiatan')->first();
        $this->assertNotNull($rekap);
        $this->assertEquals('2026-02-23', $rekap->tanggal_tayang->format('Y-m-d'));
        // Pastikan tanggal_kegiatan otomatis terisi sama dengan tanggal_tayang
        $this->assertEquals('2026-02-23', $rekap->tanggal_kegiatan->format('Y-m-d'));
        $this->assertEquals('https://radarbogor.id/berita-uji-coba', $rekap->link_berita);
    }

    public function test_scanner_store_supports_new_media_creation(): void
    {
        $postData = [
            'nama_media' => 'Koran Sinar Pagi',
            'judul_berita' => 'Berita Media Baru Sinar Pagi',
            'tanggal_tayang' => '2026-03-01',
        ];

        $response = $this->post('/scan', $postData);
        $response->assertRedirect('/rekap');

        $this->assertDatabaseHas('rekap_berita', [
            'nama_media' => 'Koran Sinar Pagi',
            'judul_berita' => 'Berita Media Baru Sinar Pagi',
        ]);
    }
}
