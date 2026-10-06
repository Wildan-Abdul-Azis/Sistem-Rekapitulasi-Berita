<?php

namespace App\Http\Controllers;

use App\Exports\RekapBeritaExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Unduh laporan rekapitulasi berita dalam format Excel (.xlsx).
     * Data akan dipecah menjadi beberapa sheet per media mitra.
     */
    public function export(Request $request)
    {
        $bulan = $request->filled('bulan') ? $request->bulan : null;

        $fileSuffix = 'Semua';
        if ($bulan) {
            $parts = explode('-', $bulan);
            if (count($parts) === 2) {
                $carbon = Carbon::createFromDate($parts[0], $parts[1], 1);
                $fileSuffix = $carbon->format('Y_m');
            }
        }

        $fileName = 'Rekap_Berita_Diskominfo_' . $fileSuffix . '.xlsx';

        return Excel::download(new RekapBeritaExport($bulan), $fileName);
    }
}
