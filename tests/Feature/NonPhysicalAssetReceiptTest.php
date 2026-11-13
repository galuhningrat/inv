<?php

namespace Tests\Feature;

use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\IntangibleAsset;
use App\Models\LightReceiptLog;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NonPhysicalAssetReceiptTest extends TestCase
{
    use DatabaseTransactions;

    protected User $sarpras;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->sarpras = User::factory()->create(['level' => 'Sarpras']);
        $this->unit = Unit::factory()->create(['name' => 'Biro IT', 'category' => 'IT']);
    }

    private function createDummyRequest(): AssetRequest
    {
        $request = AssetRequest::create([
            'requester_id' => $this->sarpras->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'aset',
            'status' => 'Dikonfirmasi',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'priority' => 'Normal',
            'reason' => 'Butuh lisensi software',
            'period_month' => now()->month,
            'period_year' => now()->year,
        ]);

        AssetRequestItem::create([
            'asset_request_id' => $request->id,
            'unit_id' => $this->unit->id,
            'item_type' => 'Non-Fisik',
            'sifat_barang' => 'Tidak Habis Pakai',
            'category' => 'Software',
            'item_name' => 'Lisensi Windows 11',
            'quantity' => 2,
            'unit' => 'Lisensi',
            'estimated_price_per_unit' => 2000000,
            'approval_status' => 'approved',
            'priority' => 'Normal',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'reason' => 'Untuk komputer lab',
        ]);

        return $request;
    }

    public function test_receive_non_physical_subscription(): void
    {
        $request = $this->createDummyRequest();
        $item = $request->items->first();
        $file = UploadedFile::fake()->create('sertifikat.pdf', 500, 'application/pdf');

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'categories' => [$item->id => 'Software'],
            'vendors' => [$item->id => 'Microsoft Indonesia'],
            'prices' => [$item->id => 2100000],
            'license_types' => [$item->id => 'Berlangganan'],
            'expiry_dates' => [$item->id => now()->addYear()->format('Y-m-d')],
            'access_urls' => [$item->id => 'https://microsoft.com'],
            'certificate_files' => [$item->id => $file],
            'product_keys' => [$item->id => ['AAAAA-BBBBB-CCCCC-DDDDD-EEEEE', 'FFFFF-GGGGG-HHHHH-IIIII-JJJJJ']],
            'assigned_emails' => [$item->id => ['lab1@stti.ac.id', 'lab2@stti.ac.id']]
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasNoErrors();
        if (session('error')) $this->fail(session('error'));

        $this->assertDatabaseCount('intangible_assets', 2);
        $this->assertDatabaseHas('intangible_assets', ['vendor' => 'Microsoft Indonesia', 'license_type' => 'Berlangganan']);

        // 🔥 FIX: Jangan pakai assertDatabaseHas untuk kolom terenkripsi. Ambil lewat Model.
        $assets = IntangibleAsset::orderBy('id', 'asc')->get();
        $this->assertEquals('AAAAA-BBBBB-CCCCC-DDDDD-EEEEE', $assets[0]->product_key);
        $this->assertEquals('FFFFF-GGGGG-HHHHH-IIIII-JJJJJ', $assets[1]->product_key);

        Storage::disk('local')->assertExists($assets[0]->certificate_file);
        Storage::disk('public')->assertMissing($assets[0]->certificate_file);
    }

    public function test_receive_non_physical_perpetual_without_expiry(): void
    {
        $request = $this->createDummyRequest();
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'categories' => [$item->id => 'Software'],
            'vendors' => [$item->id => 'Adobe'],
            'prices' => [$item->id => 5000000],
            'license_types' => [$item->id => 'Selamanya'],
            'expiry_dates' => [$item->id => null],
            'product_keys' => [$item->id => ['ADOBE-123', 'ADOBE-456']]
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasNoErrors();
        if (session('error')) $this->fail(session('error'));

        $this->assertDatabaseCount('intangible_assets', 2);
        $this->assertDatabaseHas('intangible_assets', ['license_type' => 'Selamanya', 'expiry_date' => null]);
    }

    public function test_expiry_date_cannot_be_before_purchase_date(): void
    {
        $request = $this->createDummyRequest();
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'categories' => [$item->id => 'Software'],
            'vendors' => [$item->id => 'Test'],
            'prices' => [$item->id => 1000],
            'license_types' => [$item->id => 'Berlangganan'],
            'expiry_dates' => [$item->id => now()->subDay()->format('Y-m-d')],
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasErrors("expiry_dates.{$item->id}");
    }

    public function test_partial_light_receipt_accumulation(): void
    {
        $request = AssetRequest::create([
            'requester_id' => $this->sarpras->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'aset',
            'status' => 'Dikonfirmasi',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'priority' => 'Normal',
            'reason' => 'Butuh ATK',
            'period_month' => now()->month,
            'period_year' => now()->year,
        ]);

        $item = AssetRequestItem::create([
            'asset_request_id' => $request->id,
            'unit_id' => $this->unit->id,
            'item_type' => 'Fisik',
            'sifat_barang' => 'Habis Pakai',
            'category' => 'ATK',
            'item_name' => 'Kertas HVS',
            'quantity' => 100,
            'unit' => 'Lembar',
            'estimated_price_per_unit' => 500,
            'approval_status' => 'approved',
            'priority' => 'Normal',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'reason' => 'Untuk fotokopi',
        ]);

        $file = UploadedFile::fake()->create('nota.pdf', 500, 'application/pdf');

        $response1 = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'received_quantities' => [$item->id => 60],
            'receipt_notes' => [$item->id => 'Diterima 60 lembar pertama'],
            'receipt_proof_files' => [$item->id => $file],
        ]);

        $response1->assertSessionHasNoErrors();
        if (session('error')) $this->fail(session('error'));

        $this->assertEquals(60, $item->fresh()->received_quantity);
        $this->assertEquals('Diterima Sebagian', $request->fresh()->status);

        $log = LightReceiptLog::first();
        Storage::disk('local')->assertExists($log->proof_file);
        Storage::disk('public')->assertMissing($log->proof_file);

        $response2 = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'received_quantities' => [$item->id => 40],
            'receipt_notes' => [$item->id => 'Sisa 40 lembar diterima'],
        ]);

        $response2->assertSessionHasNoErrors();
        if (session('error')) $this->fail(session('error'));

        $this->assertEquals(100, $item->fresh()->received_quantity);
        $this->assertEquals('Diterima', $request->fresh()->status);
        $this->assertDatabaseCount('light_receipt_logs', 2);
    }
}
