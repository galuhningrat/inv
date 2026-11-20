<?php

namespace Tests\Feature;

use App\Models\AssetCategory;
use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\AssetType;
use App\Models\ProcurementOffer;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProcurementOfferTest extends TestCase
{
    use DatabaseTransactions;

    protected User $pjPengadaan;
    protected User $rektor;
    protected User $mahasiswa;
    protected AssetRequest $assetRequest;
    protected AssetRequestItem $item1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat Unit
        $unit = Unit::create(['name' => 'Lab Test', 'category' => 'Laboratorium', 'asset_id_code' => 'UM']);

        // 2. Buat User
        $this->pjPengadaan = User::factory()->create(['level' => 'PJ Pengadaan', 'unit_id' => $unit->id]);
        $this->rektor      = User::factory()->create(['level' => 'Rektor', 'unit_id' => $unit->id]);
        $this->mahasiswa   = User::factory()->create(['level' => 'Mahasiswa', 'unit_id' => $unit->id]);

        // 3. Buat Asset Request (Status Disetujui)
        $this->assetRequest = AssetRequest::create([
            'requester_id' => $this->mahasiswa->id,
            'unit_id' => $unit->id,
            'request_type' => 'aset',
            'status' => 'Disetujui', // Syarat bisa input penawaran
            'period_month' => now()->month,
            'period_year' => now()->year,
            'alasan_pengajuan' => 'Pengadaan Baru',
            'priority' => 'Normal',
            'reason' => 'Butuh Laptop',
        ]);

        // 4. Buat Item Request
        $this->item1 = AssetRequestItem::forceCreate([
            'asset_request_id' => $this->assetRequest->id,
            'unit_id' => $unit->id,
            'item_type' => 'Fisik',
            'sifat_barang' => 'Tidak Habis Pakai',
            'item_name' => 'Laptop Asus',
            'quantity' => 2, // Qty 2
            'unit' => 'Unit',
            'estimated_price_per_unit' => 10000000,
            'approval_status' => 'approved',
            'priority' => 'Normal',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'reason' => 'Untuk praktikum',
        ]);
    }

    // ============================================================
    // GROUP 1 — HAK AKSES (RBAC)
    // ============================================================

    public function test_mahasiswa_cannot_access_procurement_form(): void
    {
        $response = $this->actingAs($this->mahasiswa)->get(route('procurement.create', $this->assetRequest));
        $response->assertStatus(403);
    }

    public function test_pj_pengadaan_can_access_procurement_form(): void
    {
        $response = $this->actingAs($this->pjPengadaan)->get(route('procurement.create', $this->assetRequest));
        $response->assertStatus(200);
    }

    // ============================================================
    // GROUP 2 — SIMPAN PENAWARAN & KALKULASI
    // ============================================================

    public function test_pj_pengadaan_can_store_offer_and_calculate_total(): void
    {
        // Harga 5.000.000/unit x Qty 2 = 10.000.000
        // Diskon 500.000, Pajak 100.000
        // Subtotal item = (5.000.000 * 2) - 500.000 + 100.000 = 9.600.000
        // Tambahan tax_amount 200.000, shipping 50.000
        // Total = 9.600.000 + 200.000 + 50.000 = 9.850.000

        $payload = [
            'vendor_name' => 'PT. Sumber Makmur',
            'offer_date' => now()->format('Y-m-d'),
            'tax_amount' => 200000,
            'shipping_cost' => 50000,
            'items' => [
                $this->item1->id => [
                    'unit_price' => 5000000,
                    'discount' => 500000,
                    'tax' => 100000,
                ]
            ]
        ];

        $response = $this->actingAs($this->pjPengadaan)->post(route('procurement.store', $this->assetRequest), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('procurement.index', $this->assetRequest));

        $offer = ProcurementOffer::latest('id')->first();

        $this->assertEquals('PT. Sumber Makmur', $offer->vendor_name);
        $this->assertEquals(9600000, (int)$offer->subtotal); // Subtotal item
        $this->assertEquals(9850000, (int)$offer->total_price); // Grand Total

        // Cek item penawaran tersimpan
        $this->assertDatabaseHas('procurement_offer_items', [
            'procurement_offer_id' => $offer->id,
            'asset_request_item_id' => $this->item1->id,
            'unit_price' => 5000000,
        ]);
    }

    // ============================================================
    // GROUP 3 — PENETAPAN VENDOR (SELECTION)
    // ============================================================

    private function createDummyOffer(string $vendorName, int $totalPrice): ProcurementOffer
    {
        return ProcurementOffer::create([
            'asset_request_id' => $this->assetRequest->id,
            'vendor_name' => $vendorName,
            'offer_date' => now(),
            'subtotal' => $totalPrice,
            'total_price' => $totalPrice,
            'status' => 'Diajukan',
            'submitted_by' => $this->pjPengadaan->id,
        ]);
    }

    public function test_rektor_can_select_vendor_with_reason(): void
    {
        $offerA = $this->createDummyOffer('Vendor A (Mahal)', 10000000);
        $offerB = $this->createDummyOffer('Vendor B (Murah)', 8000000);

        // Pilih Vendor B
        $response = $this->actingAs($this->rektor)->post(route('procurement.select', $offerB), [
            'selection_reason' => 'Harga termurah dan sesuai spesifikasi teknis.'
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('procurement.index', $this->assetRequest));

        // Cek Vendor B dipilih
        $offerB->refresh();
        $this->assertTrue($offerB->is_selected);
        $this->assertEquals('Dipilih', $offerB->status);
        $this->assertEquals($this->rektor->id, $offerB->selected_by);

        // Cek Vendor A otomatis ditolak
        $offerA->refresh();
        $this->assertFalse($offerA->is_selected);
        $this->assertEquals('Ditolak', $offerA->status);
    }

    public function test_cannot_select_vendor_without_reason(): void
    {
        $offerA = $this->createDummyOffer('Vendor A', 10000000);

        $response = $this->actingAs($this->rektor)->post(route('procurement.select', $offerA), [
            'selection_reason' => '' // Kosong
        ]);

        $response->assertSessionHasErrors('selection_reason');
        $this->assertFalse($offerA->fresh()->is_selected);
    }

    public function test_pj_pengadaan_cannot_select_vendor(): void
    {
        $offerA = $this->createDummyOffer('Vendor A', 10000000);

        // PJ Pengadaan coba menetapkan vendor
        $response = $this->actingAs($this->pjPengadaan)->post(route('procurement.select', $offerA), [
            'selection_reason' => 'Coba-coba pilih.'
        ]);

        $response->assertStatus(403); // Ditolak karena bukan Rektor/Sarpras/Admin
        $this->assertFalse($offerA->fresh()->is_selected);
    }
}
