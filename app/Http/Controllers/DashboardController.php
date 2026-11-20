<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_assets' => Asset::count(),
            'borrowed_assets' => Asset::where('status', 'Dipinjam')->count(),
            'available_assets' => Asset::where('status', 'Tersedia')->count(),
            'maintenance_assets' => Asset::where('status', 'Maintenance')->count(),
        ];

        // 🔥 FIX: Eager load batch (bukan assetType yang sudah tidak ada di assets)
        $recentAssets = Asset::with('batch.assetType')
            ->latest()
            ->take(5)
            ->get();

        $chartData = [
            'labels' => ['Tersedia', 'Dipinjam', 'Maintenance'],
            'data' => [
                $stats['available_assets'],
                $stats['borrowed_assets'],
                $stats['maintenance_assets'],
            ],
            'colors' => ['#10b981', '#f59e0b', '#ef4444'],
        ];

        // Aset non-fisik yang mendekati kedaluwarsa (tetap aman)
        $expiringIntangibleAssets = \App\Models\IntangibleAsset::getExpiringSoonAssets();

        // 🔥 TAMBAHAN: Statistik pengajuan yang menunggu aksi (untuk admin/rektor)
        $pendingStats = [];
        $user = auth()->user();
        if (in_array($user->level, ['Admin', 'Sarpras', 'Rektor', 'PJ Pengadaan', 'Keuangan'])) {
            $pendingStats = [
                'pending_requests' => \App\Models\AssetRequest::where('status', 'Pending')->count(),
                'pending_verification' => \App\Models\AssetRequest::where('status', 'Diverifikasi')->count(),
                'pending_disbursement' => \App\Models\AssetRequest::where('status', 'Disetujui')->count(),
                'pending_maintenance' => \App\Models\Maintenance::where('status', 'Menunggu Verifikasi')->count(),
            ];
        }

        return view('dashboard.index', compact('stats', 'recentAssets', 'chartData', 'expiringIntangibleAssets', 'pendingStats'));
    }

    public function getStats()
    {
        return response()->json([
            'total_assets' => Asset::count(),
            'borrowed_assets' => Asset::where('status', 'Dipinjam')->count(),
            'available_assets' => Asset::where('status', 'Tersedia')->count(),
            'maintenance_assets' => Asset::where('status', 'Maintenance')->count(),
            'total_users' => User::count(),
            'total_batches' => AssetBatch::count(),
            'total_asset_value' => $this->calculateTotalAssetValue(),
        ]);
    }

    /**
     * Hitung nilai total semua aset = SUM(harga_batch × jumlah_unit_di_batch)
     * Menggunakan query efisien (bukan loop PHP) agar tetap cepat untuk data besar.
     */
    private function calculateTotalAssetValue(): int
    {
        return (int) DB::table('assets')
            ->join('asset_batches', 'assets.asset_batch_id', '=', 'asset_batches.id')
            ->whereNull('assets.deleted_at')
            ->sum('asset_batches.price');
    }
}
