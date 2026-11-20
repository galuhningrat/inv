<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\User;
use Database\Seeders\StakeholderSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssetCreationTest extends TestCase
{
    // 🔥 FIX 1: Ganti RefreshDatabase ke DatabaseTransactions agar DB asli aman
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // 🔥 FIX 2: Panggil seeder spesifik, bukan $this->seed() yang sudah dihapus
        $this->seed(StakeholderSeeder::class);
    }

    public function test_sarpras_can_create_tanah_without_location()
    {
        // Ambil user Sarpras dari seeder
        $sarpras = User::where('level', 'Sarpras')->first();
        $this->assertNotNull($sarpras, 'User Sarpras harus ada dari StakeholderSeeder');

        // Ambil tipe aset TNH (dari migration)
        $tanahType = AssetType::where('code', 'TNH')->first();
        $this->assertNotNull($tanahType, 'AssetType TNH harus ada');

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

        // Cek jika ada error sistem (bukan validasi)
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
                // no_sertifikat sengaja dihilangkan
            ],
        ]);

        // Harus gagal karena validasi required dari schema TNH
        $response->assertSessionHasErrors('category_data.no_sertifikat');
        $this->assertDatabaseMissing('asset_batches', ['name' => 'Tanah Tanpa Sertifikat']);
    }
}
