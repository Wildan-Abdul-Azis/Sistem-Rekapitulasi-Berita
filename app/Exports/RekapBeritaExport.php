<?php

namespace App\Exports;

use App\Models\RekapBerita;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapBeritaExport implements WithMultipleSheets, Export
{
    use Exportable;
    protected ?string $bulan;
    protected ?int $tahun;

    /**
     * @param string|null $bulan Format: YYYY-MM (contoh: "2026-07")
     */
    public function __construct(?string $bulan = null)
    {
        if ($bulan) {
            $parts = explode('-', $bulan);
            $this->tahun = (int) $parts[0];
            $this->bulan = (int) $parts[1];
        } else {
            $this->tahun = null;
            $this->bulan = null;
        }
    }

    /**
     * Buat sheet per media.
     * Setiap media mitra mendapat tab/sheet tersendiri.
     */
    public function sheets(): array
    {
        $query = RekapBerita::orderBy('tanggal_tayang', 'asc');

        if ($this->tahun && $this->bulan) {
            $query->whereYear('tanggal_tayang', $this->tahun)
                  ->whereMonth('tanggal_tayang', $this->bulan);
        }

        $allData = $query->get();

        // Group data berdasarkan nama_media
        $grouped = $allData->groupBy('nama_media');

        // Tentukan label bulan untuk judul sheet
        $bulanLabel = '';
        if ($this->tahun && $this->bulan) {
            $carbon = Carbon::createFromDate($this->tahun, $this->bulan, 1);
            $bulanLabel = $carbon->locale('id')->isoFormat('MMMM');
        }

        $sheets = [];

        // Sheet 1: Tab gabungan Semua Berita (agar langsung terlihat seluruh rekap data)
        if ($allData->isNotEmpty()) {
            $allTitle = $bulanLabel ? "Semua ($bulanLabel)" : 'Semua Berita';
            $sheets[] = new RekapPerMediaSheet($allData, mb_substr($allTitle, 0, 31), 'Semua Media');
        }

        // Sheet per masing-masing media mitra
        foreach ($grouped as $namaMedia => $items) {
            // Nama sheet: "NamaMedia BulanTahun" (contoh: "Reformasi Aktual Juli")
            $sheetTitle = $namaMedia;
            if ($bulanLabel) {
                $sheetTitle .= ' ' . $bulanLabel;
            }

            // Excel sheet name max 31 chars
            $sheetTitle = mb_substr($sheetTitle, 0, 31);

            $sheets[] = new RekapPerMediaSheet($items, $sheetTitle, $namaMedia);
        }

        // Jika tidak ada data sama sekali, buat satu sheet kosong
        if (empty($sheets)) {
            $label = $bulanLabel ?: 'Semua Periode';
            $sheets[] = new RekapPerMediaSheet(collect(), $label, '-');
        }

        return $sheets;
    }
}
