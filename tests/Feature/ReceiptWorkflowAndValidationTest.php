<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetBatch;
use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\IntangibleAsset;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReceiptWorkflowAndValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $sarpras;
    protected User $mahasiswa;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');

        $this->sarpras = User::factory()->create(['level' => 'Sarpras']);
        $this->mahasiswa = User::factory()->create(['level' => 'Mahasiswa']);
        $this->unit = Unit::factory()->create(['name' => 'Lab Test', 'category' => 'Laboratorium']);
    }

    private function createDummyRequest(string $status = 'Dikonfirmasi', string $itemType = 'Fisik', string $sifat = 'Tidak Habis Pakai'): AssetRequest
    {
        $request = AssetRequest::create([
            'requester_id' => $this->sarpras->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'aset',
            'status' => $status,
            'alasan_pengajuan' => 'Pengadaan Baru',
            'priority' => 'Normal',
            'reason' => 'Test',
            'period_month' => now()->month,
            'period_year' => now()->year,
        ]);

        AssetRequestItem::create([
            'asset_request_id' => $request->id,
            'unit_id' => $this->unit->id,
            'item_type' => $itemType,
            'sifat_barang' => $sifat,
            'category' => 'ATK',
            'item_name' => 'Barang Test',
            'quantity' => 2,
            'unit' => 'Unit',
            'estimated_price_per_unit' => 1000,
            'approval_status' => 'approved',
            'priority' => 'Normal',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'reason' => 'Test',
        ]);

        return $request;
    }

    // ============================================================
    // 17.4 PENGUJIAN WORKFLOW & OTORISASI
    // ============================================================

    public function test_unauthorized_user_cannot_access_receive_form(): void
    {
        $request = $this->createDummyRequest();

        // Mahasiswa mencoba akses
        $response = $this->actingAs($this->mahasiswa)->get(route('requests.receive.form', $request));
        $response->assertStatus(403);
    }

    public function test_cannot_receive_if_status_is_pending(): void
    {
        // Status masih Pending (belum di-ACC Rektor)
        $request = $this->createDummyRequest('Pending');

        $response = $this->actingAs($this->sarpras)->get(route('requests.receive.form', $request));
        $response->assertRedirect(route('requests.index'));
        $response->assertSessionHas('error');
    }

    // ============================================================
    // 17.1 PENGUJIAN VALIDASI ASET FISIK (NEGATIVE CASES)
    // ============================================================

    public function test_physical_receipt_fails_if_serial_number_duplicate(): void
    {
        $request = $this->createDummyRequest();
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'brand' => [$item->id => 'Test'],
            'prices' => [$item->id => 1000],
            'received_quantities' => [$item->id => 2],
            'serial_numbers' => [
                $item->id => ['SN-SAMA', 'SN-SAMA'] // 🔥 Duplikat
            ],
            'conditions' => [$item->id => ['Baik', 'Baik']],
            'images' => [$item->id => [
                UploadedFile::fake()->image('1.jpg'),
                UploadedFile::fake()->image('2.jpg')
            ]],
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasErrors();
    }

    public function test_physical_receipt_fails_if_image_missing(): void
    {
        $request = $this->createDummyRequest();
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'brand' => [$item->id => 'Test'],
            'prices' => [$item->id => 1000],
            'received_quantities' => [$item->id => 2],
            'serial_numbers' => [
                $item->id => ['SN-01', 'SN-02']
            ],
            'conditions' => [$item->id => ['Baik', 'Baik']],
            // 🔥 Gambar sengaja tidak dikirim
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasErrors(); // Wajib error karena gambar null
    }

    // ============================================================
    // 17.2 PENGUJIAN VALIDASI ASET NON-FISIK (NEGATIVE CASES)
    // ============================================================

    public function test_non_physical_receipt_fails_if_product_key_duplicate(): void
    {
        $request = $this->createDummyRequest('Dikonfirmasi', 'Non-Fisik', 'Tidak Habis Pakai');
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'categories' => [$item->id => 'Software'],
            'vendors' => [$item->id => 'Test'],
            'prices' => [$item->id => 1000],
            'license_types' => [$item->id => 'Selamanya'],
            'product_keys' => [
                $item->id => ['KEY-SAMA', 'KEY-SAMA'] // 🔥 Duplikat
            ],
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasErrors();
    }

    public function test_non_physical_receipt_fails_if_url_invalid(): void
    {
        $request = $this->createDummyRequest('Dikonfirmasi', 'Non-Fisik', 'Tidak Habis Pakai');
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'categories' => [$item->id => 'Software'],
            'vendors' => [$item->id => 'Test'],
            'prices' => [$item->id => 1000],
            'license_types' => [$item->id => 'Selamanya'],
            'access_urls' => [$item->id => 'bukan-url-yang-benar'], // 🔥 URL Invalid
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasErrors("access_urls.{$item->id}");
    }

    // ============================================================
    // 17.3 PENGUJIAN LIGHT RECEIPT (INTEGRITAS)
    // ============================================================

    public function test_light_receipt_does_not_create_master_assets(): void
    {
        $request = $this->createDummyRequest('Dikonfirmasi', 'Fisik', 'Habis Pakai');
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'received_quantities' => [$item->id => 2],
        ];

        $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);

        // 🔥 Pastikan TIDAK ADA data masuk ke tabel Master Data
        $this->assertDatabaseCount('asset_batches', 0);
        $this->assertDatabaseCount('assets', 0);
        $this->assertDatabaseCount('intangible_assets', 0);

        // Tapi status item berubah
        $this->assertEquals(2, $item->fresh()->received_quantity);
    }

    // ============================================================
    // 17.5 PENGUJIAN DOUBLE SUBMIT (IDEMPOTENSI)
    // ============================================================

    public function test_cannot_submit_receipt_twice_simultaneously(): void
    {
        $request = $this->createDummyRequest('Dikonfirmasi', 'Fisik', 'Habis Pakai');
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'received_quantities' => [$item->id => 2],
        ];

        // Submit pertama
        $response1 = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response1->assertSessionHasNoErrors();

        // Coba submit ulang (seolah-olah double click)
        $response2 = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);

        // Harus ditolak karena statusnya sudah berubah menjadi 'Diterima'
        $response2->assertSessionHas('error');

        // Pastikan data tidak dobel
        $this->assertEquals(2, $item->fresh()->received_quantity);
        $this->assertDatabaseCount('light_receipt_logs', 1); // Hanya tercatat 1x transaksi
    }
}
