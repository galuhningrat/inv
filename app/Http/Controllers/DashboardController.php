<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;

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

        $recentAssets = Asset::with('assetType')
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

        // 🔥 POIN 12: Ambil aset non-fisik yang mendekati kedaluwarsa
        $expiringIntangibleAssets = \App\Models\IntangibleAsset::getExpiringSoonAssets();

        return view('dashboard.index', compact('stats', 'recentAssets', 'chartData', 'expiringIntangibleAssets'));
    }

    public function getStats()
    {
        return response()->json([
            'total_assets' => Asset::count(),
            'borrowed_assets' => Asset::where('status', 'Dipinjam')->count(),
            'available_assets' => Asset::where('status', 'Tersedia')->count(),
            'maintenance_assets' => Asset::where('status', 'Maintenance')->count(),
            'total_users' => User::count(),
        ]);
    }
}
