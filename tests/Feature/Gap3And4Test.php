<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetBatch;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Maintenance;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Gap3And4Test extends TestCase
{
    use DatabaseTransactions;

    protected User $sarpras;
    protected Unit $unit;
    protected AssetType $arsType;
    protected AssetType $kpbType;
    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        // ============ Unit & User ============
        $this->unit = Unit::create([
            'name' => 'Test Unit',
            'category' => 'Test',
            'asset_id_code' => 'UM',
        ]);

        $this->sarpras = User::factory()->create([
            'level' => 'Sarpras',
            'unit_id' => $this->unit->id,
        ]);

        // ============ Kategori & Tipe (ARS + KPB) ============
        $catARS = AssetCategory::firstOrCreate(['code' => 'ARS'], ['name' => 'Arsip']);
        $catSPB = AssetCategory::firstOrCreate(['code' => 'SPB'], ['name' => 'Sarana Pembelajaran']);

        $this->arsType = AssetType::firstOrCreate(
            ['code' => 'ARS'],
            ['name' => 'Arsip', 'asset_category_id' => $catARS->id]
        );
        $this->kpbType = AssetType::firstOrCreate(
            ['code' => 'KPB'],
            ['name' => 'Koleksi Perpustakaan', 'asset_category_id' => $catSPB->id]
        );

        // ============ Asset Dummy (valid untuk FK + NOT NULL) ============
        $dummyBatch = AssetBatch::create([
            'asset_type_id' => $this->arsType->id,
            'name' => 'Laptop Test',
            'brand' => 'Test',
            'price' => 5_000_000,
            'purchase_date' => now(),
        ]);

        $this->asset = Asset::create([
            'asset_id'       => 'UM-TEST-001',
            'asset_batch_id' => $dummyBatch->id,
            'serial_number'  => 'SN-TEST-001',        // 🔥 NOT NULL-safe
            'unit_id'        => $this->unit->id,
            'location'       => 'Ruang Test',         // 🔥 NOT NULL-safe
            'qr_code'        => 'QR-TEST-001',        // 🔥 NOT NULL-safe
            'status'         => 'Tersedia',
            'condition'      => 'Baik',
        ]);
    }

    // ============================================================
    // 🔥 CELAH 3: LEGACY MIGRATION
    // ============================================================

    public function test_legacy_migration_moves_buku_to_kpb(): void
    {
        $batchBuku = AssetBatch::create([
            'asset_type_id' => $this->arsType->id,
            'name' => 'Buku Panduan Skripsi',
            'brand' => 'Test',
            'price' => 100_000,
            'purchase_date' => now(),
        ]);

        $batchAmbigu = AssetBatch::create([
            'asset_type_id' => $this->arsType->id,
            'name' => 'Dokumen Rapat Yayasan',
            'brand' => 'Test',
            'price' => 100_000,
            'purchase_date' => now(),
        ]);

        Artisan::call('sinadas:migrate-legacy-assets');

        $this->assertEquals(
            $this->kpbType->id,
            $batchBuku->fresh()->asset_type_id,
            'Buku harus pindah ke KPB'
        );
        $this->assertEquals(
            $this->arsType->id,
            $batchAmbigu->fresh()->asset_type_id,
            'Data ambigu tidak boleh dipindah otomatis'
        );
    }

    // ============================================================
    // 🔥 CELAH 4: MAINTENANCE AUDIT TRAIL
    // ============================================================

    public function test_maintenance_logs_are_created_on_status_change(): void
    {
        $this->actingAs($this->sarpras);

        $ticket = Maintenance::create([
            'asset_id'         => $this->asset->id,  // ✅ pakai ID valid
            'type'             => 'Kuratif',
            'maintenance_date' => now(),
            'description'      => 'Laptop mati',
            'technician'       => 'User',
            'recorded_by'      => $this->sarpras->id,
            'status'           => 'Menunggu Verifikasi',
            'cost'             => 0,
        ]);

        // Log #1: created
        $this->assertDatabaseHas('maintenance_logs', [
            'maintenance_id' => $ticket->id,
            'action'         => 'created',
            'new_status'     => 'Menunggu Verifikasi',
        ]);

        // Trigger log #2
        $ticket->update(['status' => 'Diterima']);

        $this->assertDatabaseHas('maintenance_logs', [
            'maintenance_id' => $ticket->id,
            'action'         => 'status_changed',
            'old_status'     => 'Menunggu Verifikasi',
            'new_status'     => 'Diterima',
        ]);

        $this->assertEquals(2, $ticket->logs()->count(), 'Harus ada 2 log');
    }
}
