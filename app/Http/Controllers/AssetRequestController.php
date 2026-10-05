<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\AssetType;
use App\Models\QrCode;
use App\Models\RolloverLog;
use App\Models\ServiceRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetRequest::with('requester', 'items', 'verifier', 'approver');

        $user = Auth::user();
        if (in_array($user->level, ['Kaprodi', 'Kalab'])) {
            $query->whereHas('items', function ($q) use ($user) {
                $q->where('unit_id', $user->unit_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10);

        return view('requests.index', compact('requests'));
    }

    /**
     * Fase 3: render form jasa sesuai kategori yang dipilih dari modal katalog.
     * Satu view utama (create-service) + partial per kategori untuk field spesifik.
     */
    public function createService(Request $request, ?string $category = null)
    {
        $this->authorize('create', AssetRequest::class);

        if (! $category) {
            return redirect()->route('requests.index')
                ->with('info', 'Silakan pilih jenis jasa melalui tombol "+ Tambah Pengajuan Baru".');
        }

        $validCategories = array_keys(ServiceRequestItem::CATEGORIES);
        if (! in_array($category, $validCategories, true)) {
            abort(404, 'Kategori jasa tidak ditemukan.');
        }

        $user = Auth::user();
        $categoryLabel = ServiceRequestItem::CATEGORIES[$category];
        $schema = ServiceRequestItem::SERVICE_DATA_SCHEMA[$category] ?? [];

        $canChooseUnit = in_array($user->level, ['Sarpras', 'Admin']);
        $units = $canChooseUnit
            ? \App\Models\Unit::whereNotNull('category')->orderBy('category')->orderBy('name')->get()
            : collect();
        $currentUnitName = $user->unit_id ? \App\Models\Unit::find($user->unit_id)?->name : null;

        $priorities = ['Normal', 'Mendesak', 'Sangat Mendesak'];

        // Data khusus kategori pemeliharaan: dropdown "Aset yang Ingin Diperbaiki"
        $assets = $category === 'pemeliharaan'
            ? Asset::orderBy('name')->get()
            : collect();

        return view('requests.create-service', compact(
            'category',
            'categoryLabel',
            'schema',
            'canChooseUnit',
            'units',
            'currentUnitName',
            'priorities',
            'assets'
        ));
    }

    /**
     * Fase 3: simpan pengajuan jasa ke asset_requests (header) + service_request_items (detail).
     * Field spesifik per kategori disimpan sebagai JSON di kolom `service_data`.
     *
     * Catatan: field "Alasan Pengajuan" & "Aset yang Diganti" TIDAK dipakai di
     * form jasa — konsepnya spesifik untuk pengadaan aset (Pengadaan Baru /
     * Penggantian / Pengisian Kembali), tidak ada di formulir resmi
     * "Rencana Pemeliharaan" dan tidak relevan untuk jasa. Kolomnya nullable
     * di database, jadi di-set null di sini.
     */
    public function storeService(Request $request, string $category)
    {
        $this->authorize('create', AssetRequest::class);

        $validCategories = array_keys(ServiceRequestItem::CATEGORIES);
        if (! in_array($category, $validCategories, true)) {
            abort(404, 'Kategori jasa tidak ditemukan.');
        }

        $user = Auth::user();
        $canChooseUnit = in_array($user->level, ['Sarpras', 'Admin']);

        // ===== Base rules (field umum semua item jasa) =====
        $rules = [
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.specification' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_price_per_unit' => 'nullable|numeric|min:0',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.priority' => 'required|in:Normal,Mendesak,Sangat Mendesak',
            'items.*.reason' => 'required|string',
        ];

        // ===== Dynamic rules dari schema kategori =====
        $schema = ServiceRequestItem::SERVICE_DATA_SCHEMA[$category] ?? [];
        foreach ($schema as $field => $fieldRules) {
            $type = $fieldRules['type'] ?? 'string';
            $required = ! empty($fieldRules['required']);
            $in = ! empty($fieldRules['in']) ? '|in:' . implode(',', $fieldRules['in']) : '';

            $rule = $required ? 'required' : 'nullable';
            $rule .= match ($type) {
                'integer' => '|integer',
                'numeric' => '|numeric',
                'date' => '|date',
                'url' => '|url',
                default => '|string|max:1000',
            };
            if (isset($fieldRules['min'])) {
                $rule .= '|min:' . $fieldRules['min'];
            }
            $rule .= $in;

            $rules["items.*.service_data.$field"] = $rule;
        }

        // Khusus pemeliharaan: validasi asset_id exists
        if ($category === 'pemeliharaan') {
            $rules['items.*.service_data.asset_id'] = 'required|exists:assets,id';
        }

        $validated = $request->validate($rules);

        // ===== Cross validation per item =====
        foreach ($validated['items'] as $idx => $item) {
            if (! $canChooseUnit && $item['unit_id'] != $user->unit_id) {
                return back()->withInput()->withErrors([
                    "items.$idx.unit_id" => 'Anda hanya boleh mengajukan untuk unit Anda sendiri.',
                ]);
            }
        }

        $firstItem = $validated['items'][0];

        DB::beginTransaction();
        try {
            $assetRequest = AssetRequest::create([
                'requester_id' => Auth::id(),
                'unit_id' => $firstItem['unit_id'],
                'period_month' => now()->month,
                'period_year' => now()->year,
                'request_type' => 'jasa',
                'service_category' => $category,
                'alasan_pengajuan' => null,       // jasa tidak butuh alasan ini
                'related_asset_id' => null,
                'priority' => $firstItem['priority'],
                'reason' => $firstItem['reason'],
                'status' => 'Pending',
            ]);

            foreach ($validated['items'] as $item) {
                $serviceData = [];
                foreach ($schema as $field => $_) {
                    if (isset($item['service_data'][$field])) {
                        $serviceData[$field] = $item['service_data'][$field];
                    }
                }

                $assetRequest->serviceItems()->create([
                    'unit_id' => $item['unit_id'],
                    'priority' => $item['priority'],
                    'alasan_pengajuan' => null,   // jasa tidak butuh alasan ini
                    'reason' => $item['reason'],
                    'service_category' => $category,
                    'item_name' => $item['item_name'],
                    'specification' => $item['specification'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_price_per_unit' => $item['estimated_price_per_unit'] ?? null,
                    'service_data' => $serviceData,
                    'approval_status' => 'pending',
                ]);
            }

            DB::commit();

            return redirect()->route('requests.index')
                ->with('success', 'Pengajuan jasa berhasil dikirim, menunggu verifikasi Tim Sarpras!');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withInput()
                ->with('error', 'Gagal mengirim pengajuan jasa: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $user = Auth::user();
        $assetTypes = AssetType::all();
        $priorities = ['Normal', 'Mendesak', 'Sangat Mendesak'];
        $alasanOptions = ['Pengadaan Baru', 'Penggantian', 'Pengisian Kembali'];
        $assets = Asset::orderBy('name')->get();

        $canChooseUnit = in_array($user->level, ['Sarpras', 'Admin']);
        $units = $canChooseUnit
            ? \App\Models\Unit::whereNotNull('category')->orderBy('category')->orderBy('name')->get()
            : collect();
        $currentUnitName = $user->unit_id ? \App\Models\Unit::find($user->unit_id)?->name : null;

        $nonFisikCategories = \App\Models\IntangibleAsset::CATEGORIES;
        $habisPakaiCategories = AssetRequestItem::HABIS_PAKAI_CATEGORIES;
        $unitsFisik = AssetRequestItem::UNITS_FISIK;
        $unitsNonFisik = AssetRequestItem::UNITS_NON_FISIK;
        $sifatBarangFisik = AssetRequestItem::SIFAT_BARANG_FISIK;
        $sifatBarangNonFisik = AssetRequestItem::SIFAT_BARANG_NON_FISIK;

        return view('requests.create', compact(
            'assetTypes',
            'priorities',
            'alasanOptions',
            'assets',
            'canChooseUnit',
            'units',
            'currentUnitName',
            'nonFisikCategories',
            'habisPakaiCategories',
            'unitsFisik',
            'unitsNonFisik',
            'sifatBarangFisik',
            'sifatBarangNonFisik'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', AssetRequest::class);

        $user = Auth::user();
        $canChooseUnit = in_array($user->level, ['Sarpras', 'Admin']);

        $validated = $request->validate([
            'jenis_barang' => 'sometimes|nullable|in:Habis Pakai,Tidak Habis Pakai,Jasa',
            'kategori_barang' => 'sometimes|nullable|in:ATK,Konsumsi,Alat,Furniture,Lainnya',

            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:Fisik,Non-Fisik',
            'items.*.sifat_barang' => 'required|in:Tidak Habis Pakai,Habis Pakai,Jasa',
            'items.*.asset_type_id' => 'nullable|exists:asset_types,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.specification' => 'nullable|string|max:255',
            'items.*.category' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_price_per_unit' => 'nullable|numeric|min:0',

            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.priority' => 'required|in:Normal,Mendesak,Sangat Mendesak',
            'items.*.alasan_pengajuan' => 'required|in:Pengadaan Baru,Penggantian,Pengisian Kembali',
            'items.*.reason' => 'required|string',
            'items.*.related_asset_id' => 'nullable|exists:assets,id',
        ]);

        foreach ($validated['items'] as $idx => $item) {
            $comboValid = ($item['item_type'] === 'Fisik' && in_array($item['sifat_barang'], AssetRequestItem::SIFAT_BARANG_FISIK))
                || ($item['item_type'] === 'Non-Fisik' && in_array($item['sifat_barang'], AssetRequestItem::SIFAT_BARANG_NON_FISIK));

            if (! $comboValid) {
                return back()->withInput()->withErrors([
                    "items.$idx.sifat_barang" => 'Kombinasi Jenis Item dan Sifat Barang tidak valid.',
                ]);
            }

            if ($item['item_type'] === 'Fisik' && $item['sifat_barang'] !== 'Habis Pakai') {
                if (empty($item['asset_type_id'])) {
                    return back()->withInput()->withErrors(["items.$idx.asset_type_id" => 'Jenis Aset wajib dipilih.']);
                }
            } elseif ($item['item_type'] === 'Fisik' && $item['sifat_barang'] === 'Habis Pakai') {
                if (empty($item['category']) || ! array_key_exists($item['category'], AssetRequestItem::HABIS_PAKAI_CATEGORIES)) {
                    return back()->withInput()->withErrors(["items.$idx.category" => 'Kategori Habis Pakai wajib dipilih.']);
                }
            } elseif ($item['item_type'] === 'Non-Fisik' && $item['sifat_barang'] !== 'Jasa') {
                if (empty($item['category']) || ! array_key_exists($item['category'], \App\Models\IntangibleAsset::CATEGORIES)) {
                    return back()->withInput()->withErrors(["items.$idx.category" => 'Kategori Non-Fisik wajib dipilih.']);
                }
            }

            if ($item['alasan_pengajuan'] === 'Penggantian' && empty($item['related_asset_id'])) {
                return back()->withInput()->withErrors([
                    "items.$idx.related_asset_id" => 'Aset yang diganti wajib dipilih untuk alasan Penggantian.',
                ]);
            }

            if (! $canChooseUnit && $item['unit_id'] != $user->unit_id) {
                return back()->withInput()->withErrors([
                    "items.$idx.unit_id" => 'Anda hanya boleh mengajukan untuk unit Anda sendiri.',
                ]);
            }
        }

        $firstItem = $validated['items'][0];

        DB::beginTransaction();
        try {
            $assetRequest = AssetRequest::create([
                'requester_id' => Auth::id(),
                'unit_id' => $firstItem['unit_id'],
                'period_month' => now()->month,
                'period_year' => now()->year,
                'jenis_barang' => $validated['jenis_barang'] ?? null,
                'kategori_barang' => $validated['kategori_barang'] ?? null,
                'alasan_pengajuan' => $firstItem['alasan_pengajuan'],
                'related_asset_id' => $firstItem['related_asset_id'] ?? null,
                'priority' => $firstItem['priority'],
                'reason' => $firstItem['reason'],
                'status' => 'Pending',
            ]);

            foreach ($validated['items'] as $item) {
                $assetRequest->items()->create([
                    'item_type' => $item['item_type'],
                    'sifat_barang' => $item['sifat_barang'],
                    'asset_type_id' => $item['asset_type_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'specification' => $item['specification'] ?? null,
                    'category' => $item['category'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_price_per_unit' => $item['estimated_price_per_unit'] ?? null,
                    'unit_id' => $item['unit_id'],
                    'priority' => $item['priority'],
                    'alasan_pengajuan' => $item['alasan_pengajuan'],
                    'reason' => $item['reason'],
                    'related_asset_id' => $item['related_asset_id'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('requests.index')
                ->with('success', 'Pengajuan berhasil dikirim, menunggu verifikasi Tim Sarpras!');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withInput()
                ->with('error', 'Gagal mengirim pengajuan: ' . $e->getMessage());
        }
    }

    public function show(AssetRequest $assetRequest)
    {
        $this->authorize('view', $assetRequest);

        $assetRequest->load(
            'requester',
            'items.assetType',
            'items.unit',
            'items.rolledFrom.approver',
            'items.relatedAsset',
            'serviceItems.unit',
            'verifier',
            'approver',
            'relatedAsset'
        );

        $units = \App\Models\Unit::pluck('name', 'id');

        return view('requests.show', compact('assetRequest', 'units'));
    }

    public function verify(Request $request, AssetRequest $assetRequest)
    {
        if (! in_array(Auth::user()->level, ['PJ Pengadaan', 'Admin'])) {
            abort(403, 'Hanya PJ Pengadaan yang dapat memverifikasi pengajuan.');
        }
        if ($assetRequest->status !== 'Pending') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'verification_notes' => 'nullable|string',
        ]);

        $assetRequest->update([
            'status' => 'Diverifikasi',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ]);

        return redirect()->route('requests.index')
            ->with('success', 'Pengajuan diverifikasi, diteruskan ke Ketua STTI untuk persetujuan final!');
    }

    public function approve(AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'Rektor') {
            abort(403, 'Hanya Rektor yang dapat menyetujui pengajuan.');
        }

        if ($assetRequest->status !== 'Diverifikasi') {
            return redirect()->back()->with('error', 'Pengajuan harus diverifikasi Tim Sarpras terlebih dahulu.');
        }

        $assetRequest->update([
            'status' => 'Disetujui',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('requests.index')
            ->with('success', 'Pengajuan disetujui!');
    }

    public function disburseFund(Request $request, AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'Keuangan') {
            abort(403, 'Hanya Bagian Keuangan yang dapat mengonfirmasi pencairan dana.');
        }

        if ($assetRequest->status !== 'Disetujui') {
            return redirect()->back()->with('error', 'Pengajuan harus disetujui Rektor terlebih dahulu.');
        }

        $validated = $request->validate([
            'disbursement_notes' => 'nullable|string',
        ]);

        $assetRequest->update([
            'status' => 'Dana Cair',
            'disbursed_by' => Auth::id(),
            'disbursed_at' => now(),
            'disbursement_notes' => $validated['disbursement_notes'] ?? null,
        ]);

        return redirect()->route('requests.index')
            ->with('success', 'Dana dikonfirmasi cair, diteruskan ke PJ Pengadaan untuk proses pembelian!');
    }

    public function reject(Request $request, AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'Rektor' || $assetRequest->status !== 'Diverifikasi') {
            abort(403, 'Hanya Rektor yang dapat menolak pengajuan pada tahap ini.');
        }

        $validated = $request->validate(['approval_notes' => 'nullable|string']);

        $assetRequest->update([
            'status' => 'Ditolak',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        return redirect()->route('requests.index')->with('success', 'Pengajuan ditolak!');
    }

    public function showReceiveForm(AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'Sarpras' && Auth::user()->level !== 'Admin') {
            abort(403, 'Hanya Bagian Sarpras yang dapat melakukan registrasi aset.');
        }
        if ($assetRequest->status !== 'Dikonfirmasi') {
            return redirect()->route('requests.index')->with('error', 'Barang belum dikonfirmasi diterima secara fisik oleh PJ Pengadaan.');
        }

        $assetRequest->load([
            'items' => function ($query) {
                $query->receivable()->with('assetType');
            },
        ]);

        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level');
        $hasPhysical = $assetRequest->items->contains(
            fn($item) => $item->item_type === 'Fisik' && $item->sifat_barang !== 'Habis Pakai'
        );
        $units = $hasPhysical ? \App\Models\Unit::with('locations')->whereNotNull('category')->orderBy('category')->orderBy('name')->get() : collect();
        $categories = \App\Models\IntangibleAsset::CATEGORIES;
        $habisPakaiCategories = AssetRequestItem::HABIS_PAKAI_CATEGORIES;
        $reminderOptions = ['' => 'Tidak ada pengingat', '30' => '30 hari sebelum', '14' => '14 hari sebelum', '7' => '7 hari sebelum'];

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

        return view('requests.receive', compact('assetRequest', 'usersByLevel', 'units', 'hasPhysical', 'unitsForJs', 'categories', 'habisPakaiCategories', 'reminderOptions'));
    }

    public function receive(Request $request, AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'Sarpras' && Auth::user()->level !== 'Admin') {
            abort(403, 'Hanya Bagian Sarpras yang dapat melakukan registrasi aset.');
        }
        if ($assetRequest->status !== 'Dikonfirmasi') {
            return redirect()->route('requests.index')->with('error', 'Barang belum dikonfirmasi diterima secara fisik.');
        }

        $assetRequest->load([
            'items' => function ($query) {
                $query->receivable();
            },
        ]);
        $hasPhysical = $assetRequest->items->contains(
            fn($item) => $item->item_type === 'Fisik' && $item->sifat_barang !== 'Habis Pakai'
        );

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
            if ($item->isLightReceipt()) {
                $rules["received_quantities.{$item->id}"] = 'nullable|integer|min:0';
                $rules["receipt_notes.{$item->id}"] = 'nullable|string|max:1000';
                $rules["receipt_proof_files.{$item->id}"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
            } elseif ($item->item_type === 'Fisik') {
                $rules["brand.{$item->id}"] = 'required|string|max:255';
                $rules["prices.{$item->id}"] = 'required|numeric|min:0';
                for ($i = 0; $i < $item->quantity; $i++) {
                    $rules["images.{$item->id}.{$i}"] = 'required|image|mimes:jpg,jpeg,png,webp|max:2048';
                    $rules["serial_numbers.{$item->id}.{$i}"] = 'required|string|distinct|unique:assets,serial_number';
                    $rules["conditions.{$item->id}.{$i}"] = 'required|in:Baik,Rusak Ringan,Rusak Berat';
                    $rules["expired_dates.{$item->id}.{$i}"] = 'nullable|date';
                    $rules["unit_names.{$item->id}.{$i}"] = 'nullable|string|max:255';
                }
            } else {
                $rules["categories.{$item->id}"] = 'required|in:' . implode(',', array_keys(\App\Models\IntangibleAsset::CATEGORIES));
                $rules["vendors.{$item->id}"] = 'required|string|max:255';
                $rules["prices.{$item->id}"] = 'required|numeric|min:0';
                $rules["funding_sources.{$item->id}"] = 'nullable|string|max:255';
                $rules["contract_numbers.{$item->id}"] = 'nullable|string|max:255';
                $rules["license_types.{$item->id}"] = 'required|in:Berlangganan,Selamanya';
                $rules["expiry_dates.{$item->id}"] = 'required_if:license_types.' . $item->id . ',Berlangganan|nullable|date';
                $rules["reminder_days.{$item->id}"] = 'nullable|in:30,14,7';
                $rules["access_urls.{$item->id}"] = 'nullable|url|max:255';
                $rules["certificate_files.{$item->id}"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                for ($i = 0; $i < $item->quantity; $i++) {
                    $rules["product_keys.{$item->id}.{$i}"] = 'nullable|string|max:255';
                    $rules["assigned_emails.{$item->id}.{$i}"] = 'nullable|email|max:255';
                }
            }
        }

        $validated = $request->validate($rules);

        $locationName = $hasPhysical && ! empty($validated['location_id']) ? \App\Models\Location::find($validated['location_id'])->name : null;
        $locationString = trim(collect([$locationName, $validated['location_detail'] ?? null])->filter()->implode(' - '));

        DB::beginTransaction();
        try {
            $createdAssets = [];

            foreach ($assetRequest->items as $item) {
                if ($item->isLightReceipt()) {
                    $proofPath = $request->hasFile("receipt_proof_files.{$item->id}")
                        ? $request->file("receipt_proof_files.{$item->id}")->store('receipt-proofs', 'public')
                        : null;

                    $item->update([
                        'received_quantity' => $validated['received_quantities'][$item->id] ?? $item->quantity,
                        'receipt_notes' => $validated['receipt_notes'][$item->id] ?? null,
                        'receipt_proof_file' => $proofPath,
                    ]);
                } elseif ($item->item_type === 'Fisik') {
                    for ($i = 0; $i < $item->quantity; $i++) {
                        $unitImagePath = $request->file("images.{$item->id}.{$i}")->store('assets', 'public');

                        $asset = new Asset;
                        $asset->name = ! empty($validated['unit_names'][$item->id][$i] ?? null)
                            ? $validated['unit_names'][$item->id][$i]
                            : $item->item_name;
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

                        $createdAssets[] = $asset;

                        QrCode::create(['asset_id' => $asset->id, 'code_content' => $asset->qr_code, 'status' => 'Aktif']);
                    }
                } else {
                    $certificatePath = $request->hasFile("certificate_files.{$item->id}")
                        ? $request->file("certificate_files.{$item->id}")->store('intangible-certificates', 'public')
                        : null;

                    for ($i = 0; $i < $item->quantity; $i++) {
                        $intangible = \App\Models\IntangibleAsset::create([
                            'name' => $item->item_name,
                            'category' => $validated['categories'][$item->id],
                            'vendor' => $validated['vendors'][$item->id],
                            'price' => $validated['prices'][$item->id],
                            'activation_date' => $validated['purchase_date'],
                            'funding_source' => $validated['funding_sources'][$item->id] ?? null,
                            'contract_number' => $validated['contract_numbers'][$item->id] ?? null,
                            'license_type' => $validated['license_types'][$item->id],
                            'expiry_date' => $validated['expiry_dates'][$item->id] ?? null,
                            'reminder_days' => $validated['reminder_days'][$item->id] ?? null,
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
                        $createdAssets[] = $intangible;
                    }
                }
            }

            if ($assetRequest->alasan_pengajuan === 'Penggantian' && $assetRequest->related_asset_id) {
                $oldAsset = Asset::find($assetRequest->related_asset_id);

                if ($oldAsset) {
                    $newAsset = $createdAssets[0] ?? null;

                    if ($newAsset && $newAsset instanceof Asset) {
                        $oldAsset->update([
                            'status' => 'Diganti',
                            'replaces_asset_id' => $newAsset->id,
                            'updated_at' => now(),
                        ]);

                        session()->flash('replacement_notification', [
                            'old_asset_id' => $oldAsset->asset_id,
                            'old_asset_name' => $oldAsset->name,
                            'new_asset_id' => $newAsset->asset_id,
                            'new_asset_name' => $newAsset->name,
                        ]);
                    }
                }
            }

            $assetRequest->update(['status' => 'Diterima']);
            DB::commit();

            return redirect()->route('requests.index')
                ->with('success', 'Semua item berhasil diregistrasi ke inventaris!');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withInput()->with('error', 'Gagal memproses registrasi: ' . $e->getMessage());
        }
    }

    /**
     * Fase 4: form penyelesaian jasa — hanya untuk Sarpras/Admin,
     * status harus "Dana Cair" dan dokumen bertipe jasa.
     */
    public function showCompleteServiceForm(AssetRequest $assetRequest)
    {
        if (! in_array(Auth::user()->level, ['Sarpras', 'Admin'])) {
            abort(403, 'Hanya Bagian Sarpras yang dapat menyelesaikan jasa.');
        }
        if (! $assetRequest->isServiceRequest()) {
            return redirect()->route('requests.index')->with('error', 'Dokumen ini bukan pengajuan jasa.');
        }
        if ($assetRequest->status !== 'Dana Cair') {
            return redirect()->route('requests.index')->with('error', 'Dana untuk pengajuan ini belum dicairkan.');
        }

        $assetRequest->load('serviceItems.unit', 'requester');

        return view('requests.complete-service', compact('assetRequest'));
    }

    /**
     * Fase 4: simpan penyelesaian jasa — upload BAST, catat vendor & biaya real.
     * Kalau semua item sudah selesai, ubah status header → "Selesai".
     */
    public function completeService(Request $request, AssetRequest $assetRequest)
    {
        if (! in_array(Auth::user()->level, ['Sarpras', 'Admin'])) {
            abort(403, 'Hanya Bagian Sarpras yang dapat menyelesaikan jasa.');
        }
        if (! $assetRequest->isServiceRequest()) {
            return redirect()->route('requests.index')->with('error', 'Dokumen ini bukan pengajuan jasa.');
        }
        if ($assetRequest->status !== 'Dana Cair') {
            return redirect()->route('requests.index')->with('error', 'Dana untuk pengajuan ini belum dicairkan.');
        }

        $assetRequest->load('serviceItems');

        // Validasi per item: setiap item wajib diisi executor & minimal 1 BAST per dokumen
        $rules = [
            'completion_date' => 'required|date',
            'items' => 'required|array',
        ];
        foreach ($assetRequest->serviceItems as $item) {
            $rules["items.{$item->id}.executor"] = 'required|string|max:255';
            $rules["items.{$item->id}.actual_cost"] = 'nullable|numeric|min:0';
            $rules["items.{$item->id}.completion_notes"] = 'nullable|string|max:1000';
            $rules["items.{$item->id}.bast_file"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            foreach ($assetRequest->serviceItems as $item) {
                $bastPath = $item->bast_file; // default: simpan file lama
                if ($request->hasFile("items.{$item->id}.bast_file")) {
                    // Hapus file lama kalau ada
                    if ($item->bast_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->bast_file)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($item->bast_file);
                    }
                    $bastPath = $request->file("items.{$item->id}.bast_file")->store('bast-files', 'public');
                }

                $item->update([
                    'completed_at' => $validated['completion_date'],
                    'completion_notes' => $validated["items"][$item->id]['completion_notes'] ?? null,
                    'executor' => $validated["items"][$item->id]['executor'],
                    'actual_cost' => $validated["items"][$item->id]['actual_cost'] ?? null,
                    'bast_file' => $bastPath,
                ]);
            }

            // Semua item sudah diupdate → status header menjadi "Selesai"
            $assetRequest->update(['status' => 'Selesai']);

            DB::commit();

            return redirect()->route('requests.index')
                ->with('success', 'Jasa berhasil ditandai selesai! BAST telah tersimpan.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyelesaikan jasa: ' . $e->getMessage());
        }
    }

    public function destroy(AssetRequest $assetRequest)
    {
        $this->authorize('delete', $assetRequest);

        $assetRequest->delete();

        return redirect()->route('requests.index')->with('success', 'Pengajuan berhasil dihapus!');
    }

    public function confirmPhysical(Request $request, AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'PJ Pengadaan') {
            abort(403, 'Hanya PJ Pengadaan yang dapat mengonfirmasi penerimaan fisik barang.');
        }

        if ($assetRequest->status !== 'Dana Cair') {
            return redirect()->back()->with('error', 'Dana untuk pengajuan ini belum dicairkan Bagian Keuangan.');
        }

        $validated = $request->validate([
            'confirmation_notes' => 'nullable|string',
        ]);

        $assetRequest->update([
            'status' => 'Dikonfirmasi',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
            'confirmation_notes' => $validated['confirmation_notes'] ?? null,
        ]);

        return redirect()->route('requests.index')
            ->with('success', 'Penerimaan fisik dikonfirmasi, diteruskan ke Sarpras untuk registrasi!');
    }

    public function approval(AssetRequest $assetRequest)
    {
        if (Auth::user()->level !== 'Rektor') {
            abort(403, 'Hanya Rektor yang dapat mengakses halaman approval.');
        }

        if ($assetRequest->status !== 'Diverifikasi') {
            return redirect()->route('requests.index')->with('error', 'Pengajuan belum diverifikasi.');
        }

        $assetRequest->load(
            'items.assetType',
            'items.unit',
            'items.rolledFrom',
            'serviceItems.unit',
            'requester',
            'unit'
        );

        $units = \App\Models\Unit::pluck('name', 'id');

        return view('requests.approval', compact('assetRequest', 'units'));
    }

    public function approveItem(Request $request, AssetRequest $assetRequest, AssetRequestItem $item)
    {
        if (Auth::user()->level !== 'Rektor') {
            abort(403);
        }

        if ($item->asset_request_id !== $assetRequest->id) {
            abort(404);
        }

        $validated = $request->validate([
            'action' => 'required|in:approved,rejected,deferred',
            'approval_notes' => 'required_if:action,rejected,deferred|nullable|string',
        ]);

        $item->update([
            'approval_status' => $validated['action'],
            'approval_notes' => $validated['approval_notes'] ?? null,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->updateRequestStatus($assetRequest);

        $message = match ($validated['action']) {
            'approved' => 'Item disetujui.',
            'rejected' => 'Item ditolak.',
            'deferred' => 'Item ditangguhkan dan akan di-rollover ke bulan depan.',
        };

        return redirect()->back()->with('success', $message);
    }

    public function approveServiceItem(Request $request, AssetRequest $assetRequest, ServiceRequestItem $item)
    {
        if (Auth::user()->level !== 'Rektor') {
            abort(403);
        }

        if ($item->asset_request_id !== $assetRequest->id) {
            abort(404);
        }

        $validated = $request->validate([
            'action' => 'required|in:approved,rejected,deferred',
            'approval_notes' => 'required_if:action,rejected,deferred|nullable|string',
        ]);

        $item->update([
            'approval_status' => $validated['action'],
            'approval_notes' => $validated['approval_notes'] ?? null,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->updateRequestStatus($assetRequest);

        $message = match ($validated['action']) {
            'approved' => 'Item jasa disetujui.',
            'rejected' => 'Item jasa ditolak.',
            'deferred' => 'Item jasa ditangguhkan dan akan di-rollover ke bulan depan.',
        };

        return redirect()->back()->with('success', $message);
    }

    private function updateRequestStatus(AssetRequest $assetRequest)
    {
        // Load kedua relasi — karena satu dokumen bisa berisi aset, jasa, atau campuran
        // (secara ideal tidak campuran, tapi aman untuk handle keduanya).
        $assetRequest->load('items', 'serviceItems');

        // Gabung item aset + jasa untuk dihitung
        $allItems = $assetRequest->items->concat($assetRequest->serviceItems);

        $pending = $allItems->where('approval_status', 'pending')->count();
        $rejected = $allItems->where('approval_status', 'rejected')->count();
        $approved = $allItems->where('approval_status', 'approved')->count();

        if ($pending > 0) {
            return;
        }

        if ($rejected > 0 && $approved === 0) {
            $assetRequest->update([
                'status' => 'Ditolak',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => 'Semua item ditolak.',
            ]);
        } else {
            $assetRequest->update([
                'status' => 'Disetujui',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        }

        // Rollover deferred — pisahkan aset vs jasa
        $deferredAssetItems = $assetRequest->items->where('approval_status', 'deferred');
        if ($deferredAssetItems->count() > 0) {
            $this->rolloverDeferredItems($assetRequest, $deferredAssetItems);
        }

        $deferredServiceItems = $assetRequest->serviceItems->where('approval_status', 'deferred');
        if ($deferredServiceItems->count() > 0) {
            $this->rolloverDeferredServiceItems($assetRequest, $deferredServiceItems);
        }
    }

    private function rolloverDeferredItems(AssetRequest $assetRequest, $deferredItems)
    {
        $nextMonth = now()->addMonth();
        $targetMonth = $nextMonth->month;
        $targetYear = $nextMonth->year;

        $existingDraft = AssetRequest::where('unit_id', $assetRequest->unit_id)
            ->where('period_month', $targetMonth)
            ->where('period_year', $targetYear)
            ->whereIn('status', ['Pending', 'Diverifikasi'])
            ->first();

        if (! $existingDraft) {
            $existingDraft = AssetRequest::create([
                'requester_id' => $assetRequest->requester_id,
                'unit_id' => $assetRequest->unit_id,
                'period_month' => $targetMonth,
                'period_year' => $targetYear,
                'jenis_barang' => $assetRequest->jenis_barang,
                'kategori_barang' => $assetRequest->kategori_barang,
                'alasan_pengajuan' => $assetRequest->alasan_pengajuan,
                'priority' => $assetRequest->priority,
                'reason' => $assetRequest->reason . ' (Rollover dari bulan ' . $assetRequest->period_month . '/' . $assetRequest->period_year . ')',
                'status' => 'Pending',
            ]);
        }

        foreach ($deferredItems as $item) {
            $newItem = $existingDraft->items()->create([
                'item_type' => $item->item_type,
                'sifat_barang' => $item->sifat_barang,
                'asset_type_id' => $item->asset_type_id,
                'item_name' => $item->item_name,
                'specification' => $item->specification,
                'category' => $item->category,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'estimated_price_per_unit' => $item->estimated_price_per_unit,
                'approval_status' => 'pending',
                'rolled_from_item_id' => $item->id,
                'unit_id' => $item->unit_id,
                'priority' => $item->priority,
                'alasan_pengajuan' => $item->alasan_pengajuan,
                'reason' => $item->reason,
                'related_asset_id' => $item->related_asset_id,
            ]);

            RolloverLog::create([
                'original_item_id' => $item->id,
                'new_item_id' => $newItem->id,
                'source_month' => $assetRequest->period_month,
                'source_year' => $assetRequest->period_year,
                'target_month' => $targetMonth,
                'target_year' => $targetYear,
                'reason' => $item->approval_notes ?? 'Ditangguhkan oleh Rektor',
            ]);
        }

        session()->flash('rollover_notification', [
            'count' => $deferredItems->count(),
            'month' => $targetMonth,
            'year' => $targetYear,
        ]);
    }

    /**
     * Fase 3.5: rollover item jasa yang ditangguhkan Rektor ke draft bulan depan.
     * Paralel dengan rolloverDeferredItems() — bedanya item disimpan di tabel
     * service_request_items, bukan asset_request_items.
     *
     * Catatan: RolloverLog sengaja tidak dicatat di sini karena skema tabelnya
     * spesifik untuk item aset (FK ke asset_request_items). Jejak rollover jasa
     * tetap terlacak lewat kolom `rolled_from_item_id` di service_request_items.
     */
    private function rolloverDeferredServiceItems(AssetRequest $assetRequest, $deferredItems)
    {
        $nextMonth = now()->addMonth();
        $targetMonth = $nextMonth->month;
        $targetYear = $nextMonth->year;

        // Cari atau buat draft pengajuan bulan depan — sama tipe request (jasa)
        // dan sama service_category
        $existingDraft = AssetRequest::where('unit_id', $assetRequest->unit_id)
            ->where('period_month', $targetMonth)
            ->where('period_year', $targetYear)
            ->where('request_type', 'jasa')
            ->where('service_category', $assetRequest->service_category)
            ->whereIn('status', ['Pending', 'Diverifikasi'])
            ->first();

        if (! $existingDraft) {
            $existingDraft = AssetRequest::create([
                'requester_id' => $assetRequest->requester_id,
                'unit_id' => $assetRequest->unit_id,
                'period_month' => $targetMonth,
                'period_year' => $targetYear,
                'request_type' => 'jasa',
                'service_category' => $assetRequest->service_category,
                'alasan_pengajuan' => null,
                'priority' => $assetRequest->priority,
                'reason' => $assetRequest->reason . ' (Rollover dari bulan ' . $assetRequest->period_month . '/' . $assetRequest->period_year . ')',
                'status' => 'Pending',
            ]);
        }

        foreach ($deferredItems as $item) {
            $existingDraft->serviceItems()->create([
                'unit_id' => $item->unit_id,
                'priority' => $item->priority,
                'alasan_pengajuan' => null,
                'reason' => $item->reason,
                'service_category' => $item->service_category,
                'item_name' => $item->item_name,
                'specification' => $item->specification,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'estimated_price_per_unit' => $item->estimated_price_per_unit,
                'service_data' => $item->service_data,   // copy JSON
                'approval_status' => 'pending',
                'rolled_from_item_id' => $item->id,
            ]);
        }

        session()->flash('rollover_notification', [
            'count' => $deferredItems->count(),
            'month' => $targetMonth,
            'year' => $targetYear,
        ]);
    }
}
