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

    public function create(Request $request)
    {
        $assets = Asset::with('assetType')
            ->orderBy('name')
            ->get()
            ->groupBy('assetType.name');

        $maintenanceTypes = ['Preventif', 'Kuratif', 'Emergensi'];

        $preselectedAssetId = $request->query('asset_id');

        return view('maintenances.create', compact('assets', 'maintenanceTypes', 'preselectedAssetId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:Preventif,Kuratif,Emergensi',
            'maintenance_date' => 'required|date',
            'description' => 'required|string',
            'technician' => 'required|string|max:255',
        ]);

        $validated['recorded_by'] = Auth::id();
        $validated['status'] = 'Diterima';
        $validated['cost'] = 0; // biaya diisi belakangan saat tiket diproses/selesai

        $maintenance = Maintenance::create($validated);

        // Tandai aset sedang dalam penanganan
        Asset::where('id', $validated['asset_id'])->update(['status' => 'Maintenance']);

        return redirect()->route('maintenances.index')
            ->with('success', 'Tiket pemeliharaan berhasil dibuat!');
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'status' => 'required|in:Diterima,Dalam Proses,Menunggu Komponen,Selesai',
            'cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'technician' => 'nullable|string|max:255', // ✅ tambahan
        ]);

        $maintenance->update($validated);

        if ($validated['status'] === 'Selesai') {
            $maintenance->asset->update(['status' => 'Tersedia', 'condition' => 'Baik']);
        }

        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Status tiket berhasil diperbarui!');
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
