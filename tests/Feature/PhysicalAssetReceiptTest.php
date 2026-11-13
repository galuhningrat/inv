<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetBatch;
use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\AssetType;
use App\Models\Location;
use App\Models\QrCode;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhysicalAssetReceiptTest extends TestCase
{
    use DatabaseTransactions;

    protected User $sarpras;
    protected Unit $unit;
    protected Location $location;
    protected AssetType $assetType;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');

        $this->sarpras = User::factory()->create(['level' => 'Sarpras']);
        $this->unit = Unit::factory()->create(['name' => 'Laboratorium Komputer', 'category' => 'Laboratorium']);
        $this->location = Location::create(['unit_id' => $this->unit->id, 'name' => 'Lab Komputer 1']);
        $this->assetType = AssetType::firstOrCreate(['code' => 'KOM'], ['name' => 'Komputer & Laptop']);
    }

    private function createDummyRequest(int $quantity = 5): AssetRequest
    {
        $request = AssetRequest::create([
            'requester_id' => $this->sarpras->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'aset',
            'status' => 'Dikonfirmasi',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'priority' => 'Normal',
            'reason' => 'Butuh laptop baru',
            'period_month' => now()->month,
            'period_year' => now()->year,
        ]);

        AssetRequestItem::create([
            'asset_request_id' => $request->id,
            'unit_id' => $this->unit->id,
            'item_type' => 'Fisik',
            'sifat_barang' => 'Tidak Habis Pakai',
            'asset_type_id' => $this->assetType->id,
            'item_name' => 'Laptop Asus ROG',
            'quantity' => $quantity,
            'unit' => 'Unit',
            'estimated_price_per_unit' => 15000000,
            'approval_status' => 'approved',
            'priority' => 'Normal',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'reason' => 'Untuk praktikum',
        ]);

        return $request;
    }

    public function test_receive_physical_assets_fully(): void
    {
        $request = $this->createDummyRequest(quantity: 2);
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'unit_id' => $this->unit->id,
            'location_id' => $this->location->id,
            'location_detail' => 'Meja Dosen 1',
            'brand' => [$item->id => 'Asus'],
            'model' => [$item->id => 'ROG Zephyrus'],
            'prices' => [$item->id => 15500000],
            'received_quantities' => [$item->id => 2],
            'images' => [$item->id => [UploadedFile::fake()->image('laptop1.jpg'), UploadedFile::fake()->image('laptop2.jpg')]],
            'serial_numbers' => [$item->id => ['SN-ROG-001', 'SN-ROG-002']],
            'conditions' => [$item->id => ['Baik', 'Baik']],
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('requests.index'));

        $this->assertEquals('Diterima', $request->fresh()->status);
        $this->assertDatabaseCount('asset_batches', 1);
        $this->assertDatabaseCount('assets', 2);
        $this->assertDatabaseHas('assets', ['serial_number' => 'SN-ROG-001']);
        $this->assertDatabaseHas('assets', ['serial_number' => 'SN-ROG-002']);
        $this->assertDatabaseCount('qr_codes', 2);
    }

    public function test_receive_physical_assets_partially(): void
    {
        $request = $this->createDummyRequest(quantity: 5);
        $item = $request->items->first();

        $payload = [
            'purchase_date' => now()->format('Y-m-d'),
            'penanggung_jawab_id' => $this->sarpras->id,
            'unit_id' => $this->unit->id,
            'location_id' => $this->location->id,
            'location_detail' => '-',
            'brand' => [$item->id => 'Asus'],
            'model' => [$item->id => 'ROG Zephyrus'],
            'prices' => [$item->id => 15500000],
            'received_quantities' => [$item->id => 3],
            'images' => [$item->id => [
                UploadedFile::fake()->image('laptop1.jpg'),
                UploadedFile::fake()->image('laptop2.jpg'),
                UploadedFile::fake()->image('laptop3.jpg')
            ]],
            'serial_numbers' => [$item->id => ['SN-ROG-001', 'SN-ROG-002', 'SN-ROG-003']],
            'conditions' => [$item->id => ['Baik', 'Baik', 'Rusak Ringan']],
        ];

        $response = $this->actingAs($this->sarpras)->post(route('requests.receive', $request), $payload);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('asset_batches', 1);
        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseHas('assets', ['serial_number' => 'SN-ROG-003']);
        $this->assertDatabaseMissing('assets', ['serial_number' => 'SN-ROG-004']);
    }
}
