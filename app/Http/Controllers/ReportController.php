<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetsExport;
use App\Exports\BorrowingsExport;
use App\Exports\MaintenancesExport;
use App\Exports\FinancialExport;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function generate(Request $request)
    {
        $type = $request->type;

        switch ($type) {
            case 'assets':
                $data = Asset::with('assetType')->get();
                $title = 'LAPORAN INVENTARIS ASET';
                break;
            case 'borrowing':
                $data = Borrowing::with('asset', 'approver')->get();
                $title = 'LAPORAN PEMINJAMAN ASET';
                break;
            case 'maintenance':
                $data = Maintenance::with('asset', 'recorder')->get();
                $title = 'LAPORAN PEMELIHARAAN ASET';
                break;
            case 'financial':
                $data = [
                    'total_assets' => Asset::count(),
                    'total_value' => Asset::sum('price'),
                    'maintenance_cost' => Maintenance::sum('cost'),
                    'average_value' => Asset::avg('price'),
                    'maintenance_records' => Maintenance::count(),
                ];
                $title = 'LAPORAN KEUANGAN ASET';
                break;
            default:
                return back()->with('error', 'Jenis laporan tidak valid');
        }

        $user = Auth::user();

        return view('reports.preview', compact('type', 'data', 'title', 'user'));
    }

    public function exportPdf(Request $request)
    {
        $type = $request->type;

        switch ($type) {
            case 'assets':
                $data = Asset::with('assetType')->get();
                $title = 'LAPORAN INVENTARIS ASET';
                $view = 'reports.pdf.assets';
                break;
            case 'borrowing':
                $data = Borrowing::with('asset', 'approver')->get();
                $title = 'LAPORAN PEMINJAMAN ASET';
                $view = 'reports.pdf.borrowings';
                break;
            case 'maintenance':
                $data = Maintenance::with('asset', 'recorder')->get();
                $title = 'LAPORAN PEMELIHARAAN ASET';
                $view = 'reports.pdf.maintenances';
                break;
            case 'financial':
                $data = [
                    'total_assets' => Asset::count(),
                    'total_value' => Asset::sum('price'),
                    'maintenance_cost' => Maintenance::sum('cost'),
                    'average_value' => Asset::avg('price'),
                    'maintenance_records' => Maintenance::count(),
                ];
                $title = 'LAPORAN KEUANGAN ASET';
                $view = 'reports.pdf.financial';
                break;
            default:
                return back()->with('error', 'Jenis laporan tidak valid');
        }

        $user = Auth::user();

        $pdf = Pdf::loadView($view, compact('data', 'title', 'user'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("laporan-{$type}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $type = $request->type;

        switch ($type) {
            case 'assets':
                return Excel::download(new AssetsExport, 'laporan-aset.xlsx');
            case 'borrowing':
                return Excel::download(new BorrowingsExport, 'laporan-peminjaman.xlsx');
            case 'maintenance':
                return Excel::download(new MaintenancesExport, 'laporan-pemeliharaan.xlsx');
            case 'financial':
                return Excel::download(new FinancialExport, 'laporan-keuangan.xlsx');
            default:
                return back()->with('error', 'Jenis laporan tidak valid');
        }
    }
}