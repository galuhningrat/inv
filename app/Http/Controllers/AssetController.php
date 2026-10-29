<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetBatch;
use App\Models\AssetType;
use App\Models\IntangibleAsset;
use App\Models\Location;
use App\Models\QrCode;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Asset::class);

        $view = $request->input('view', 'semua');
        $assetTypes = AssetType::getOrdered();
        $units = Unit::whereNotNull('category')->orderBy('type')->orderBy('name')->get();

        if ($view === 'non-fisik') {
            return $this->indexNonFisik($request, $assetTypes, $units, $view);
        }

        if ($view === 'semua') {
            return $this->indexSemua($request, $assetTypes, $units, $view);
        }

        $query = AssetBatch::with(['assetType', 'assets.unit'])
            ->withCount([
                'assets',
                'assets as tersedia_count' => fn ($q) => $q->where('status', 'Tersedia'),
                'assets as dipinjam_count' => fn ($q) => $q->where('status', 'Dipinjam'),
                'assets as maintenance_count' => fn ($q) => $q->where('status', 'Maintenance'),
                'assets as diganti_count' => fn ($q) => $q->where('status', 'Diganti'),
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('brand', 'ILIKE', "%{$search}%")
                    ->orWhere('model', 'ILIKE', "%{$search}%");
            });
        }
        if ($request->filled('type')) {
            $query->whereHas('assetType', fn ($q) => $q->where('code', $request->type));
        }
        if ($request->filled('unit_id')) {
            $query->whereHas('assets', fn ($q) => $q->where('unit_id', $request->unit_id));
        }
        if ($request->filled('status')) {
            $query->whereHas('assets', fn ($q) => $q->where('status', $request->status));
        }

        $batches = $query->orderBy('created_at', 'desc')->get();

        if ($request->filled('status')) {
            $assets = Asset::with(['batch', 'unit'])->where('status', $request->status)->get();

            return view('assets-inv.index', [
                'assets' => $assets,
                'assetTypes' => $assetTypes,
                'units' => $units,
                'isGrouped' => false,
                'view' => $view,
            ]);
        }

        return view('assets-inv.index', [
            'grouped' => $batches,
            'assetTypes' => $assetTypes,
            'units' => $units,
            'isGrouped' => true,
            'view' => $view,
        ]);
    }

    private function indexNonFisik(Request $request, $assetTypes, $units, $view)
    {
        $search = $request->input('search');
        $unitId = $request->input('unit_id');
        $status = $request->input('status');

        $query = IntangibleAsset::with('unit');

        if ($request->filled('type')) {
            $query->whereRaw('1 = 0');
        }

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

        // 🔥 POIN 13: Handle Filter Status Baru
        if ($status) {
            if ($status === 'Tersedia') {
                $query->where('status', 'Aktif');
            } elseif ($status === 'Kadaluarsa') {
                $query->where('status', 'Kadaluarsa');
            } elseif (in_array($status, ['Belum Aktif', 'Ditangguhkan', 'Dihentikan'])) {
                $query->where('status', $status);
            }
        }

        $groupedNonFisik = $query->orderBy('created_at', 'desc')->get()
            ->groupBy(fn ($i) => $i->name.'|'.$i->category.'|'.$i->vendor)
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

        $physicalQuery = AssetBatch::with(['assetType', 'assets.unit'])
            ->withCount('assets as quantity')
            ->whereHas('assets');

        if ($search) {
            $physicalQuery->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('brand', 'ILIKE', "%{$search}%")
                    ->orWhere('model', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $physicalQuery->whereHas('assetType', fn ($q) => $q->where('code', $request->type));
        }

        if ($unitId) {
            $physicalQuery->whereHas('assets', fn ($q) => $q->where('unit_id', $unitId));
        }
        if ($status && in_array($status, ['Tersedia', 'Dipinjam', 'Maintenance', 'Diganti'])) {
            $physicalQuery->whereHas('assets', fn ($q) => $q->where('status', $status));
        }

        $physicalGroups = $physicalQuery->get()->map(function ($batch) {
            $tersedia = $batch->assets->where('status', 'Tersedia')->count();

            // Ambil nama unit dari aset pertama di batch tersebut
            $firstAsset = $batch->assets->first();
            $unitName = $firstAsset && $firstAsset->unit ? $firstAsset->unit->name : '-';

            return (object) [
                'type' => 'Fisik',
                'name' => $batch->name,
                'category_label' => $batch->assetType->name ?? '-',
                'unit_label' => $unitName, // 🔥 SUDAH DIPERBAIKI
                'quantity' => $batch->quantity,
                'status_label' => $tersedia.' Tersedia',
                'detail_route' => route('assets-inv.group-detail', ['batch_id' => $batch->id]),
            ];
        });

        $intangibleQuery = IntangibleAsset::with('unit');

        if ($request->filled('type')) {
            $intangibleQuery->whereRaw('1 = 0');
        }

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

        // 🔥 POIN 13: Handle Filter Status Baru
        if ($status) {
            if ($status === 'Tersedia') {
                $intangibleQuery->where('status', 'Aktif');
            } elseif ($status === 'Kadaluarsa') {
                $intangibleQuery->where('status', 'Kadaluarsa');
            } elseif (in_array($status, ['Belum Aktif', 'Ditangguhkan', 'Dihentikan'])) {
                $intangibleQuery->where('status', $status);
            }
        }

        $intangibleGroups = $intangibleQuery->get()
            ->groupBy(fn ($i) => $i->name.'|'.$i->category.'|'.$i->vendor)
            ->map(function ($group) {
                $first = $group->first();
                $kadaluarsa = $group->where('status', 'Kadaluarsa')->count();

                // 🔥 POIN 13: Tampilkan status non-fisik secara dinamis di halaman gabungan
                if ($kadaluarsa > 0) {
                    $statusLabel = "{$kadaluarsa} Kadaluarsa";
                } elseif ($group->where('status', 'Dihentikan')->count() > 0) {
                    $statusLabel = 'Dihentikan';
                } elseif ($group->where('status', 'Ditangguhkan')->count() > 0) {
                    $statusLabel = 'Ditangguhkan';
                } elseif ($group->where('status', 'Belum Aktif')->count() > 0) {
                    $statusLabel = 'Belum Aktif';
                } else {
                    $statusLabel = 'Aktif';
                }

                return (object) [
                    'type' => 'Non-Fisik',
                    'name' => $first->name,
                    'category_label' => $first->category,
                    'unit_label' => $first->unit->name ?? '-',
                    'quantity' => $group->count(),
                    'status_label' => $statusLabel,
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

        $batch = AssetBatch::with(['assetType', 'assets' => function ($q) {
            $q->orderBy('serial_number');
        }, 'assets.location_ref', 'assets.unit'])
            ->findOrFail($request->query('batch_id'));

        abort_if($batch->assets->isEmpty(), 404);

        return view('assets-inv.group-detail', compact('batch'));
    }

    public function editBatch(AssetBatch $batch)
    {
        if (! in_array(auth()->user()->level, ['Sarpras', 'Admin'])) {
            abort(403, 'Hanya Sarpras yang dapat mengedit batch.');
        }

        $orderedCodes = [
            'KOM', 'JAR', 'ELK', 'LAB', 'MEB', 'ARS', 'OTO', 'TNH', 'GDG', 'PRS', 'FUR', 'ATK', 'LAN',
        ];
        $assetTypes = AssetType::all()->sortBy(function ($type) use ($orderedCodes) {
            $pos = array_search(strtoupper($type->code), $orderedCodes);

            return $pos === false ? 999 : $pos;
        });

        $fundingSources = ['Dana Yayasan', 'Hibah/Bantuan Pemerintah (LLDIKTI)', 'Dana Mandiri/UKT Mahasiswa', 'Kerja Sama Industri'];

        return view('assets-inv.edit-batch', compact('batch', 'assetTypes', 'fundingSources'));
    }

    public function updateBatch(Request $request, AssetBatch $batch)
    {
        if (! in_array(auth()->user()->level, ['Sarpras', 'Admin'])) {
            abort(403, 'Hanya Sarpras yang dapat mengedit batch.');
        }

        if ($request->has('price')) {
            $request->merge(['price' => preg_replace('/[^0-9]/', '', $request->input('price'))]);
        }
        if ($request->has('residual_value') && $request->input('residual_value') != '') {
            $request->merge(['residual_value' => preg_replace('/[^0-9]/', '', $request->input('residual_value'))]);
        }

        $schema = AssetType::getSchema($batch->assetType->code);
        $hideGeneral = $schema['hide_general'] ?? false;
        $isStaticAsset = $schema['is_static_asset'] ?? false;

        $rules = [
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand' => $hideGeneral ? 'nullable' : 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'funding_source' => $isStaticAsset ? 'nullable' : 'nullable|string|max:100',
            'economic_life_years' => $isStaticAsset ? 'nullable' : 'nullable|integer|min:1|max:100',
            'residual_value' => $isStaticAsset ? 'nullable' : 'nullable|numeric|min:0',
        ];

        foreach ($schema as $key => $field) {
            if (isset($field['in_batch']) && $field['in_batch'] === true) {
                $rule = $field['required'] ? 'required' : 'nullable';
                if ($field['type'] === 'number') {
                    $rule .= '|numeric';
                } elseif ($field['type'] === 'select' && isset($field['options'])) {
                    $rule .= '|in:'.implode(',', array_keys($field['options']));
                } elseif ($field['type'] === 'file') {
                    $rule .= '|file|mimes:pdf,jpg,jpeg,png|max:5120';
                }
                $rules["category_data.{$key}"] = $rule;
            }
        }

        $validated = $request->validate($rules);

        if ($isStaticAsset) {
            $validated['funding_source'] = null;
            $validated['economic_life_years'] = null;
            $validated['residual_value'] = null;
        }

        $categoryData = $batch->category_data ?? [];
        $inputCategoryData = $request->input('category_data', []);

        foreach ($schema as $key => $field) {
            if (isset($field['in_batch'])) {
                if (($field['type'] ?? '') === 'file') {
                    // Handling multiple file di update
                    if (! empty($field['multiple'])) {
                        if ($request->hasFile("category_data.{$key}")) {
                            $existingPaths = $categoryData[$key] ?? [];
                            if (! is_array($existingPaths)) {
                                $existingPaths = $existingPaths ? [$existingPaths] : [];
                            }

                            foreach ($request->file("category_data.{$key}") as $file) {
                                if ($file->isValid()) {
                                    $existingPaths[] = $file->store('legal-documents/batch', 'public');
                                }
                            }
                            $categoryData[$key] = $existingPaths;
                        }
                    } else {
                        // Single file logic (sama seperti sebelumnya)
                        if ($request->hasFile("category_data.{$key}")) {
                            $file = $request->file("category_data.{$key}");
                            if ($file->isValid()) {
                                if (isset($categoryData[$key]) && Storage::disk('public')->exists($categoryData[$key])) {
                                    Storage::disk('public')->delete($categoryData[$key]);
                                }
                                $categoryData[$key] = $file->store('legal-documents/batch', 'public');
                            }
                        }
                    }
                } else {
                    if (isset($inputCategoryData[$key])) {
                        $categoryData[$key] = $inputCategoryData[$key];
                    }
                }
            }
        }

        $batch->update([
            'asset_type_id' => $validated['asset_type_id'],
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'model' => $validated['model'] ?? null,
            'price' => $validated['price'],
            'purchase_date' => $validated['purchase_date'],
            'funding_source' => $validated['funding_source'] ?? null,
            'economic_life_years' => $validated['economic_life_years'] ?? null,
            'residual_value' => $validated['residual_value'] ?? null,
            'category_data' => $categoryData,
        ]);

        return redirect()->route('assets-inv.group-detail', ['batch_id' => $batch->id])
            ->with('success', 'Data Induk (Batch) berhasil diupdate!');
    }

    public function create()
    {
        $assetTypes = AssetType::getOrdered();
        $units = Unit::with('locations')->whereNotNull('category')->orderBy('category')->orderBy('name')->get();
        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level');
        $fundingSources = ['Dana Yayasan', 'Hibah/Bantuan Pemerintah (LLDIKTI)', 'Dana Mandiri/UKT Mahasiswa', 'Kerja Sama Industri'];

        $schemas = [];
        foreach (AssetType::all() as $type) {
            $schemas[$type->id] = AssetType::getSchema($type->code);
        }

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

        return view('assets-inv.create', compact('assetTypes', 'units', 'usersByLevel', 'unitsForJs', 'fundingSources', 'schemas'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Asset::class);

        $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
        ]);

        if ($request->has('price')) {
            $request->merge(['price' => preg_replace('/[^0-9]/', '', $request->input('price'))]);
        }
        if ($request->has('residual_value') && $request->input('residual_value') != '') {
            $request->merge(['residual_value' => preg_replace('/[^0-9]/', '', $request->input('residual_value'))]);
        }

        $assetType = AssetType::find($request->input('asset_type_id'));

        if (! $assetType) {
            return back()->withInput()->with('error', 'Data Jenis Aset tidak ditemukan di database. Silakan muat ulang (refresh) halaman ini dan coba lagi.');
        }

        $schema = AssetType::getSchema($assetType->code);
        $hideGeneral = $schema['hide_general'] ?? false;
        $isStaticAsset = $schema['is_static_asset'] ?? false;

        $unit = Unit::find($request->input('unit_id'));
        $hasLocations = $unit && $unit->locations()->exists();

        $rules = [
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'brand' => $hideGeneral ? 'nullable' : 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'unit_id' => $isStaticAsset ? 'nullable' : 'required|exists:units,id',
            'location_id' => ($isStaticAsset || ! $hasLocations) ? 'nullable|exists:locations,id' : 'required|exists:locations,id',
            'location_detail' => $isStaticAsset ? 'nullable|string|max:255' : ($hasLocations ? 'nullable|string|max:255' : 'required|string|max:255'),
            'funding_source' => $isStaticAsset ? 'nullable' : 'nullable|string|max:100',
            'economic_life_years' => $isStaticAsset ? 'nullable' : 'nullable|integer|min:1|max:100',
            'residual_value' => $isStaticAsset ? 'nullable' : 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:100',
            'conditions' => 'required|array',
            'conditions.*' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'penanggung_jawab_id' => 'nullable|exists:users,id',
        ];

        if (! $hideGeneral) {
            $rules['serial_numbers'] = 'required|array|size:'.$request->input('quantity', 1);
            $rules['serial_numbers.*'] = ['required', 'string', 'distinct', Rule::unique('assets', 'serial_number')->whereNull('deleted_at')];
        }

        foreach ($schema as $key => $field) {
            if (isset($field['type']) && $field['type'] !== 'hidden') {
                $rule = $field['required'] ? 'required' : 'nullable';
                if ($field['type'] === 'number') {
                    $rule .= '|numeric';
                } elseif ($field['type'] === 'select' && isset($field['options'])) {
                    $rule .= '|in:'.implode(',', array_keys($field['options']));
                } elseif ($field['type'] === 'file') {
                    // Handling multiple file
                    if (! empty($field['multiple'])) {
                        $rules["category_data.{$key}"] = $rule.'|array';
                        $rules["category_data.{$key}.*"] = 'file|mimes:pdf,jpg,jpeg,png|max:5120';

                        continue;
                    } else {
                        $rule .= '|file|mimes:pdf,jpg,jpeg,png|max:5120';
                    }
                }

                if (isset($field['in_batch'])) {
                    $rules["category_data.{$key}"] = $rule;
                }
                if (isset($field['in_unit'])) {
                    $rules["unit_data.*.{$key}"] = $rule;
                }
            }
        }

        $validated = $request->validate($rules);

        if ($isStaticAsset) {
            $validated['funding_source'] = null;
            $validated['economic_life_years'] = null;
            $validated['residual_value'] = null;
            $validated['unit_id'] = null;
            $validated['location_id'] = null;
            $validated['location_detail'] = null;
        }

        $locationName = ! empty($validated['location_id']) ? Location::find($validated['location_id'])->name : null;
        $locationString = trim(collect([$locationName, $validated['location_detail'] ?? null])->filter()->implode(' - '));

        DB::beginTransaction();
        try {
            $categoryData = $request->input('category_data', []);
            if ($request->hasFile('category_data')) {
                foreach ($request->file('category_data') as $key => $fileOrFiles) {
                    // Jika berupa array (multiple file)
                    if (is_array($fileOrFiles)) {
                        $paths = [];
                        foreach ($fileOrFiles as $file) {
                            if ($file->isValid()) {
                                $paths[] = $file->store('legal-documents/batch', 'public');
                            }
                        }
                        $categoryData[$key] = $paths;
                    }
                    // Single file biasa
                    elseif ($fileOrFiles->isValid()) {
                        $categoryData[$key] = $fileOrFiles->store('legal-documents/batch', 'public');
                    }
                }
            }

            $batch = AssetBatch::create([
                'asset_type_id' => $validated['asset_type_id'],
                'name' => $validated['name'],
                'brand' => $validated['brand'] ?? null,
                'model' => $validated['model'] ?? null,
                'price' => $validated['price'],
                'purchase_date' => $validated['purchase_date'],
                'funding_source' => $validated['funding_source'] ?? null,
                'economic_life_years' => $validated['economic_life_years'] ?? null,
                'residual_value' => $validated['residual_value'] ?? null,
                'category_data' => $categoryData,
            ]);

            $created = 0;

            for ($i = 0; $i < $validated['quantity']; $i++) {
                $imagePath = $request->hasFile("images.$i")
                    ? $request->file("images.$i")->store('assets', 'public')
                    : null;

                $unitData = $request->input("unit_data.{$i}", []);
                if ($request->hasFile("unit_data.{$i}")) {
                    foreach ($request->file("unit_data.{$i}") as $key => $file) {
                        if (is_object($file) && $file->isValid()) {
                            $unitData[$key] = $file->store('legal-documents/unit', 'public');
                        }
                    }
                }

                $newAssetId = Asset::generateAssetId($assetType->id);

                $asset = Asset::create([
                    'asset_id' => $newAssetId,
                    'asset_batch_id' => $batch->id,
                    'serial_number' => $validated['serial_numbers'][$i] ?? null,
                    'location' => $locationString,
                    'location_id' => $validated['location_id'] ?? null,
                    'location_detail' => $validated['location_detail'] ?? null,
                    'unit_id' => $validated['unit_id'] ?? null,
                    'condition' => $validated['conditions'][$i],
                    'status' => 'Tersedia',
                    'image' => $imagePath,
                    'qr_code' => QrCode::generateCodeContent($assetType->code),
                    'penanggung_jawab_id' => $validated['penanggung_jawab_id'] ?? null,
                    'unit_data' => $unitData,
                ]);

                QrCode::create([
                    'qr_code_id' => 'QCD-'.str_pad($asset->id, 3, '0', STR_PAD_LEFT),
                    'asset_id' => $asset->id,
                    'code_content' => $asset->qr_code,
                    'status' => 'Aktif',
                ]);

                $created++;
            }

            DB::commit();

            return redirect()->route('assets-inv.index')->with('success', "Batch aset & {$created} unit berhasil ditambahkan!");
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan aset: '.$e->getMessage());
        }
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'batch.assetType',
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
        $orderedCodes = [
            'KOM', 'JAR', 'ELK', 'LAB', 'MEB', 'ARS', 'OTO', 'TNH', 'GDG', 'PRS', 'FUR', 'ATK', 'LAN',
        ];
        $assetTypes = AssetType::all()->sortBy(function ($type) use ($orderedCodes) {
            $pos = array_search(strtoupper($type->code), $orderedCodes);

            return $pos === false ? 999 : $pos;
        });

        $units = Unit::with('locations')->whereNotNull('category')->orderBy('category')->orderBy('name')->get();
        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level');
        $fundingSources = ['Dana Yayasan', 'Hibah/Bantuan Pemerintah (LLDIKTI)', 'Dana Mandiri/UKT Mahasiswa', 'Kerja Sama Industri'];

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

        $schemas = [];
        foreach (AssetType::all() as $type) {
            $schemas[$type->id] = AssetType::getSchema($type->code);
        }

        return view('assets-inv.edit', compact('asset', 'assetTypes', 'units', 'usersByLevel', 'unitsForJs', 'fundingSources', 'schemas'));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);

        $assetType = $asset->batch->assetType;
        $schema = AssetType::getSchema($assetType->code);
        $hideGeneral = $schema['hide_general'] ?? false;
        $isStaticAsset = $schema['is_static_asset'] ?? false;

        $rules = [
            'unit_id' => $isStaticAsset ? 'nullable' : 'required|exists:units,id',
            'location_id' => $isStaticAsset ? 'nullable|exists:locations,id' : 'nullable|exists:locations,id',
            'location_detail' => $isStaticAsset ? 'nullable|string|max:255' : 'nullable|string|max:255',
            'serial_number' => $hideGeneral ? 'nullable' : ['required', 'string', Rule::unique('assets', 'serial_number')->ignore($asset->id)->whereNull('deleted_at')],
            'condition' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'penanggung_jawab_id' => 'nullable|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $unitDataRules = [];
        foreach ($schema as $key => $field) {
            if (isset($field['in_unit']) && $field['in_unit'] === true) {
                $rule = $field['required'] ? 'required' : 'nullable';
                if ($field['type'] === 'number') {
                    $rule .= '|numeric';
                } elseif ($field['type'] === 'select' && isset($field['options'])) {
                    $rule .= '|in:'.implode(',', array_keys($field['options']));
                } elseif ($field['type'] === 'file') {
                    $rule .= '|file|mimes:pdf,jpg,jpeg,png|max:5120';
                }
                $unitDataRules["unit_data.{$key}"] = $rule;
            }
        }

        $validated = $request->validate($rules);
        $request->validate($unitDataRules);

        if (! $isStaticAsset) {
            $unit = Unit::find($validated['unit_id']);
            $hasLocations = $unit && $unit->locations()->exists();

            if ($hasLocations && empty($validated['location_id'])) {
                return back()->withErrors(['location_id' => 'Lokasi Spesifik wajib diisi untuk unit ini.'])->withInput();
            }

            if (! $hasLocations && empty($validated['location_detail'])) {
                return back()->withErrors(['location_detail' => 'Detail Penempatan wajib diisi karena unit ini belum memiliki daftar lokasi spesifik.'])->withInput();
            }
        }

        $locationName = ! empty($validated['location_id']) ? Location::find($validated['location_id'])->name : null;
        $locationString = trim(collect([$locationName, $validated['location_detail'] ?? null])->filter()->implode(' - '));

        if (empty($locationString)) {
            $locationString = $asset->location;
        }

        $unitData = $asset->unit_data ?? [];
        $inputUnitData = $request->input('unit_data', []);

        foreach ($schema as $key => $field) {
            if (isset($field['in_unit'])) {
                if (($field['type'] ?? '') === 'file') {
                    if ($request->hasFile("unit_data.{$key}")) {
                        $file = $request->file("unit_data.{$key}");
                        if ($file->isValid()) {
                            if (isset($unitData[$key]) && Storage::disk('public')->exists($unitData[$key])) {
                                Storage::disk('public')->delete($unitData[$key]);
                            }
                            $unitData[$key] = $file->store('legal-documents/unit', 'public');
                        }
                    }
                } else {
                    if (isset($inputUnitData[$key])) {
                        $unitData[$key] = $inputUnitData[$key];
                    }
                }
            }
        }

        $asset->unit_id = $validated['unit_id'] ?? null;
        $asset->location_id = $validated['location_id'] ?? null;
        $asset->location_detail = $validated['location_detail'] ?? null;
        $asset->location = $locationString;
        $asset->serial_number = $validated['serial_number'] ?? null;
        $asset->condition = $validated['condition'];
        $asset->penanggung_jawab_id = $validated['penanggung_jawab_id'] ?? null;
        $asset->unit_data = $unitData;

        if ($request->hasFile('image')) {
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }
            $asset->image = $request->file('image')->store('assets', 'public');
        }

        $asset->save();

        return redirect()->route('assets-inv.group-detail', ['batch_id' => $asset->asset_batch_id])
            ->with('success', 'Data unit aset berhasil diupdate!');
    }

    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset);

        $batchId = $asset->asset_batch_id;

        try {
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }
            $asset->qrCodes()->delete();
            $asset->delete();

            $sisaUnit = Asset::where('asset_batch_id', $batchId)->count();
            if ($sisaUnit == 0) {
                AssetBatch::find($batchId)?->delete();

                return redirect()->route('assets-inv.index')->with('success', 'Unit aset terakhir dihapus, beserta data batchnya.');
            }

            return redirect()->route('assets-inv.group-detail', ['batch_id' => $batchId])
                ->with('success', '1 unit aset berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('assets-inv.index')
                ->with('error', 'Gagal menghapus aset: '.$e->getMessage());
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

            return response()->json(['success' => true, 'message' => "{$deleted} aset berhasil dihapus."]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['success' => false, 'message' => 'Gagal menghapus aset: '.$e->getMessage()], 500);
        }
    }

    public function showModal(Asset $asset)
    {
        $asset->load(['batch.assetType', 'qrCodes', 'borrowings', 'maintenances']);

        return response()->json(['html' => view('assets-inv.modal-detail', compact('asset'))->render()]);
    }

    public function guide()
    {
        return view('assets-inv.guide');
    }
}
