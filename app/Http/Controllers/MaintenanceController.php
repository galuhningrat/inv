<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::with('asset', 'recorder');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $maintenances = $query->latest()->paginate(10);

        return view('maintenances.index', compact('maintenances'));
    }

    public function create()
    {
        $assets = Asset::with('assetType')
            ->orderBy('name')
            ->get()
            ->groupBy('assetType.name');

        $maintenanceTypes = ['Preventif', 'Corrective', 'Predictive', 'Emergency'];

        return view('maintenances.create', compact('assets', 'maintenanceTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:Preventif,Corrective,Predictive,Emergency',
            'maintenance_date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'description' => 'required|string',
            'technician' => 'required|string|max:255',
        ]);

        $validated['recorded_by'] = Auth::id();
        $validated['status'] = 'Selesai';

        $maintenance = Maintenance::create($validated);

        // Update asset condition if corrective maintenance
        if ($validated['type'] === 'Corrective') {
            Asset::where('id', $validated['asset_id'])
                ->update([
                    'condition' => 'Baik',
                    'status' => 'Tersedia'
                ]);
        }

        return redirect()->route('maintenances.index')
            ->with('success', 'Catatan pemeliharaan berhasil disimpan!');
    }

    public function show(Maintenance $maintenance)
    {
        $maintenance->load('asset', 'recorder');
        return view('maintenances.show', compact('maintenance'));
    }

    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();

        return redirect()->route('maintenances.index')
            ->with('success', 'Catatan pemeliharaan berhasil dihapus!');
    }

    public function showAssetDetail($id)
    {
        $asset = Asset::findOrFail($id);
        return view('maintenances.asset-detail', compact('asset'));
    }
}