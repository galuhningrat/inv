<?php

namespace App\Exports;

use App\Models\Borrowing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BorrowingsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Borrowing::with('asset', 'approver')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Peminjaman',
            'Nama Aset',
            'Peminjam',
            'Jabatan',
            'Tanggal Pinjam',
            'Tanggal Kembali',
            'Tanggal Aktual Kembali',
            'Tujuan',
            'Status',
            'Disetujui Oleh',
        ];
    }

    public function map($borrowing): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $borrowing->borrowing_id,
            $borrowing->asset->name ?? '-',
            $borrowing->borrower_name,
            $borrowing->borrower_role,
            $borrowing->borrow_date->format('d/m/Y'),
            $borrowing->return_date->format('d/m/Y'),
            $borrowing->actual_return_date ? $borrowing->actual_return_date->format('d/m/Y') : '-',
            $borrowing->purpose,
            $borrowing->status,
            $borrowing->approver->name ?? '-',
        ];
    }
}