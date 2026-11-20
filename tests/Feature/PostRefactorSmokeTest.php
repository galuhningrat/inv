<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetBatch;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PostRefactorSmokeTest extends TestCase
{
  use DatabaseTransactions;

  protected User $sarpras;
  protected User $rektor;

  protected function setUp(): void
  {
    parent::setUp();

    $unit = Unit::create([
      'name' => 'Test Unit',
      'category' => 'Sarpras',
      'asset_id_code' => 'UM',
    ]);

    $this->sarpras = User::factory()->create(['level' => 'Sarpras', 'unit_id' => $unit->id]);
    $this->rektor = User::factory()->create(['level' => 'Rektor', 'unit_id' => $unit->id]);

    $category = AssetCategory::firstOrCreate(
      ['code' => 'EQU'],
      ['name' => 'Equipment', 'is_active' => true]
    );

    $type = AssetType::firstOrCreate(
      ['code' => 'KOM'],
      ['name' => 'Komputer', 'asset_category_id' => $category->id]
    );

    $batch = AssetBatch::forceCreate([
      'asset_type_id' => $type->id,
      'name' => 'Laptop Test',
      'brand' => 'Asus',
      'price' => 10000000,
      'purchase_date' => now()->subYear(),
    ]);

    Asset::forceCreate([
      'asset_id' => 'EQU/2026/UM/0001',
      'asset_batch_id' => $batch->id,
      'serial_number' => 'SN-001',
      'unit_id' => $unit->id,
      'location' => 'Lab 1',
      'condition' => 'Baik',
      'status' => 'Tersedia',
      'qr_code' => 'QR-001',
    ]);

    Asset::forceCreate([
      'asset_id' => 'EQU/2026/UM/0002',
      'asset_batch_id' => $batch->id,
      'serial_number' => 'SN-002',
      'unit_id' => $unit->id,
      'location' => 'Lab 1',
      'condition' => 'Baik',
      'status' => 'Tersedia',
      'qr_code' => 'QR-002',
    ]);
  }

  public function test_dashboard_loads_without_error(): void
  {
    $response = $this->actingAs($this->sarpras)->get(route('dashboard'));
    $response->assertStatus(200);
  }

  public function test_dashboard_stats_api_returns_data(): void
  {
    $response = $this->actingAs($this->sarpras)->get(route('api.stats'));
    $response->assertStatus(200);
    $response->assertJsonStructure([
      'total_assets',
      'available_assets',
      'borrowed_assets',
      'maintenance_assets',
      'total_users',
      'total_batches',
      'total_asset_value',
    ]);
  }

  public function test_report_index_loads(): void
  {
    $response = $this->actingAs($this->rektor)->get(route('reports.index'));
    $response->assertStatus(200);
  }

  public function test_financial_report_generates_without_error(): void
  {
    // 🔥 FIX: Hitung expected value dari DB, bukan hardcode
    // Karena DB asli mungkin sudah berisi aset lain di luar setUp
    $expectedTotalValue = (int) \Illuminate\Support\Facades\DB::table('assets')
      ->join('asset_batches', 'assets.asset_batch_id', '=', 'asset_batches.id')
      ->whereNull('assets.deleted_at')
      ->sum('asset_batches.price');

    $expectedTotalAssets = \App\Models\Asset::count();

    // Pastikan minimal ada aset yang kita buat di setUp (2 aset × 10 juta)
    $this->assertGreaterThanOrEqual(2, $expectedTotalAssets);
    $this->assertGreaterThanOrEqual(20000000, $expectedTotalValue);

    $response = $this->actingAs($this->rektor)->post(route('reports.generate'), [
      'type' => 'financial',
    ]);
    $response->assertStatus(200);

    // Format nilai ke Rupiah Indonesia (mis. 25.000.000)
    $formattedValue = number_format($expectedTotalValue, 0, ',', '.');
    $response->assertSee($formattedValue);
    $response->assertSee('LAPORAN KEUANGAN ASET');
  }

  public function test_assets_report_generates(): void
  {
    $response = $this->actingAs($this->rektor)->post(route('reports.generate'), [
      'type' => 'assets',
    ]);
    $response->assertStatus(200);
  }

  public function test_asset_request_create_form_loads(): void
  {
    $response = $this->actingAs($this->sarpras)->get(route('requests.create'));
    $response->assertStatus(200);
  }

  public function test_service_request_create_form_loads(): void
  {
    $response = $this->actingAs($this->sarpras)->get(
      route('requests.create-service', ['category' => 'pemeliharaan'])
    );
    $response->assertStatus(200);
  }
}
