<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\Maintenance;
use App\Models\AssetRequest;
use App\Models\User;
use Illuminate\Http\Request;

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
                $stats['maintenance_assets']
            ],
            'colors' => ['#10b981', '#f59e0b', '#ef4444']
        ];

        return view('dashboard.index', compact('stats', 'recentAssets', 'chartData'));
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