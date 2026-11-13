<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\User;
<<<<<<< HEAD
use Illuminate\Foundation\Testing\DatabaseTransactions;
=======
use Illuminate\Foundation\Testing\RefreshDatabase;
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
use Tests\TestCase;

class AssetCreationTest extends TestCase
{
<<<<<<< HEAD
    use DatabaseTransactions;

    public function test_sarpras_can_create_tanah_without_location()
    {
        // 🔥 Jangan pakai seeder, buat manual jika kosong
        $sarpras = User::where('level', 'Sarpras')->first() ?? User::factory()->create(['level' => 'Sarpras']);
        $tanahType = AssetType::where('code', 'TNH')->first() ?? AssetType::create(['code' => 'TNH', 'name' => 'Tanah']);
=======
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
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e

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
<<<<<<< HEAD
        $sarpras = User::where('level', 'Sarpras')->first() ?? User::factory()->create(['level' => 'Sarpras']);
        $tanahType = AssetType::where('code', 'TNH')->first() ?? AssetType::create(['code' => 'TNH', 'name' => 'Tanah']);
=======
        $sarpras = User::where('level', 'Sarpras')->first();
        $tanahType = AssetType::where('code', 'TNH')->first();
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e

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
