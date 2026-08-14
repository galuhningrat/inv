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
use App\Http\Controllers\IntangibleAssetController;
use App\Models\IntangibleAsset;

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
            return $this->indexSemua($assetTypes, $units, $view);
        }

        // view === 'fisik' — PERSIS perilaku lama
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
            $assets = $query->orderBy('created_at', 'desc')->get();
            return view('assets-inv.index', [
                'assets' => $assets,
                'assetTypes' => $assetTypes,
                'units' => $units,
                'isGrouped' => false, // ✅ dikirim
                'view' => $view
            ]);
        }

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
            'units' => $units,
            'isGrouped' => true,
            'view' => $view
        ]);
    }

    private function indexNonFisik(Request $request, $assetTypes, $units, $view)
    {
        $query = IntangibleAsset::with('unit');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('vendor', 'ILIKE', "%{$search}%")
                    ->orWhere('asset_code', 'ILIKE', "%{$search}%");
            });
        }
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
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
            'isGrouped' => false, // ✅ tambahkan
        ]);
    }

    private function indexSemua($assetTypes, $units, $view)
    {
        $physicalGroups = Asset::with('assetType', 'unit')->get()
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

        $intangibleGroups = IntangibleAsset::with('unit')->get()
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
            'isGrouped' => false, // ✅ tambahkan
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

        $query = Asset::with('assetType')
            ->where('name', $validated['name'])
            ->where('asset_type_id', $validated['type_id'])
            ->where('brand', $validated['brand']);

        // $this->authorize('viewAny', Asset::class);

        // $query = Asset::with('assetType')
        //     ->where('name', $validated['name'])
        //     ->where('asset_type_id', $validated['type_id'])
        //     ->where('brand', $validated['brand']);

        // $user = auth()->user();
        // if (in_array($user->level, ['Kalab', 'Kaprodi', 'Aslab'])) {
        //     $query->where('unit_id', $user->unit_id);
        // }

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
        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level');

        $rules = [
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'unit_id' => 'required|exists:units,id',
            'location_id' => $hasLocations ? 'required|exists:locations,id' : 'nullable',      // ✅ kondisional
            'location_detail' => $hasLocations ? 'nullable|string|max:255' : 'required|string|max:255', // ✅ kondisional
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
                    'location_id' => $validated['location_id'],
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
        $this->authorize('update', $asset);

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
        $this->authorize('delete', $asset);

        $groupSnapshot = ['name' => $asset->name, 'type_id' => $asset->asset_type_id, 'brand' => $asset->brand];

        try {
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }
            $asset->qrCodes()->delete();
            $asset->delete();

            return redirect($this->resolveGroupRedirectAfterDelete(request(), $groupSnapshot)) // ✅ dipakai
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

    // TAMBAHAN: Show detail di modal
    public function showModal(Asset $asset)
    {
        $asset->load(['assetType', 'qrCodes', 'borrowings', 'maintenances']);

        return response()->json([
            'html' => view('assets-inv.modal-detail', compact('asset'))->render()
        ]);
    }

    public function showReceiveForm(AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'Sarpras' && Auth::user()->level !== 'Admin') {
            abort(403, 'Hanya Bagian Sarpras yang dapat melakukan registrasi aset.');
        }
        if ($assetRequest->status !== 'Dikonfirmasi') {
            return redirect()->route('requests.index')->with('error', 'Barang belum dikonfirmasi diterima secara fisik oleh PJ Pengadaan.');
        }

        $assetRequest->load('items.assetType');

        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level');
        $hasPhysical = $assetRequest->items->contains('item_type', 'Fisik');
        $units = $hasPhysical ? \App\Models\Unit::with('locations')->whereNotNull('category')->orderBy('category')->orderBy('name')->get() : collect();

        return view('requests.receive', compact('assetRequest', 'usersByLevel', 'units', 'hasPhysical'));
    }

    public function receive(Request $request, AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'Sarpras' && Auth::user()->level !== 'Admin') {
            abort(403, 'Hanya Bagian Sarpras yang dapat melakukan registrasi aset.');
        }
        if ($assetRequest->status !== 'Dikonfirmasi') {
            return redirect()->route('requests.index')->with('error', 'Barang belum dikonfirmasi diterima secara fisik.');
        }

        $assetRequest->load('items');
        $hasPhysical = $assetRequest->items->contains('item_type', 'Fisik');

        $rules = [
            'purchase_date' => 'required|date',
            'penanggung_jawab_id' => 'nullable|exists:users,id',
        ];

        if ($hasPhysical) {
            $rules['unit_id'] = 'required|exists:units,id';
            $rules['location_id'] = 'nullable|exists:locations,id';
            $rules['location_detail'] = 'nullable|string|max:255';
        }

        foreach ($assetRequest->items as $item) {
            if ($item->item_type === 'Fisik') {
                $rules["brand.{$item->id}"] = 'required|string|max:255';
                $rules["prices.{$item->id}"] = 'required|numeric|min:0';
                for ($i = 0; $i < $item->quantity; $i++) {
                    $rules["images.{$item->id}.{$i}"] = 'required|image|mimes:jpg,jpeg,png,webp|max:2048';
                    $rules["serial_numbers.{$item->id}.{$i}"] = 'required|string|distinct|unique:assets,serial_number';
                    $rules["conditions.{$item->id}.{$i}"] = 'required|in:Baik,Rusak Ringan,Rusak Berat';
                    $rules["expired_dates.{$item->id}.{$i}"] = 'nullable|date';
                    $rules["unit_names.{$item->id}.{$i}"] = 'nullable|string|max:255';
                }
            } else { // Non-Fisik
                $rules["categories.{$item->id}"] = 'required|in:Software,HAKI/Paten,Jurnal Ilmiah,Domain/Hosting,Kurikulum';
                $rules["vendors.{$item->id}"] = 'required|string|max:255';
                $rules["prices.{$item->id}"] = 'required|numeric|min:0';
                $rules["funding_sources.{$item->id}"] = 'nullable|string|max:255';
                $rules["contract_numbers.{$item->id}"] = 'nullable|string|max:255';
                $rules["license_types.{$item->id}"] = 'required|in:Berlangganan,Selamanya';
                $rules["expiry_dates.{$item->id}"] = 'required_if:license_types.' . $item->id . ',Berlangganan|nullable|date';
                $rules["access_urls.{$item->id}"] = 'nullable|url|max:255';
                $rules["certificate_files.{$item->id}"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                for ($i = 0; $i < $item->quantity; $i++) {
                    $rules["product_keys.{$item->id}.{$i}"] = 'nullable|string|max:255';
                    $rules["assigned_emails.{$item->id}.{$i}"] = 'nullable|email|max:255';
                }
            }
        }

        $validated = $request->validate($rules);

        $locationName = $hasPhysical && !empty($validated['location_id']) ? \App\Models\Location::find($validated['location_id'])->name : null;
        $locationString = trim(collect([$locationName, $validated['location_detail'] ?? null])->filter()->implode(' - '));

        DB::beginTransaction();
        try {
            foreach ($assetRequest->items as $item) {
                if ($item->item_type === 'Fisik') {
                    $imagePath = null; // per-unit di bawah
                    for ($i = 0; $i < $item->quantity; $i++) {
                        $unitImagePath = $request->file("images.{$item->id}.{$i}")->store('assets', 'public');

                        $asset = new Asset();
                        $asset->name = !empty($validated['unit_names'][$item->id][$i] ?? null) ? $validated['unit_names'][$item->id][$i] : $item->item_name;
                        $asset->asset_type_id = $item->asset_type_id;
                        $asset->brand = $validated['brand'][$item->id];
                        $asset->serial_number = $validated['serial_numbers'][$item->id][$i];
                        $asset->price = $validated['prices'][$item->id];
                        $asset->purchase_date = $validated['purchase_date'];
                        $asset->expired_at = $validated['expired_dates'][$item->id][$i] ?? null;
                        $asset->location = $locationString;
                        $asset->location_id = $validated['location_id'] ?? null;
                        $asset->location_detail = $validated['location_detail'] ?? null;
                        $asset->unit_id = $validated['unit_id'];
                        $asset->condition = $validated['conditions'][$item->id][$i];
                        $asset->status = 'Tersedia';
                        $asset->image = $unitImagePath;
                        $asset->penanggung_jawab_id = $validated['penanggung_jawab_id'] ?? $assetRequest->requester_id;
                        $asset->asset_request_id = $assetRequest->id;
                        $asset->qr_code = QrCode::generateCodeContent($item->assetType->code);
                        $asset->save();

                        QrCode::create(['asset_id' => $asset->id, 'code_content' => $asset->qr_code, 'status' => 'Aktif']);
                    }
                } else { // Non-Fisik
                    $certificatePath = $request->hasFile("certificate_files.{$item->id}")
                        ? $request->file("certificate_files.{$item->id}")->store('intangible-certificates', 'public')
                        : null;

                    for ($i = 0; $i < $item->quantity; $i++) {
                        \App\Models\IntangibleAsset::create([
                            'name' => $item->item_name,
                            'category' => $validated['categories'][$item->id],
                            'vendor' => $validated['vendors'][$item->id],
                            'price' => $validated['prices'][$item->id],
                            'activation_date' => $validated['purchase_date'],
                            'funding_source' => $validated['funding_sources'][$item->id] ?? null,
                            'contract_number' => $validated['contract_numbers'][$item->id] ?? null,
                            'license_type' => $validated['license_types'][$item->id],
                            'expiry_date' => $validated['expiry_dates'][$item->id] ?? null,
                            'quota' => null,
                            'unit_id' => $assetRequest->unit_id,
                            'pic_id' => $validated['penanggung_jawab_id'] ?? null,
                            'access_url' => $validated['access_urls'][$item->id] ?? null,
                            'certificate_file' => $certificatePath,
                            'product_key' => $validated['product_keys'][$item->id][$i] ?? null,
                            'assigned_user_email' => $validated['assigned_emails'][$item->id][$i] ?? null,
                            'status' => 'Aktif',
                            'created_by' => Auth::id(),
                            'asset_request_id' => $assetRequest->id,
                        ]);
                    }
                }
            }

            $assetRequest->update(['status' => 'Diterima']);
            DB::commit();

            return redirect()->route('requests.index')->with('success', 'Semua item berhasil diregistrasi ke inventaris!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Gagal memproses registrasi: ' . $e->getMessage());
        }
    }
}