<?php

namespace App\Exports;

use App\Models\Asset;
use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinancialExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        // 🔥 FIX: hitung dari batch, bukan dari kolom price yang sudah dihapus
        $totalValue = (int) DB::table('assets')
            ->join('asset_batches', 'assets.asset_batch_id', '=', 'asset_batches.id')
            ->whereNull('assets.deleted_at')
            ->sum('asset_batches.price');

        $totalAssets = Asset::count();
        $averageValue = $totalAssets > 0 ? ($totalValue / $totalAssets) : 0;

        return [
            [
                'Total Aset',
                $totalAssets,
            ],
            [
                'Total Nilai Aset',
                'Rp ' . number_format($totalValue, 0, ',', '.'),
            ],
            [
                'Total Biaya Pemeliharaan',
                'Rp ' . number_format(Maintenance::sum('cost'), 0, ',', '.'),
            ],
            [
                'Rata-rata Nilai Aset',
                'Rp ' . number_format($averageValue, 0, ',', '.'),
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
