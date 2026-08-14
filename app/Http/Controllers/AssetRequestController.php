<?php

namespace App\Http\Controllers;

use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\AssetType;
use App\Models\Asset;
use App\Models\QrCode;
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
                'jenis_barang' => $validated['jenis_barang'],
                'kategori_barang' => $validated['kategori_barang'],
                'alasan_pengajuan' => $validated['alasan_pengajuan'],
                'related_asset_id' => $validated['related_asset_id'] ?? null,
                'priority' => $validated['priority'],
                'reason' => $validated['reason'],
                'status' => 'Pending',
            ]);

            foreach ($validated['items'] as $item) {
                $assetRequest->items()->create($item);
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

        $rules = [
            'brand' => 'required|array',
            'purchase_date' => 'required|date',
            'location' => 'required|string|max:255',
            'penanggung_jawab_id' => 'nullable|exists:users,id',
        ];

        foreach ($assetRequest->items as $item) {
            $rules["brand.{$item->id}"] = 'required|string|max:255';
            $rules["prices.{$item->id}"] = 'required|numeric|min:0';

            for ($i = 0; $i < $item->quantity; $i++) {
                $rules["unit_names.{$item->id}.{$i}"] = 'nullable|string|max:255';
                $rules["images.{$item->id}.{$i}"] = 'required|image|mimes:jpg,jpeg,png,webp|max:2048'; // ✅ per-unit sekarang
                $rules["serial_numbers.{$item->id}.{$i}"] = 'required|string|distinct|unique:assets,serial_number';
                $rules["conditions.{$item->id}.{$i}"] = 'required|in:Baik,Rusak Ringan,Rusak Berat';
                $rules["expired_dates.{$item->id}.{$i}"] = 'nullable|date'; // ✅ baru
            }
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            foreach ($assetRequest->items as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $imagePath = $request->file("images.{$item->id}.{$i}")->store('assets', 'public'); // ✅ per-unit

                    $asset = new Asset();
                    $asset->name = !empty($validated['unit_names'][$item->id][$i])
                        ? $validated['unit_names'][$item->id][$i] // ✅ override nama per-unit kalau diisi
                        : $item->item_name; // fallback ke nama generik item
                    $asset->asset_type_id = $item->asset_type_id;
                    $asset->brand = $validated['brand'][$item->id];
                    $asset->serial_number = $validated['serial_numbers'][$item->id][$i];
                    $asset->price = $validated['prices'][$item->id];
                    $asset->purchase_date = $validated['purchase_date'];
                    $asset->expired_at = $validated['expired_dates'][$item->id][$i] ?? null; // ✅ baru
                    $asset->location = $validated['location'];
                    $asset->condition = $validated['conditions'][$item->id][$i];
                    $asset->status = 'Tersedia';
                    $asset->image = $imagePath;
                    $asset->penanggung_jawab_id = $validated['penanggung_jawab_id'] ?? $assetRequest->requester_id;
                    $asset->asset_request_id = $assetRequest->id;
                    $asset->unit_id = $assetRequest->unit_id; // ✅ ikut unit dari pengajuannya
                    $asset->qr_code = QrCode::generateCodeContent($item->assetType->code);
                    $asset->save();

                    QrCode::create([
                        'asset_id' => $asset->id,
                        'code_content' => $asset->qr_code,
                        'status' => 'Aktif',
                    ]);
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
}
