<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('assetType');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_id', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%")
                    ->orWhere('brand', 'ILIKE', "%{$search}%")
                    ->orWhere('location', 'ILIKE', "%{$search}%")
                    ->orWhere('serial_number', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->whereHas('assetType', function ($q) use ($request) {
                $q->where('code', $request->type);
            });
        }

        $assetTypes = AssetType::all();

        // Kalau filter status aktif, tampilkan FLAT per-unit (karena status memang atribut per-unit,
        // tidak masuk akal dikelompokkan saat sedang difilter per status)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
            $assets = $query->orderBy('created_at', 'desc')->get();

            return view('assets-inv.index', [
                'assets' => $assets,
                'assetTypes' => $assetTypes,
                'isGrouped' => false,
            ]);
        }

        // Default: kelompokkan per (nama + jenis + merek)
        $allAssets = $query->orderBy('created_at', 'desc')->get();

        $grouped = $allAssets
            ->groupBy(fn($a) => $a->name . '|' . $a->asset_type_id . '|' . $a->brand)
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'name' => $first->name,
                    'brand' => $first->brand,
                    'asset_type_id' => $first->asset_type_id,
                    'assetType' => $first->assetType,
                    'location' => $first->location,
                    'image_url' => $first->image_url,
                    'price' => $first->price,
                    'total_quantity' => $group->count(),
                    'tersedia_count' => $group->where('status', 'Tersedia')->count(),
                    'dipinjam_count' => $group->where('status', 'Dipinjam')->count(),
                    'maintenance_count' => $group->where('status', 'Maintenance')->count(),
                ];
            })->values();

        return view('assets-inv.index', [
            'grouped' => $grouped,
            'assetTypes' => $assetTypes,
            'isGrouped' => true,
        ]);
    }

    public function groupDetail(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string',
        ]);

        $units = Asset::with('assetType')
            ->where('name', $validated['name'])
            ->where('asset_type_id', $validated['type_id'])
            ->where('brand', $validated['brand'])
            ->orderBy('serial_number')
            ->get();

        abort_if($units->isEmpty(), 404);

        return view('assets-inv.group-detail', [
            'units' => $units,
            'groupName' => $validated['name'],
            'groupBrand' => $validated['brand'],
        ]);
    }

    public function create()
    {
        $assetTypes = AssetType::all();
        $locations = ['Ruang IT', 'Laboratorium', 'Perpustakaan', 'Aula', 'Ruang Dosen'];

        return view('assets-inv.create', compact('assetTypes', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:assets,serial_number',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'location' => 'required|string|max:255',
            'condition' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $assetType = AssetType::find($request->asset_type_id);
            $year = date('Y');
            $month = date('m');

            // SOLUSI RADIKAL: Ambil MAX ID langsung dari database
            $maxAssetId = DB::select("
                SELECT MAX(
                    CAST(
                        SUBSTRING(asset_id FROM '[0-9]+$') AS INTEGER
                    )
                ) as max_num
                FROM assets
                WHERE asset_id LIKE ?
                AND deleted_at IS NULL
            ", ["{$year}/{$month}/{$assetType->code}-%"]);

            $counter = ($maxAssetId[0]->max_num ?? 0) + 1;
            $validated['asset_id'] = sprintf('%s/%s/%s-%04d', $year, $month, $assetType->code, $counter);

            // Generate QR Code UNIK
            $qrCode = $this->generateUniqueQrCode($request->asset_type_id);
            $validated['qr_code'] = $qrCode;
            $validated['status'] = 'Tersedia';

            // Handle image
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('assets', 'public');
                $validated['image'] = $imagePath;
            }

            $asset = Asset::create($validated);

            // SOLUSI RADIKAL: Generate QR Code ID langsung dari MAX
            $maxQrCodeId = DB::select("
                SELECT MAX(
                    CAST(
                        REPLACE(qr_code_id, 'QCD-', '') AS INTEGER
                    )
                ) as max_num
                FROM qr_codes
            ");

            $qrCounter = ($maxQrCodeId[0]->max_num ?? 0) + 1;
            // dd($maxQrCodeId[0]->max_num);
            $qrCodeId = sprintf('QCD-%03d', $qrCounter);

            QrCode::create([
                'qr_code_id' => $qrCodeId,
                'asset_id' => $asset->id,
                'code_content' => $qrCode,
                'status' => 'Aktif',
            ]);

            DB::commit();

            return redirect()->route('assets-inv.index')
                ->with('success', 'Aset berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollback();
            // \Log::error('Asset Store Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan aset: ' . $e->getMessage());
        }
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'assetType',
            'qrCodes',
            'borrowings' => function ($query) {
                $query->latest()->limit(10);
            },
            'maintenances' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        return view('assets-inv.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $assetTypes = AssetType::all();
        $locations = ['Ruang IT', 'Laboratorium', 'Perpustakaan', 'Aula', 'Ruang Dosen'];

        return view('assets-inv.edit', compact('asset', 'assetTypes', 'locations'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:assets,serial_number,' . $asset->id,
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'location' => 'required|string|max:255',
            'condition' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }
            $imagePath = $request->file('image')->store('assets', 'public');
            $validated['image'] = $imagePath;
        }

        $asset->update($validated);

        return redirect($this->resolveGroupRedirect($request))
            ->with('success', 'Aset berhasil diupdate!');
    }

    public function destroy(Asset $asset)
    {
        try {
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }

            $asset->qrCodes()->delete();
            $asset->delete();

            return redirect()->route('assets-inv.index')
                ->with('success', 'Aset berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('assets-inv.index')
                ->with('error', 'Gagal menghapus aset: ' . $e->getMessage());
        }
    }

    private function resolveGroupRedirect(Request $request): string
    {
        if ($request->filled('group_name') && is_numeric($request->input('group_type_id')) && $request->filled('group_brand')) {
            return route('assets-inv.group-detail', [
                'name' => $request->input('group_name'),
                'type_id' => $request->input('group_type_id'),
                'brand' => $request->input('group_brand'),
            ]);
        }

        return route('assets-inv.index');
    }
    private function resolveGroupRedirectAfterDelete(Request $request, array $group): string
    {
        if ($request->filled('group_name')) {
            $stillExists = Asset::where('name', $group['name'])
                ->where('asset_type_id', $group['type_id'])
                ->where('brand', $group['brand'])
                ->exists();

            if ($stillExists) {
                return route('assets-inv.group-detail', [
                    'name' => $group['name'],
                    'type_id' => $group['type_id'],
                    'brand' => $group['brand'],
                ]);
            }
        }

        return route('assets-inv.index'); // grup sudah kosong (unit terakhir dihapus) atau memang bukan dari grup
    }

    public function generateQrCode(Request $request)
    {
        $assetTypeId = $request->asset_type_id;
        $qrCode = $this->generateUniqueQrCode($assetTypeId);

        return response()->json(['qr_code' => $qrCode]);
    }

    private function generateUniqueQrCode($assetTypeId)
    {
        $type = AssetType::find($assetTypeId);
        $prefix = $type ? $type->code : 'AST';

        $attempts = 0;
        do {
            $timestamp = base_convert(time() + $attempts, 10, 36);
            $random = strtoupper(Str::random(6));
            $qrCode = "{$prefix}-{$timestamp}-{$random}";

            $exists = Asset::where('qr_code', $qrCode)->exists();
            $attempts++;
        } while ($exists && $attempts < 10);

        return $qrCode;
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            $deleted = 0;

            DB::beginTransaction();

            foreach ($ids as $id) {
                $asset = Asset::find($id);
                if ($asset) {
                    if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                        Storage::disk('public')->delete($asset->image);
                    }
                    $asset->qrCodes()->delete();
                    $asset->delete();
                    $deleted++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deleted} aset berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus aset: ' . $e->getMessage()
            ], 500);
        }
    }

    // TAMBAHAN: Show detail di modal
    public function showModal(Asset $asset)
    {
        $asset->load(['assetType', 'qrCodes', 'borrowings', 'maintenances']);

        return response()->json([
            'html' => view('assets-inv.modal-detail', compact('asset'))->render()
        ]);
    }
}