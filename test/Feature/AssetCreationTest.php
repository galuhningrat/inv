<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user Sarpras untuk bypass authorize
        $this->user = User::factory()->create(['level' => 'Sarpras']);

        // Buat data master minimal
        AssetType::create(['code' => 'JAR', 'name' => 'Jaringan']);
        AssetType::create(['code' => 'TNH', 'name' => 'Tanah']);
        Unit::create(['name' => 'Teknik Informatika', 'category' => 'Akademik', 'type' => 'prodi']);
    }

    /** @test */
    public function it_fails_to_create_network_asset_with_invalid_mac_address()
    {
        $this->actingAs($this->user);

        $response = $this->post('/assets-inv', [
            'asset_type_id' => 1, // JAR
            'name' => 'Switch Rusak',
            'brand' => 'TP-Link',
            'price' => 100000,
            'purchase_date' => '2024-01-01',
            'quantity' => 1,
            'unit_id' => 1,
            'location_detail' => 'Lab',
            'conditions' => ['Baik'],
            'serial_numbers' => ['SN-123'],
            'category_data' => ['subkategori' => 'switch'],
            'unit_data' => [
                ['mac_address' => '123:MAC:SALAH'], // 🔥 Sengaja dibuat salah
            ],
        ]);

        $response->assertSessionHasErrors('unit_data.0.mac_address');
    }

    /** @test */
    public function it_fails_to_create_asset_with_duplicate_serial_number()
    {
        $this->actingAs($this->user);

        // Buat aset pertama
        $this->post('/assets-inv', [
            'asset_type_id' => 1, 'name' => 'Switch 1', 'brand' => 'TP-Link', 'price' => 100,
            'purchase_date' => '2024-01-01', 'quantity' => 1, 'unit_id' => 1, 'location_detail' => 'Lab',
            'conditions' => ['Baik'], 'serial_numbers' => ['SN-DUPLIKAT'], 'category_data' => ['subkategori' => 'switch'],
        ]);

        // Coba buat aset kedua dengan S/N yang sama
        $response = $this->post('/assets-inv', [
            'asset_type_id' => 1, 'name' => 'Switch 2', 'brand' => 'TP-Link', 'price' => 100,
            'purchase_date' => '2024-01-01', 'quantity' => 1, 'unit_id' => 1, 'location_detail' => 'Lab',
            'conditions' => ['Baik'], 'serial_numbers' => ['SN-DUPLIKAT'], 'category_data' => ['subkategori' => 'switch'],
        ]);

        $response->assertSessionHasErrors('serial_numbers.0');
    }

    /** @test */
    public function it_can_create_tanah_asset_without_serial_number()
    {
        $this->actingAs($this->user);

        $response = $this->post('/assets-inv', [
            'asset_type_id' => 2, // TNH
            'name' => 'Tanah Kavling A',
            'price' => 500000000,
            'purchase_date' => '2024-01-01',
            'quantity' => 1,
            'conditions' => ['Baik'],
            'category_data' => [
                'subkategori' => 'HM',
                'luas_m2' => 500,
                'no_sertifikat' => 'SHM-123',
                'lokasi_administratif' => 'Cirebon',
            ],
            // Perhatikan: field 'brand' dan 'serial_numbers' tidak dikirim
        ]);

        $response->assertRedirect('/assets-inv');
        $this->assertDatabaseHas('asset_batches', ['name' => 'Tanah Kavling A']);
        $this->assertDatabaseHas('assets', ['serial_number' => null]); // S/N kosong diizinkan
    }
}
