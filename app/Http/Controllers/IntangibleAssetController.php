<?php

namespace App\Http\Controllers;

use App\Models\IntangibleAsset;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IntangibleAssetController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        $query = IntangibleAsset::with('unit');

        // Pencarian Teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('asset_code', 'ILIKE', "%{$search}%")
                    ->orWhere('vendor', 'ILIKE', "%{$search}%")
                    ->orWhere('contract_number', 'ILIKE', "%{$search}%");
            });
        }

        // Pencarian JSON
        if ($request->filled('json_key') && $request->filled('json_value')) {
            $jsonKey = $request->json_key;
            $jsonValue = $request->json_value;
            $driver = \DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                $query->whereRaw('category_data->>? ILIKE ?', [$jsonKey, "%{$jsonValue}%"]);
            } elseif ($driver === 'mysql') {
                $query->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(category_data, ?)) LIKE ?', ["$.{$jsonKey}", "%{$jsonValue}%"]);
            }
        }

        // Filter Kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Masa Berlaku
        if ($request->filled('expiry_filter')) {
            if ($request->expiry_filter === 'expired') {
                $query->whereNotNull('expiry_date')->where('expiry_date', '<', now()->startOfDay());
            } elseif ($request->expiry_filter === 'soon') {
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '>=', now()->startOfDay())
                    ->where('expiry_date', '<=', now()->addDays(30)->endOfDay());
            }
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $categories = IntangibleAsset::CATEGORIES;

        return view('intangible-assets.index', compact('items', 'categories'));
    }

    public function create()
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        $units = Unit::whereNotNull('category')->orderBy('category')->orderBy('name')->get();
        $fundingSources = IntangibleAsset::FUNDING_SOURCES;
        $usersByLevel = \App\Models\User::query()->orderBy('name')->get()->groupBy('level');
        $categories = IntangibleAsset::CATEGORIES;

        $availableRequests = \App\Models\AssetRequest::query()
            ->whereIn('status', ['Dikonfirmasi', 'Diterima', 'Selesai'])
            ->whereHas('items', fn ($q) => $q->where('item_type', 'non-fisik'))
            ->with(['items' => fn ($q) => $q->where('item_type', 'non-fisik')])
            ->withCount(['intangibleAssets as linked_count'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->filter(function ($req) {
                $nonFisikItemCount = $req->items->count();
                $linkedCount = $req->linked_count ?? 0;

                return $linkedCount < $nonFisikItemCount;
            });

        $schemas = [];
        foreach (array_keys($categories) as $cat) {
            $schemas[$cat] = IntangibleAsset::getSchema($cat);
        }

        return view('intangible-assets.create', compact('units', 'fundingSources', 'usersByLevel', 'categories', 'schemas', 'availableRequests'));
    }

    public function store(Request $request)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        $category = $request->input('category');
        $schema = IntangibleAsset::getSchema($category);
        $hideVendor = $schema['hide_vendor'] ?? false;

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => ['required', Rule::in(array_keys(IntangibleAsset::CATEGORIES))],
            'vendor' => $hideVendor ? 'nullable|string|max:255' : 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'acquisition_method' => 'nullable|string|max:100',
            'activation_date' => 'required|date',
            'funding_source' => ['nullable', Rule::in(IntangibleAsset::FUNDING_SOURCES)],
            'contract_number' => 'nullable|string|max:255',
            'license_type' => ['required', Rule::in(array_keys($schema['license_types'] ?? []))],
            'billing_model' => ['nullable', Rule::in(array_keys($schema['billing_models'] ?? []))],
            'expiry_date' => [
                'nullable', 'date', 'after:activation_date',
                Rule::requiredIf(fn () => in_array($request->input('license_type'), ['Subscription', 'Registered Right'])),
            ],
            'reminder_days' => 'nullable|in:30,14,7',
            'quota' => 'nullable|string|max:100',
            'unit_id' => [
                'required',
                Rule::exists('units', 'id'),
            ],
            'pic_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'access_url' => 'nullable|url|max:255',
            'status' => 'required|in:Aktif,Belum Aktif,Ditangguhkan,Dihentikan,Kadaluarsa',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120',
            'asset_request_id' => [
                'nullable',
                'integer',
                Rule::exists('asset_requests', 'id')->where(function ($q) {
                    $q->whereIn('status', ['Dikonfirmasi', 'Diterima', 'Selesai'])
                        ->whereNull('deleted_at');
                }),
            ],
        ];

        $attributes = [
            'name' => 'Nama Aset', 'category' => 'Kategori', 'vendor' => 'Vendor',
            'price' => 'Harga Perolehan', 'activation_date' => 'Tanggal Perolehan',
            'funding_source' => 'Sumber Pendanaan', 'license_type' => 'Jenis Lisensi',
            'expiry_date' => 'Tanggal Kedaluwarsa', 'unit_id' => 'Unit Pengelola',
            'pic_id' => 'PIC', 'status' => 'Status', 'certificate_file' => 'File Sertifikat',
            'asset_request_id' => 'Permintaan Pengadaan',
        ];

        foreach ($schema as $key => $field) {
            if (is_array($field) && isset($field['type'])) {
                $rule = ($field['required'] ?? false) ? 'required' : 'nullable';
                if ($field['type'] === 'email') {
                    $rule .= '|email';
                }
                if ($field['type'] === 'number') {
                    $rule .= '|numeric';
                }
                if ($field['type'] === 'date') {
                    $rule .= '|date';
                }
                if ($field['type'] === 'url') {
                    $rule .= '|url';
                }
                if ($field['type'] === 'select') {
                    $rule .= '|in:'.implode(',', array_keys($field['options']));
                }

                $rules["category_data.{$key}"] = $rule;
                $attributes["category_data.{$key}"] = $field['label'];
            }
        }

        $validator = Validator::make($request->all(), $rules, [], $attributes);
        $validated = $validator->validate();

        if (! empty($validated['asset_request_id'])) {
            $req = \App\Models\AssetRequest::with('items')->find($validated['asset_request_id']);
            if ($req) {
                $nonFisikItemCount = $req->items->where('item_type', 'Non-Fisik')->count(); // 🔥 Ubah ke 'Non-Fisik'
                $linkedCount = IntangibleAsset::where('asset_request_id', $req->id)->count();

                if ($linkedCount >= $nonFisikItemCount) {
                    return back()->withInput()->withErrors([
                        'asset_request_id' => "Permintaan '{$req->request_id}' sudah penuh ({$linkedCount}/{$nonFisikItemCount} item non-fisik sudah tercatat). Tidak dapat ditautkan lagi.",
                    ]);
                }
            }
        }

        if ($hideVendor) {
            $validated['vendor'] = '-'; // 🔥 Pakai strip agar tidak error NOT NULL
        }

        if (! in_array($validated['license_type'], ['Subscription', 'Registered Right'])) {
            $validated['expiry_date'] = null;
            $validated['reminder_days'] = null;
        }

        $validated['created_by'] = Auth::id();

        $categoryData = $request->input('category_data', []);
        $filteredData = [];
        foreach ($schema as $key => $field) {
            if (is_array($field) && isset($categoryData[$key])) {
                $filteredData[$key] = $categoryData[$key];
            }
        }
        $validated['category_data'] = $filteredData;

        $maxRetries = 3;
        $attempt = 0;
        $uploadedFilePath = null;

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                if ($request->hasFile('certificate_file')) {
                    $uploadedFilePath = $request->file('certificate_file')->store('intangible-certificates', 'local');
                    $validated['certificate_file'] = $uploadedFilePath;
                }

                $year = date('Y');
                $prefix = "NF/{$year}/";

                $lastAsset = IntangibleAsset::whereYear('created_at', $year)
                    ->withTrashed()
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNumber = $lastAsset ? (intval(substr($lastAsset->asset_code, -4)) + 1) : 1;

                // 🔥 FIX UTAMA: Jangan pakai create(), tapi set manual
                $asset = new IntangibleAsset($validated);
                $asset->asset_code = $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                $asset->save();

                DB::commit();

                $successMsg = 'Aset non-fisik berhasil ditambahkan!';
                if (! empty($validated['asset_request_id'])) {
                    $successMsg .= ' Aset berhasil ditautkan ke permintaan pengadaan.';
                }

                return redirect()->route('intangible-assets.index')->with('success', $successMsg);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                if ($uploadedFilePath && Storage::disk('local')->exists($uploadedFilePath)) {
                    Storage::disk('local')->delete($uploadedFilePath);
                }
                if ($e->getCode() == '23505' || $e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        return back()->withInput()->with('error', 'Terjadi bentrokan sistem saat membuat kode aset. Silakan coba simpan kembali.');
                    }
                    usleep(500000);
                } else {
                    return back()->withInput()->with('error', 'Gagal menambahkan aset: '.$e->getMessage());
                }
            } catch (\Exception $e) {
                DB::rollBack();
                if ($uploadedFilePath && Storage::disk('local')->exists($uploadedFilePath)) {
                    Storage::disk('local')->delete($uploadedFilePath);
                }

                return back()->withInput()->with('error', 'Gagal menambahkan aset: '.$e->getMessage());
            }
        }
    }

    public function edit(IntangibleAsset $intangibleAsset)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        $units = Unit::whereNotNull('category')->orderBy('category')->orderBy('name')->get();
        $fundingSources = IntangibleAsset::FUNDING_SOURCES;
        $usersByLevel = \App\Models\User::query()->orderBy('name')->get()->groupBy('level');
        $categories = IntangibleAsset::CATEGORIES;

        $availableRequests = \App\Models\AssetRequest::query()
            ->whereIn('status', ['Dikonfirmasi', 'Diterima', 'Selesai'])
            ->whereHas('items', fn ($q) => $q->where('item_type', 'non-fisik'))
            ->with(['items' => fn ($q) => $q->where('item_type', 'non-fisik')])
            ->withCount(['intangibleAssets as linked_count'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->filter(function ($req) use ($intangibleAsset) {
                if ($intangibleAsset->asset_request_id === $req->id) {
                    return true;
                }
                $nonFisikItemCount = $req->items->count();
                $linkedCount = $req->linked_count ?? 0;

                return $linkedCount < $nonFisikItemCount;
            });

        $schemas = [];
        foreach (array_keys($categories) as $cat) {
            $schemas[$cat] = IntangibleAsset::getSchema($cat);
        }

        return view('intangible-assets.edit', compact('intangibleAsset', 'units', 'fundingSources', 'usersByLevel', 'categories', 'schemas', 'availableRequests'));
    }

    public function update(Request $request, IntangibleAsset $intangibleAsset)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        $category = $request->input('category');
        $schema = IntangibleAsset::getSchema($category);
        $hideVendor = $schema['hide_vendor'] ?? false;

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => ['required', Rule::in(array_keys(IntangibleAsset::CATEGORIES))],
            'vendor' => $hideVendor ? 'nullable|string|max:255' : 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'acquisition_method' => 'nullable|string|max:100',
            'activation_date' => 'required|date',
            'funding_source' => ['nullable', Rule::in(IntangibleAsset::FUNDING_SOURCES)],
            'contract_number' => 'nullable|string|max:255',
            'license_type' => ['required', Rule::in(array_keys($schema['license_types'] ?? []))],
            'billing_model' => ['nullable', Rule::in(array_keys($schema['billing_models'] ?? []))],
            'expiry_date' => [
                'nullable', 'date', 'after:activation_date',
                Rule::requiredIf(fn () => in_array($request->input('license_type'), ['Subscription', 'Registered Right'])),
            ],
            'reminder_days' => 'nullable|in:30,14,7',
            'quota' => 'nullable|string|max:100',
            'unit_id' => [
                'required',
                Rule::exists('units', 'id'),
            ],
            'pic_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'access_url' => 'nullable|url|max:255',
            'status' => 'required|in:Aktif,Belum Aktif,Ditangguhkan,Dihentikan,Kadaluarsa',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120',
            'asset_request_id' => [
                'nullable',
                'integer',
                Rule::exists('asset_requests', 'id')->where(function ($q) {
                    $q->whereIn('status', ['Dikonfirmasi', 'Diterima', 'Selesai'])
                        ->whereNull('deleted_at');
                }),
            ],
        ];

        $attributes = [
            'name' => 'Nama Aset', 'category' => 'Kategori', 'vendor' => 'Vendor',
            'price' => 'Harga Perolehan', 'activation_date' => 'Tanggal Perolehan',
            'funding_source' => 'Sumber Pendanaan', 'license_type' => 'Jenis Lisensi',
            'expiry_date' => 'Tanggal Kedaluwarsa', 'unit_id' => 'Unit Pengelola',
            'pic_id' => 'PIC', 'status' => 'Status', 'certificate_file' => 'File Sertifikat',
            'asset_request_id' => 'Permintaan Pengadaan',
        ];

        foreach ($schema as $key => $field) {
            if (is_array($field) && isset($field['type'])) {
                $rule = ($field['required'] ?? false) ? 'required' : 'nullable';
                if ($field['type'] === 'email') {
                    $rule .= '|email';
                }
                if ($field['type'] === 'number') {
                    $rule .= '|numeric';
                }
                if ($field['type'] === 'date') {
                    $rule .= '|date';
                }
                if ($field['type'] === 'url') {
                    $rule .= '|url';
                }
                if ($field['type'] === 'select') {
                    $rule .= '|in:'.implode(',', array_keys($field['options']));
                }

                $rules["category_data.{$key}"] = $rule;
                $attributes["category_data.{$key}"] = $field['label'];
            }
        }

        $validator = Validator::make($request->all(), $rules, [], $attributes);
        $validated = $validator->validate();

        if (! empty($validated['asset_request_id'])) {
            $req = \App\Models\AssetRequest::with('items')->find($validated['asset_request_id']);
            if ($req) {
                $nonFisikItemCount = $req->items->where('item_type', 'non-fisik')->count();
                $linkedCount = IntangibleAsset::where('asset_request_id', $req->id)
                    ->where('id', '!=', $intangibleAsset->id)
                    ->count();

                if ($linkedCount >= $nonFisikItemCount) {
                    return back()->withInput()->withErrors([
                        'asset_request_id' => "Permintaan '{$req->request_id}' sudah penuh ({$linkedCount}/{$nonFisikItemCount} item non-fisik tercatat). Tidak dapat ditautkan lagi.",
                    ]);
                }
            }
        }

        if ($hideVendor) {
            $validated['vendor'] = '-'; // 🔥 Pakai strip agar tidak error NOT NULL
        }

        if (! in_array($validated['license_type'], ['Subscription', 'Registered Right'])) {
            $validated['expiry_date'] = null;
            $validated['reminder_days'] = null;
        }

        $categoryData = $request->input('category_data', []);
        $existingData = $intangibleAsset->category_data ?? [];
        $filteredData = [];

        foreach ($schema as $key => $field) {
            if (is_array($field) && isset($field['in_batch']) && $field['in_batch']) {
                if (isset($categoryData[$key]) && $categoryData[$key] !== '') {
                    $filteredData[$key] = $categoryData[$key];
                } elseif ($key === 'product_key' && isset($existingData[$key])) {
                    $filteredData[$key] = $existingData[$key];
                } elseif (isset($categoryData[$key])) {
                    $filteredData[$key] = $categoryData[$key];
                }
            }
        }
        $validated['category_data'] = $filteredData;

        $oldFilePath = $intangibleAsset->certificate_file;
        $newFilePath = null;

        if ($request->hasFile('certificate_file')) {
            $newFilePath = $request->file('certificate_file')->store('intangible-certificates', 'local');
            $validated['certificate_file'] = $newFilePath;
        }

        try {
            unset($validated['asset_code']);
            $intangibleAsset->update($validated);

            if ($newFilePath && $oldFilePath && Storage::disk('local')->exists($oldFilePath)) {
                Storage::disk('local')->delete($oldFilePath);
            }
        } catch (\Exception $e) {
            if ($newFilePath && Storage::disk('local')->exists($newFilePath)) {
                Storage::disk('local')->delete($newFilePath);
            }

            return back()->withInput()->with('error', 'Gagal update aset: '.$e->getMessage());
        }

        return redirect()->route('intangible-assets.show', $intangibleAsset)
            ->with('success', 'Aset non-fisik berhasil diperbarui!');
    }

    public function show(IntangibleAsset $intangibleAsset)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        return view('intangible-assets.show', compact('intangibleAsset'));
    }

    public function destroy(IntangibleAsset $intangibleAsset)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        if ($intangibleAsset->certificate_file && Storage::disk('local')->exists($intangibleAsset->certificate_file)) {
            Storage::disk('local')->delete($intangibleAsset->certificate_file);
        }

        $intangibleAsset->delete();

        return redirect()->route('intangible-assets.index')->with('success', 'Aset non-fisik berhasil dihapus!');
    }

    public function downloadCertificate(IntangibleAsset $intangibleAsset)
    {
        abort_unless(in_array(Auth::user()->level, ['Sarpras', 'Admin']), 403);

        if (! $intangibleAsset->certificate_file || ! Storage::disk('local')->exists($intangibleAsset->certificate_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->download($intangibleAsset->certificate_file);
    }
}
