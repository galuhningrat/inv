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
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index()
    {
        $allowedTypes = collect(['assets', 'borrowing', 'maintenance', 'financial'])
            ->filter(fn($type) => Gate::allows('view-report', $type))
            ->values();

        return view('reports.index', compact('allowedTypes'));
    }

    public function generate(Request $request)
    {
        $type = $request->type;
        Gate::authorize('view-report', $type);

        $user = Auth::user();

        switch ($type) {
            case 'assets':
                $query = Asset::with('assetType');
                if (in_array($user->level, ['Kalab', 'Kaprodi'])) {
                    $query->where('unit_id', $user->unit_id); 
                }
                $data = $query->get();
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

        return view('reports.preview', compact('type', 'data', 'title', 'user'));
    }

    public function exportPdf(Request $request)
    {
        $type = $request->type;
        Gate::authorize('view-report', $type);  // Cek otorisasi

        $user = Auth::user();

        // Tentukan view PDF berdasarkan tipe
        $viewMap = [
            'assets' => 'reports.pdf.assets',
            'borrowing' => 'reports.pdf.borrowings',
            'maintenance' => 'reports.pdf.maintenances',
            'financial' => 'reports.pdf.financial',
        ];

        if (!isset($viewMap[$type])) {
            return back()->with('error', 'Jenis laporan tidak valid');
        }

        // Ambil data sesuai tipe
        switch ($type) {
            case 'assets':
                $query = Asset::with('assetType');
                // Scoping untuk Kalab/Kaprodi
                if (in_array($user->level, ['Kalab', 'Kaprodi'])) {
                    $query->where('unit_id', $user->unit_id);
                }
                $data = $query->get();
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

        $pdf = Pdf::loadView($viewMap[$type], compact('data', 'title', 'user'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("laporan-{$type}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $type = $request->type;
        Gate::authorize('view-report', $type);  // Cek otorisasi

        // Untuk Excel, kita tidak perlu scoping manual karena sudah di handle di Export class masing-masing
        // Tapi kita tetap harus memastikan hanya tipe yang diizinkan
        $allowedTypes = ['assets', 'borrowing', 'maintenance', 'financial'];
        if (!in_array($type, $allowedTypes)) {
            return back()->with('error', 'Jenis laporan tidak valid');
        }

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