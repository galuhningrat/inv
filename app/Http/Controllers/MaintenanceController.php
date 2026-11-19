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
        $query = Maintenance::with('asset.batch.assetType', 'recorder', 'verifier');

        $user = auth()->user();
        if ($user->level === 'Kalab') {
            $query->whereHas('asset', fn($q) => $q->where('unit_id', $user->unit_id));
        } elseif (in_array($user->level, ['Aslab', 'Karyawan', 'Mahasiswa'])) {
            $query->where('recorded_by', $user->id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $maintenances = $query->latest()->paginate(10);

        // Statistik ringkas untuk dashboard pemeliharaan
        $stats = [
            'menunggu_verifikasi' => Maintenance::where('status', 'Menunggu Verifikasi')->count(),
            'dalam_proses' => Maintenance::whereIn('status', ['Diterima', 'Dalam Proses', 'Menunggu Komponen'])->count(),
            'selesai' => Maintenance::where('status', 'Selesai')->count(),
        ];

        return view('maintenances.index', compact('maintenances', 'stats'));
    }

    public function create(Request $request)
    {
        // 🔥 PERBAIKAN: Karena asset sekarang tidak punya kolom 'name' & 'asset_type_id' langsung
        // Eager-load relasi batch + assetType-nya, lalu kelompokkan berdasarkan nama jenis aset
        $assets = Asset::with(['batch.assetType'])
            ->whereHas('batch')
            ->get()
            ->groupBy(fn($asset) => $asset->batch->assetType->name ?? 'Tanpa Kategori');

        $maintenanceTypes = ['Preventif', 'Kuratif', 'Emergensi'];

        $preselectedAssetId = $request->query('asset_id');

        return view('maintenances.create', compact('assets', 'maintenanceTypes', 'preselectedAssetId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Maintenance::class);

        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:Preventif,Kuratif,Emergensi',
            'maintenance_date' => 'required|date',
            'description' => 'required|string',
            'technician' => 'required|string|max:255',
        ]);

        $validated['recorded_by'] = Auth::id();
        $validated['status'] = 'Menunggu Verifikasi'; // Tiket baru selalu menunggu verifikasi
        $validated['cost'] = 0;

        $maintenance = Maintenance::create($validated);

        // 🔥 Aset TIDAK langsung diubah ke 'Maintenance' — tunggu diverifikasi Kalab/PJ dulu
        // Asset::where('id', $validated['asset_id'])->update(['status' => 'Maintenance']);

        return redirect()->route('maintenances.index')
            ->with('success', 'Tiket pemeliharaan berhasil dibuat. Menunggu verifikasi Kalab/PJ.');
    }

    /**
     * Verifikasi tiket oleh Kalab/PJ Sarpras/Admin.
     * Hanya tiket dengan status 'Menunggu Verifikasi' yang bisa diverifikasi.
     */
    public function verify(Request $request, Maintenance $maintenance)
    {
        $this->authorize('verify', $maintenance);

        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $maintenance->update([
            'status' => 'Diterima',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ]);

        // Baru setelah diverifikasi, aset ditandai sedang dalam pemeliharaan
        $maintenance->asset->update(['status' => 'Maintenance']);

        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Laporan diverifikasi dan diteruskan ke Tim Pemeliharaan.');
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $this->authorize('update', $maintenance);

        $validated = $request->validate([
            'status' => 'required|in:Diterima,Dalam Proses,Menunggu Komponen,Selesai',
            'cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'technician' => 'nullable|string|max:255',
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
        $this->authorize('view', $maintenance);

        $maintenance->load('asset.batch.assetType', 'recorder', 'verifier');
        return view('maintenances.show', compact('maintenance'));
    }

    public function destroy(Maintenance $maintenance)
    {
        $this->authorize('delete', $maintenance);

        $maintenance->delete();
        return redirect()->route('maintenances.index')->with('success', 'Catatan pemeliharaan berhasil dihapus!');
    }

    public function showAssetDetail($id)
    {
        // 🔥 PERBAIKAN: Eager-load batch relasi
        $asset = Asset::with('batch.assetType', 'qrCodes')->findOrFail($id);
        return view('maintenances.asset-detail', compact('asset'));
    }
}
