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

        // Eager load batch dan assetType untuk menghindari N+1 query
        return Asset::with(['batch.assetType'])->get();
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

        // Ambil data dengan aman (mendukung data lama yang mungkin tidak punya batch)
        $name = $asset->name ?? $asset->batch->name ?? '-';
        $brand = $asset->brand ?? $asset->batch->brand ?? '-';
        $price = $asset->price ?? $asset->batch->price ?? 0;
        $purchaseDate = $asset->purchase_date ?? $asset->batch->purchase_date ?? null;
        $assetTypeName = $asset->assetType->name ?? $asset->batch->assetType->name ?? '-';

        return [
            $no,
            $asset->asset_id ?? '-',
            $name,
            $assetTypeName,
            $brand,
            $asset->serial_number ?? '-',
            'Rp '.number_format($price, 0, ',', '.'),
            // 🔥 Menggunakan Nullsafe untuk mencegah error jika tanggal null 🔥
            $purchaseDate ? $purchaseDate->format('d/m/Y') : '-',
            $asset->location ?? '-',
            $asset->condition ?? '-',
            $asset->status ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
