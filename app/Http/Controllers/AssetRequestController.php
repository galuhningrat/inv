<?php

namespace App\Http\Controllers;

use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\AssetType;
use App\Models\Asset;
use App\Models\QrCode;
use App\Models\RolloverLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Location;

class AssetRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetRequest::with('requester', 'items', 'verifier', 'approver');

        $user = auth()->user();
        if (in_array($user->level, ['Kaprodi', 'Kalab'])) {
            $query->where('unit_id', $user->unit_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10);

        return view('requests.index', compact('requests'));
    }

    public function create()
    {
        $assetTypes = AssetType::all();
        $priorities = ['Normal', 'Mendesak', 'Sangat Mendesak'];
        $jenisBarangOptions = ['Habis Pakai', 'Tidak Habis Pakai', 'Jasa'];
        $kategoriBarangOptions = ['ATK', 'Konsumsi', 'Alat', 'Furniture', 'Lainnya'];
        $alasanOptions = ['Pengadaan Baru', 'Penggantian', 'Pengisian Kembali'];
        $assets = Asset::orderBy('name')->get(); // untuk dropdown "aset terkait" kasus Penggantian

        return view('requests.create', compact(
            'assetTypes',
            'priorities',
            'jenisBarangOptions',
            'kategoriBarangOptions',
            'alasanOptions',
            'assets'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', AssetRequest::class);

        // Cek apakah sudah ada pengajuan bulan ini untuk unit ini
        if (AssetRequest::hasRequestThisMonth(Auth::user()->unit_id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anda telah melakukan pengajuan untuk bulan ini. Akses dibuka kembali bulan depan.');
        }

        $validated = $request->validate([
            'jenis_barang' => 'required|in:Habis Pakai,Tidak Habis Pakai,Jasa',
            'kategori_barang' => 'required|in:ATK,Konsumsi,Alat,Furniture,Lainnya',
            'alasan_pengajuan' => 'required|in:Pengadaan Baru,Penggantian,Pengisian Kembali',
            'related_asset_id' => 'required_if:alasan_pengajuan,Penggantian|nullable|exists:assets,id',
            'priority' => 'required|in:Normal,Mendesak,Sangat Mendesak',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:Fisik,Non-Fisik',
            'items.*.asset_type_id' => 'required_if:items.*.item_type,Fisik|nullable|exists:asset_types,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.specification' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_price_per_unit' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $assetRequest = AssetRequest::create([
                'requester_id' => Auth::id(),
                'unit_id' => Auth::user()->unit_id,
                'period_month' => now()->month,
                'period_year' => now()->year,
                'jenis_barang' => $validated['jenis_barang'],
                'kategori_barang' => $validated['kategori_barang'],
                'alasan_pengajuan' => $validated['alasan_pengajuan'],
                'related_asset_id' => $validated['related_asset_id'] ?? null,
                'priority' => $validated['priority'],
                'reason' => $validated['reason'],
                'status' => 'Pending',
            ]);

            foreach ($validated['items'] as $item) {
                $assetRequest->items()->create([
                    'item_type' => $item['item_type'],
                    'asset_type_id' => $item['asset_type_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'specification' => $item['specification'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_price_per_unit' => $item['estimated_price_per_unit'] ?? null,
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

        $assetRequest->load('requester', 'items.assetType', 'verifier', 'approver', 'relatedAsset');
        return view('requests.show', compact('assetRequest'));
    }

    // Tahap 1 - Verifikasi oleh Tim Sarpras
    public function verify(Request $request, AssetRequest $assetRequest)
    {
        if (!in_array(Auth::user()->level, ['PJ Pengadaan', 'Admin'])) {
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

    // Tahap 2 - Approval final oleh Rektor (hanya dari status Diverifikasi)
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

    // Bisa dipakai untuk menolak di tahap Pending (oleh Sarpras) ATAU Diverifikasi (oleh Rektor)
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

        $assetRequest->load('items.assetType');

        $usersByLevel = \App\Models\User::orderBy('name')->get()->groupBy('level');
        $hasPhysical = $assetRequest->items->contains('item_type', 'Fisik');
        $units = $hasPhysical ? \App\Models\Unit::with('locations')->whereNotNull('category')->orderBy('category')->orderBy('name')->get() : collect();

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

        return view('requests.receive', compact('assetRequest', 'usersByLevel', 'units', 'hasPhysical', 'unitsForJs'));
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
            } else {
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
            $createdAssets = [];

            foreach ($assetRequest->items as $item) {
                if ($item->item_type === 'Fisik') {
                    for ($i = 0; $i < $item->quantity; $i++) {
                        $unitImagePath = $request->file("images.{$item->id}.{$i}")->store('assets', 'public');

                        $asset = new Asset();
                        $asset->name = !empty($validated['unit_names'][$item->id][$i] ?? null)
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
                } else { // Non-Fisik
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
                    // Cari aset baru pertama yang dibuat (untuk penggantian)
                    $newAsset = $createdAssets[0] ?? null;

                    if ($newAsset && $newAsset instanceof Asset) {
                        // Update aset lama menjadi "Diganti"
                        $oldAsset->update([
                            'status' => 'Diganti',
                            'replaces_asset_id' => $newAsset->id,
                            'updated_at' => now(),
                        ]);

                        // Opsional: simpan log di session
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

        if ($assetRequest->status !== 'Dana Cair') { // diubah dari 'Disetujui'
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

        $assetRequest->load('items.assetType', 'requester', 'unit');

        return view('requests.approval', compact('assetRequest'));
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

        // Jika semua item sudah diproses, update status header
        $this->updateRequestStatus($assetRequest);

        $message = match ($validated['action']) {
            'approved' => 'Item disetujui.',
            'rejected' => 'Item ditolak.',
            'deferred' => 'Item ditangguhkan dan akan di-rollover ke bulan depan.',
        };

        return redirect()->back()->with('success', $message);
    }

    private function updateRequestStatus(AssetRequest $assetRequest)
    {
        $assetRequest->load('items');

        $pending = $assetRequest->items->where('approval_status', 'pending')->count();
        $rejected = $assetRequest->items->where('approval_status', 'rejected')->count();
        $approved = $assetRequest->items->where('approval_status', 'approved')->count();

        if ($pending > 0) {
            // Masih ada yang pending, status tetap Diverifikasi
            return;
        }

        if ($rejected > 0 && $approved === 0) {
            // Semua ditolak
            $assetRequest->update([
                'status' => 'Ditolak',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => 'Semua item ditolak.',
            ]);
        } else {
            // Ada yang disetujui
            $assetRequest->update([
                'status' => 'Disetujui',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        }

        // Jika ada item ditangguhkan, jalankan rollover
        $deferredItems = $assetRequest->items->where('approval_status', 'deferred');
        if ($deferredItems->count() > 0) {
            $this->rolloverDeferredItems($assetRequest, $deferredItems);
        }
    }

    private function rolloverDeferredItems(AssetRequest $assetRequest, $deferredItems)
    {
        $nextMonth = now()->addMonth();
        $targetMonth = $nextMonth->month;
        $targetYear = $nextMonth->year;

        // Buat draft pengajuan baru untuk bulan depan (jika belum ada)
        $existingDraft = AssetRequest::where('unit_id', $assetRequest->unit_id)
            ->where('period_month', $targetMonth)
            ->where('period_year', $targetYear)
            ->whereIn('status', ['Pending', 'Diverifikasi'])
            ->first();

        if (!$existingDraft) {
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
            // Duplikat item ke draft baru
            $newItem = $existingDraft->items()->create([
                'item_type' => $item->item_type,
                'asset_type_id' => $item->asset_type_id,
                'item_name' => $item->item_name,
                'specification' => $item->specification,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'estimated_price_per_unit' => $item->estimated_price_per_unit,
                'approval_status' => 'pending',
                'approval_notes' => 'Rollover dari item #' . $item->id . ' (Alasan: ' . ($item->approval_notes ?? 'Ditangguhkan') . ')',
            ]);

            // Catat di rollover_logs
            RolloverLog::create([
                'original_item_id' => $item->id,
                'new_item_id' => $newItem->id,
                'source_month' => $assetRequest->period_month,
                'source_year' => $assetRequest->period_year,
                'target_month' => $targetMonth,
                'target_year' => $targetYear,
                'reason' => $item->approval_notes ?? 'Ditangguhkan oleh Rektor',
            ]);

            // Tandai item asal sebagai sudah di-rollover
            $item->update([
                'rolled_from_item_id' => $item->id, // menandai bahwa item ini sudah di-rollover
            ]);
        }

        // Kirim notifikasi ke user (bisa pakai event atau session)
        session()->flash('rollover_notification', [
            'count' => $deferredItems->count(),
            'month' => $targetMonth,
            'year' => $targetYear,
        ]);
    }


}
