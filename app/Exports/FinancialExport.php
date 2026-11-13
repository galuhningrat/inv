<?php

namespace App\Exports;

use App\Models\Asset;
use App\Models\Maintenance;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinancialExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            [
                'Total Aset',
                Asset::count(),
            ],
            [
                'Total Nilai Aset',
                'Rp ' . number_format(Asset::sum('price'), 0, ',', '.'),
            ],
            [
                'Total Biaya Pemeliharaan',
                'Rp ' . number_format(Maintenance::sum('cost'), 0, ',', '.'),
            ],
            [
                'Rata-rata Nilai Aset',
                'Rp ' . number_format(Asset::avg('price'), 0, ',', '.'),
            ],
            [
                'Jumlah Record Pemeliharaan',
                Maintenance::count(),
            ],
        ];
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

    public function title(): string
    {
        return 'Laporan Keuangan';
    }
}