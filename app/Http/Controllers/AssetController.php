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

        // Search
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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->whereHas('assetType', function ($q) use ($request) {
                $q->where('code', $request->type);
            });
        }

        $assets = $query->orderBy('created_at', 'desc')->get();
        $assetTypes = AssetType::all();

        return view('assets-inv.index', compact('assets', 'assetTypes'));
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

        return redirect()->route('assets-inv.index')
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