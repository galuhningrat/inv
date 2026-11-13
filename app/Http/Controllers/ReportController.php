<?php

namespace App\Http\Controllers;

use App\Exports\AssetsExport;
use App\Exports\BorrowingsExport;
use App\Exports\FinancialExport;
use App\Exports\MaintenancesExport;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $allowedTypes = collect(['assets', 'borrowing', 'maintenance', 'financial'])
            ->filter(fn ($type) => Gate::allows('view-report', $type))
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
                $query = Asset::with(['batch.assetType']);
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

        \App\Models\ActivityLog::record('EXPORT', "Mengekspor Laporan {$type} ke format PDF");

        Gate::authorize('view-report', $type);

        $user = Auth::user();
        $ids = $request->filled('ids') ? explode(',', $request->ids) : [];
        $types = $request->filled('types') ? explode(',', $request->types) : [];

        $viewMap = [
            'assets' => 'reports.pdf.assets',
            'borrowing' => 'reports.pdf.borrowings',
            'maintenance' => 'reports.pdf.maintenances',
            'financial' => 'reports.pdf.financial',
        ];

        if (! isset($viewMap[$type])) {
            return back()->with('error', 'Jenis laporan tidak valid');
        }

        switch ($type) {
            case 'assets':
                $query = Asset::with(['batch.assetType', 'batch']); // Eager load batch

                // Scoping untuk Kalab/Kaprodi
                if (in_array($user->level, ['Kalab', 'Kaprodi'])) {
                    $query->where('unit_id', $user->unit_id);
                }

                // 🔥 Filter berdasarkan ID yang dipilih
                if (! empty($ids)) {
                    $batchIds = [];
                    $assetIds = [];
                    foreach ($ids as $index => $id) {
                        $typeSelected = $types[$index] ?? 'asset';
                        if ($typeSelected === 'batch') {
                            $batchIds[] = $id;
                        } else {
                            $assetIds[] = $id;
                        }
                    }

                    $query->where(function ($q) use ($batchIds, $assetIds) {
                        if (! empty($batchIds)) {
                            $q->whereIn('asset_batch_id', $batchIds);
                        }
                        if (! empty($assetIds)) {
                            $q->orWhereIn('id', $assetIds);
                        }
                    });
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

        \App\Models\ActivityLog::record('EXPORT', "Mengekspor Laporan {$type} ke format Excel");

        Gate::authorize('view-report', $type);

        $ids = $request->filled('ids') ? explode(',', $request->ids) : [];
        $types = $request->filled('types') ? explode(',', $request->types) : [];

        $allowedTypes = ['assets', 'borrowing', 'maintenance', 'financial'];
        if (! in_array($type, $allowedTypes)) {
            return back()->with('error', 'Jenis laporan tidak valid');
        }

        switch ($type) {
            case 'assets':
                // 🔥 Siapkan query data
                $query = Asset::with(['batch.assetType']);

                // Filter berdasarkan ID yang dipilih
                if (! empty($ids)) {
                    $batchIds = [];
                    $assetIds = [];
                    foreach ($ids as $index => $id) {
                        $typeSelected = $types[$index] ?? 'asset';
                        if ($typeSelected === 'batch') {
                            $batchIds[] = $id;
                        } else {
                            $assetIds[] = $id;
                        }
                    }

                    $query->where(function ($q) use ($batchIds, $assetIds) {
                        if (! empty($batchIds)) {
                            $q->whereIn('asset_batch_id', $batchIds);
                        }
                        if (! empty($assetIds)) {
                            $q->orWhereIn('id', $assetIds);
                        }
                    });
                }

                // Kirim hasil query ke class Export
                return Excel::download(new AssetsExport($query->get()), 'laporan-aset.xlsx');

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
