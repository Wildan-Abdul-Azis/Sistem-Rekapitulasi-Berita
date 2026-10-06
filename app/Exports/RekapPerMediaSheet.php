<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapPerMediaSheet implements FromView, ShouldAutoSize, WithTitle, Export
{
    protected $rekapList;
    protected string $sheetTitle;
    protected string $namaMedia;

    public function __construct($rekapList, string $sheetTitle, string $namaMedia)
    {
        $this->rekapList = $rekapList;
        $this->sheetTitle = $sheetTitle;
        $this->namaMedia = $namaMedia;
    }

    public function view(): View
    {
        return view('rekap.excel', [
            'rekapList' => $this->rekapList,
            'namaMedia' => $this->namaMedia,
        ]);
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
