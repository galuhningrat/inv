<?php

namespace App\Exports;

use App\Models\Maintenance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaintenancesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Maintenance::with('asset', 'recorder')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Maintenance',
            'Nama Aset',
            'Jenis Pemeliharaan',
            'Tanggal',
            'Biaya',
            'Deskripsi',
            'Teknisi',
            'Status',
            'Dicatat Oleh',
        ];
    }

    public function map($maintenance): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $maintenance->maintenance_id,
            $maintenance->asset->name ?? '-',
            $maintenance->type,
            $maintenance->maintenance_date->format('d/m/Y'),
            'Rp ' . number_format($maintenance->cost, 0, ',', '.'),
            $maintenance->description,
            $maintenance->technician,
            $maintenance->status,
            $maintenance->recorder->name ?? '-',
        ];
    }
}