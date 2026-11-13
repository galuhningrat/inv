<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sarpras_can_create_tanah_without_location()
    {
        $sarpras = User::where('level', 'Sarpras')->first();
        $tanahType = AssetType::where('code', 'TNH')->first();

        $response = $this->actingAs($sarpras)->post(route('assets-inv.store'), [
            'asset_type_id' => $tanahType->id,
            'name' => 'Tanah Kavling Test',
            'price' => 500000000,
            'purchase_date' => now()->format('Y-m-d'),
            'quantity' => 1,
            'conditions' => ['Baik'],
            'category_data' => [
                'subkategori' => 'HM',
                'luas_m2' => 500,
                'no_sertifikat' => 'HM-12345',
                'lokasi_administratif' => 'Cirebon',
            ],
        ]);

        if (session('error')) {
            $this->fail(session('error'));
        }

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('assets-inv.index'));
        $this->assertDatabaseHas('asset_batches', ['name' => 'Tanah Kavling Test']);
    }

    public function test_tanah_creation_fails_if_sertifikat_is_missing()
    {
        $sarpras = User::where('level', 'Sarpras')->first();
        $tanahType = AssetType::where('code', 'TNH')->first();

        $response = $this->actingAs($sarpras)->post(route('assets-inv.store'), [
            'asset_type_id' => $tanahType->id,
            'name' => 'Tanah Tanpa Sertifikat',
            'price' => 100000,
            'purchase_date' => now()->format('Y-m-d'),
            'quantity' => 1,
            'conditions' => ['Baik'],
            'category_data' => [
                'subkategori' => 'HM',
                'luas_m2' => 500,
            ],
        ]);

        $response->assertSessionHasErrors('category_data.no_sertifikat');
        $this->assertDatabaseMissing('asset_batches', ['name' => 'Tanah Tanpa Sertifikat']);
    }
}
