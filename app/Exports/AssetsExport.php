<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $assets;

    public function __construct($assets = null)
    {
        $this->assets = $assets;
    }

    public function collection()
    {
        if ($this->assets) {
            return $this->assets;
        }
        return Asset::with('assetType')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Aset',
            'Nama Aset',
            'Jenis',
            'Merek',
            'Nomor Seri',
            'Harga',
            'Tanggal Pembelian',
            'Lokasi',
            'Kondisi',
            'Status',
        ];
    }

    public function map($asset): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $asset->asset_id,
            $asset->name,
            $asset->assetType->name ?? '-',
            $asset->brand,
            $asset->serial_number,
            'Rp ' . number_format($asset->price, 0, ',', '.'),
            $asset->purchase_date->format('d/m/Y'),
            $asset->location,
            $asset->condition,
            $asset->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}