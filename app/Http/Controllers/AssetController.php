<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Unit;
use App\Models\Location;
use App\Models\IntangibleAsset;
use App\Models\AssetRequest;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Asset::class);

        $view = $request->input('view', 'semua'); // semua | fisik | non-fisik
        $assetTypes = AssetType::all();
        $units = Unit::whereNotNull('category')->orderBy('type')->orderBy('name')->get();

        if ($view === 'non-fisik') {
            return $this->indexNonFisik($request, $assetTypes, $units, $view);
        }

        if ($view === 'semua') {
            return $this->indexSemua($request, $assetTypes, $units, $view);
        }

        // view === 'fisik'
        $query = Asset::with('assetType', 'unit');
        $user = auth()->user();

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
            $query->whereHas('assetType', fn($q) => $q->where('code', $request->type));
        }
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $allAssets = $query->orderBy('created_at', 'desc')->get();

        // Jika ada filter status, tampilkan list (tidak dikelompokkan)
        if ($request->filled('status')) {
            $assets = $allAssets;
            return view('assets-inv.index', [
                'assets' => $assets,
                'assetTypes' => $assetTypes,
                'units' => $units,
                'isGrouped' => false,
                'view' => $view
            ]);
        }

        // Group by name, type_id, brand
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
                    'diganti_count' => $group->where('status', 'Diganti')->count(),
                ];
            })->values();

        return view('assets-inv.index', [
            'grouped' => $grouped,
            'assetTypes' => $assetTypes,
            'units' => $units,
            'isGrouped' => true,
            'view' => $view
        ]);
    }

    private function indexNonFisik(Request $request, $assetTypes, $units, $view)
    {
        $search = $request->input('search');
        $unitId = $request->input('unit_id');
        $status = $request->input('status');

        $query = IntangibleAsset::with('unit');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('vendor', 'ILIKE', "%{$search}%")
                    ->orWhere('asset_code', 'ILIKE', "%{$search}%");
            });
        }
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        if ($status) {
            if ($status === 'Tersedia') {
                $query->where('status', 'Aktif');
            } elseif ($status === 'Kadaluarsa') {
                $query->where('status', 'Kadaluarsa');
            }
            // status lain tidak berlaku untuk non-fisik
        }

        $groupedNonFisik = $query->orderBy('created_at', 'desc')->get()
            ->groupBy(fn($i) => $i->name . '|' . $i->category . '|' . $i->vendor)
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'name' => $first->name,
                    'category' => $first->category,
                    'vendor' => $first->vendor,
                    'unit' => $first->unit,
                    'price' => $first->price,
                    'license_type' => $first->license_type,
                    'total_quantity' => $group->count(),
                    'kadaluarsa_count' => $group->where('status', 'Kadaluarsa')->count(),
                    'sample_id' => $first->id,
                ];
            })->values();

        return view('assets-inv.index', [
            'groupedNonFisik' => $groupedNonFisik,
            'assetTypes' => $assetTypes,
            'units' => $units,
            'view' => $view,
            'isGrouped' => false,
        ]);
    }

    private function indexSemua(Request $request, $assetTypes, $units, $view)
    {
        $search = $request->input('search');
        $unitId = $request->input('unit_id');
        $status = $request->input('status');

        // ---- QUERY ASET FISIK ----
        $physicalQuery = Asset::with('assetType', 'unit');
        if ($search) {
            $physicalQuery->where(function ($q) use ($search) {
                $q->where('asset_id', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%")
                    ->orWhere('brand', 'ILIKE', "%{$search}%")
                    ->orWhere('location', 'ILIKE', "%{$search}%")
                    ->orWhere('serial_number', 'ILIKE', "%{$search}%");
            });
        }
        if ($unitId) {
            $physicalQuery->where('unit_id', $unitId);
        }
        if ($status && in_array($status, ['Tersedia', 'Dipinjam', 'Maintenance', 'Diganti'])) {
            $physicalQuery->where('status', $status);
        }

        $physicalGroups = $physicalQuery->get()
            ->groupBy(fn($a) => $a->name . '|' . $a->asset_type_id . '|' . $a->brand)
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'type' => 'Fisik',
                    'name' => $first->name,
                    'category_label' => $first->assetType->name ?? '-',
                    'unit_label' => $first->unit->name ?? '-',
                    'quantity' => $group->count(),
                    'status_label' => $group->where('status', 'Tersedia')->count() . ' Tersedia',
                    'detail_route' => route('assets-inv.group-detail', [
                        'name' => $first->name,
                        'type_id' => $first->asset_type_id,
                        'brand' => $first->brand
                    ]),
                ];
            });

        // ---- QUERY ASET NON-FISIK ----
        $intangibleQuery = IntangibleAsset::with('unit');
        if ($search) {
            $intangibleQuery->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('vendor', 'ILIKE', "%{$search}%")
                    ->orWhere('asset_code', 'ILIKE', "%{$search}%");
            });
        }
        if ($unitId) {
            $intangibleQuery->where('unit_id', $unitId);
        }
        // Filter status untuk non-fisik: Tersedia -> Aktif, Kadaluarsa -> Kadaluarsa
        if ($status) {
            if ($status === 'Tersedia') {
                $intangibleQuery->where('status', 'Aktif');
            } elseif ($status === 'Kadaluarsa') {
                $intangibleQuery->where('status', 'Kadaluarsa');
            }
            // status lain (Dipinjam, Maintenance, Diganti) tidak mempengaruhi non-fisik
        }

        $intangibleGroups = $intangibleQuery->get()
            ->groupBy(fn($i) => $i->name . '|' . $i->category . '|' . $i->vendor)
            ->map(function ($group) {
                $first = $group->first();
                $kadaluarsa = $group->where('status', 'Kadaluarsa')->count();
                return (object) [
                    'type' => 'Non-Fisik',
                    'name' => $first->name,
                    'category_label' => $first->category,
                    'unit_label' => $first->unit->name ?? '-',
                    'quantity' => $group->count(),
                    'status_label' => $kadaluarsa > 0 ? "{$kadaluarsa} Kadaluarsa" : 'Aktif',
                    'detail_route' => route('intangible-assets.show', $first->sample_id ?? $first->id),
                ];
            });

        $combined = $physicalGroups->concat($intangibleGroups)->sortBy('name')->values();

        return view('assets-inv.index', [
            'combined' => $combined,
            'assetTypes' => $assetTypes,
            'units' => $units,
            'view' => $view,
            'isGrouped' => false,
        ]);
    }

    public function groupDetail(Request $request)
    {
        $this->authorize('viewAny', Asset::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string',
        ]);

        $query = Asset::with('assetType', 'location_ref', 'unit')
            ->where('name', $validated['name'])
            ->where('asset_type_id', $validated['type_id'])
            ->where('brand', $validated['brand']);

        $units = $query->orderBy('serial_number')->get();

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
        $units = Unit::with('locations')->whereNotNull('category')->orderBy('category')->orderBy('name')->get();
        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level');

        $unitsForJs = $units->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'category' => $u->category,
                'locations' => $u->locations->map(function ($l) {
                    return ['id' => $l->id, 'name' => $l->name];
                })->values(),
            ];
        })->values();

        return view('assets-inv.create', compact('assetTypes', 'units', 'usersByLevel', 'unitsForJs'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Asset::class);

        $unit = Unit::find($request->input('unit_id'));
        $hasLocations = $unit && $unit->locations()->exists();

        $rules = [
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'unit_id' => 'required|exists:units,id',
            'location_id' => $hasLocations ? 'required|exists:locations,id' : 'nullable',
            'location_detail' => $hasLocations ? 'nullable|string|max:255' : 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:100',
            'serial_numbers' => 'required|array|size:' . $request->input('quantity', 1),
            'serial_numbers.*' => 'required|string|distinct|unique:assets,serial_number',
            'conditions' => 'required|array',
            'conditions.*' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'penanggung_jawab_id' => 'nullable|exists:users,id',
        ];

        $validated = $request->validate($rules);

        $locationName = !empty($validated['location_id']) ? Location::find($validated['location_id'])->name : null;
        $locationString = trim(collect([$locationName, $validated['location_detail'] ?? null])->filter()->implode(' - '));
        $assetType = AssetType::find($validated['asset_type_id']);

        DB::beginTransaction();
        try {
            $created = 0;

            foreach ($validated['serial_numbers'] as $index => $serialNumber) {
                $year = date('Y');
                $month = date('m');
                $maxAssetId = DB::select("SELECT MAX(CAST(SUBSTRING(asset_id FROM '[0-9]+$') AS INTEGER)) as max_num FROM assets WHERE asset_id LIKE ? AND deleted_at IS NULL", ["{$year}/{$month}/{$assetType->code}-%"]);
                $counter = ($maxAssetId[0]->max_num ?? 0) + 1;

                $imagePath = $request->hasFile("images.$index")
                    ? $request->file("images.$index")->store('assets', 'public')
                    : null;

                $asset = Asset::create([
                    'asset_id' => sprintf('%s/%s/%s-%04d', $year, $month, $assetType->code, $counter),
                    'name' => $validated['name'],
                    'asset_type_id' => $validated['asset_type_id'],
                    'brand' => $validated['brand'],
                    'serial_number' => $serialNumber,
                    'price' => $validated['price'],
                    'purchase_date' => $validated['purchase_date'],
                    'location' => $locationString,
                    'location_id' => $validated['location_id'] ?? null,
                    'location_detail' => $validated['location_detail'] ?? null,
                    'unit_id' => $validated['unit_id'],
                    'condition' => $validated['conditions'][$index],
                    'status' => 'Tersedia',
                    'image' => $imagePath,
                    'qr_code' => QrCode::generateCodeContent($assetType->code),
                    'penanggung_jawab_id' => $validated['penanggung_jawab_id'] ?? null,
                ]);

                QrCode::create(['asset_id' => $asset->id, 'code_content' => $asset->qr_code, 'status' => 'Aktif']);
                $created++;
            }

            DB::commit();
            return redirect()->route('assets-inv.index')->with('success', "{$created} unit aset berhasil ditambahkan!");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan aset: ' . $e->getMessage());
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
            },
            'unit',
            'location_ref',
            'replacement',
            'replaces',
        ]);

        return view('assets-inv.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $assetTypes = AssetType::all();
        $units = Unit::with('locations')->whereNotNull('category')->orderBy('category')->orderBy('name')->get();
        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level');

        $unitsForJs = $units->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'category' => $u->category,
                'locations' => $u->locations->map(function ($l) {
                    return ['id' => $l->id, 'name' => $l->name];
                })->values(),
            ];
        })->values();

        return view('assets-inv.edit', compact('asset', 'assetTypes', 'units', 'usersByLevel', 'unitsForJs'));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);

        // Validasi dasar
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'unit_id' => 'required|exists:units,id',
            'location_id' => 'nullable|exists:locations,id',
            'location_detail' => 'nullable|string|max:255',
            'serial_number' => 'required|string|unique:assets,serial_number,' . $asset->id,
            'condition' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'penanggung_jawab_id' => 'nullable|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Tentukan apakah unit memiliki lokasi
        $unit = Unit::find($validated['unit_id']);
        $hasLocations = $unit && $unit->locations()->exists();

        // Jika unit punya lokasi, location_id wajib
        if ($hasLocations && empty($validated['location_id'])) {
            return back()->withErrors(['location_id' => 'Lokasi Spesifik wajib diisi untuk unit ini.'])->withInput();
        }

        // Jika unit tidak punya lokasi, location_detail wajib
        if (!$hasLocations && empty($validated['location_detail'])) {
            return back()->withErrors(['location_detail' => 'Detail Penempatan wajib diisi karena unit ini belum memiliki daftar lokasi spesifik.'])->withInput();
        }

        // Generate location string
        $locationName = !empty($validated['location_id']) ? Location::find($validated['location_id'])->name : null;
        $locationString = trim(collect([$locationName, $validated['location_detail'] ?? null])->filter()->implode(' - '));

        // Jika location string kosong, gunakan location lama
        if (empty($locationString)) {
            $locationString = $asset->location;
        }

        // Update data
        $asset->name = $validated['name'];
        $asset->asset_type_id = $validated['asset_type_id'];
        $asset->brand = $validated['brand'];
        $asset->price = $validated['price'];
        $asset->purchase_date = $validated['purchase_date'];
        $asset->unit_id = $validated['unit_id'];
        $asset->location_id = $validated['location_id'] ?? null;
        $asset->location_detail = $validated['location_detail'] ?? null;
        $asset->location = $locationString;
        $asset->serial_number = $validated['serial_number'];
        $asset->condition = $validated['condition'];
        $asset->penanggung_jawab_id = $validated['penanggung_jawab_id'] ?? null;

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }
            $imagePath = $request->file('image')->store('assets', 'public');
            $asset->image = $imagePath;
        }

        $asset->save();

        return redirect($this->resolveGroupRedirect($request))
            ->with('success', 'Aset berhasil diupdate!');
    }

    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset);

        $groupSnapshot = ['name' => $asset->name, 'type_id' => $asset->asset_type_id, 'brand' => $asset->brand];

        try {
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }
            $asset->qrCodes()->delete();
            $asset->delete();

            return redirect($this->resolveGroupRedirectAfterDelete(request(), $groupSnapshot))
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

        return route('assets-inv.index');
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
        $this->authorize('delete', Asset::class);

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

    public function showModal(Asset $asset)
    {
        $asset->load(['assetType', 'qrCodes', 'borrowings', 'maintenances']);

        return response()->json([
            'html' => view('assets-inv.modal-detail', compact('asset'))->render()
        ]);
    }
}