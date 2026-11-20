<?php

namespace App\Http\Controllers;

use App\Models\AssetRequest;
use App\Models\ProcurementOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcurementOfferController extends Controller
{
    // Halaman Perbandingan & Daftar Penawaran
    public function index(AssetRequest $assetRequest)
    {
        // Hanya PJ Pengadaan, Sarpras, Rektor, Admin yang bisa lihat
        if (!in_array(Auth::user()->level, ['PJ Pengadaan', 'Sarpras', 'Admin', 'Rektor'])) {
            abort(403, 'Anda tidak berhak melihat penawaran ini.');
        }

        $assetRequest->load(['items.assetType', 'procurementOffers.items.requestItem', 'procurementOffers.submitter', 'procurementOffers.selector']);

        $offers = $assetRequest->procurementOffers()->orderBy('total_price', 'asc')->get();

        return view('procurement.index', compact('assetRequest', 'offers'));
    }

    // Form Input Penawaran
    public function create(AssetRequest $assetRequest)
    {
        if (!in_array(Auth::user()->level, ['PJ Pengadaan', 'Admin'])) {
            abort(403, 'Hanya PJ Pengadaan yang dapat menambah penawaran.');
        }

        // Hanya bisa input jika pengajuan sudah disetujui/dana cair
        if (!in_array($assetRequest->status, ['Disetujui', 'Dana Cair'])) {
            return redirect()->back()->with('error', 'Penawaran hanya bisa diinput setelah pengajuan disetujui.');
        }

        // Hanya item yang disetujui (approved) yang bisa ditawar
        $assetRequest->load(['items' => function ($q) {
            $q->where('approval_status', 'approved');
        }]);

        return view('procurement.create', compact('assetRequest'));
    }

    // Simpan Penawaran
    public function store(Request $request, AssetRequest $assetRequest)
    {
        if (!in_array(Auth::user()->level, ['PJ Pengadaan', 'Admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'vendor_email' => 'nullable|email|max:255',
            'vendor_phone' => 'nullable|string|max:50',
            'offer_number' => 'nullable|string|max:255',
            'offer_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:offer_date',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'quotation_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'required|array',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $filePath = $request->hasFile('quotation_file')
                ? $request->file('quotation_file')->store('procurement-quotations', 'local') // Ganti 'public' ke 'local'
                : null;

            $subtotal = 0;
            $offerItems = [];

            // Hitung subtotal per item
            foreach ($assetRequest->items as $reqItem) {
                if (isset($validated['items'][$reqItem->id])) {
                    $itemData = $validated['items'][$reqItem->id];
                    $qty = $reqItem->quantity;
                    $unitPrice = $itemData['unit_price'];
                    $discount = $itemData['discount'] ?? 0;
                    $tax = $itemData['tax'] ?? 0;

                    $itemSubtotal = ($unitPrice * $qty) - $discount + $tax;
                    $subtotal += $itemSubtotal;

                    $offerItems[] = [
                        'asset_request_item_id' => $reqItem->id,
                        'unit_price' => $unitPrice,
                        'discount' => $discount,
                        'tax' => $tax,
                        'subtotal' => $itemSubtotal,
                        'notes' => $itemData['notes'] ?? null,
                    ];
                }
            }

            $totalPrice = $subtotal + ($validated['tax_amount'] ?? 0) + ($validated['shipping_cost'] ?? 0);

            $offer = ProcurementOffer::create([
                'asset_request_id' => $assetRequest->id,
                'vendor_name' => $validated['vendor_name'],
                'vendor_contact' => $validated['vendor_contact'] ?? null,
                'vendor_email' => $validated['vendor_email'] ?? null,
                'vendor_phone' => $validated['vendor_phone'] ?? null,
                'offer_number' => $validated['offer_number'] ?? null,
                'offer_date' => $validated['offer_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total_price' => $totalPrice,
                'notes' => $validated['notes'] ?? null,
                'quotation_file' => $filePath,
                'status' => 'Diajukan',
                'submitted_by' => Auth::id(),
            ]);

            $offer->items()->createMany($offerItems);

            DB::commit();
            return redirect()->route('procurement.index', $assetRequest)->with('success', 'Penawaran vendor berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan penawaran: ' . $e->getMessage());
        }
    }

    // Penetapan Vendor
    public function selectVendor(Request $request, ProcurementOffer $offer)
    {
        // Hanya Rektor atau Pejabat berwenang (bisa disesuaikan)
        if (!in_array(Auth::user()->level, ['Rektor', 'Sarpras', 'Admin'])) {
            abort(403, 'Anda tidak berhak menetapkan vendor.');
        }

        $validated = $request->validate([
            'selection_reason' => 'required|string|min:10', // Wajib alasan
        ]);

        DB::beginTransaction();
        try {
            // Reset status terpilih untuk penawaran lain di request yang sama
            ProcurementOffer::where('asset_request_id', $offer->asset_request_id)
                ->update(['is_selected' => false, 'status' => 'Ditolak']);

            // Set terpilih
            $offer->update([
                'is_selected' => true,
                'status' => 'Dipilih',
                'selection_reason' => $validated['selection_reason'],
                'selected_by' => Auth::id(),
                'selected_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('procurement.index', $offer->asset_request_id)->with('success', 'Vendor berhasil ditetapkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menetapkan vendor: ' . $e->getMessage());
        }
    }

    public function downloadQuotation(ProcurementOffer $offer)
    {
        // Cek RBAC (hanya yang berhak)
        if (!in_array(Auth::user()->level, ['PJ Pengadaan', 'Sarpras', 'Admin', 'Rektor'])) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($offer->quotation_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->download($offer->quotation_file);
    }
}
