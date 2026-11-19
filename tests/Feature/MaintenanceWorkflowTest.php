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
use Tests\TestCase;

class MaintenanceWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $sarpras;
    protected User $kalabTI;
    protected User $kalabTE;
    protected User $teknisi;
    protected User $mahasiswa;
    protected User $mahasiswaLain;
    protected Unit $unitTI;
    protected Unit $unitTE;
    protected Asset $assetTI;
    protected Asset $assetTE;

    protected function setUp(): void
    {
        parent::setUp();

        // ============ Units ============
        $this->unitTI = Unit::create([
            'name' => 'Laboratorium Komputer',
            'type' => 'Lab',
            'category' => 'Laboratorium',
            'asset_id_code' => 'UM',
        ]);
        $this->unitTE = Unit::create([
            'name' => 'Laboratorium Elektro',
            'type' => 'Lab',
            'category' => 'Laboratorium',
            'asset_id_code' => 'UM',
        ]);

        // ============ Users ============
        $this->sarpras       = User::factory()->create(['level' => 'Sarpras', 'unit_id' => $this->unitTI->id]);
        $this->kalabTI       = User::factory()->create(['level' => 'Kalab', 'unit_id' => $this->unitTI->id]);
        $this->kalabTE       = User::factory()->create(['level' => 'Kalab', 'unit_id' => $this->unitTE->id]);
        $this->teknisi       = User::factory()->create(['level' => 'Tim Pemeliharaan', 'unit_id' => $this->unitTI->id]);
        $this->mahasiswa     = User::factory()->create(['level' => 'Mahasiswa', 'unit_id' => $this->unitTI->id]);
        $this->mahasiswaLain = User::factory()->create(['level' => 'Mahasiswa', 'unit_id' => $this->unitTI->id]);

        // ============ Asset Category + Type ============
        $category = AssetCategory::firstOrCreate(
            ['code' => 'EQU'],
            ['name' => 'Equipment', 'is_active' => true]
        );
        $assetType = AssetType::firstOrCreate(
            ['code' => 'KOM'],
            ['name' => 'Komputer', 'asset_category_id' => $category->id]
        );
        // Pastikan category selalu set (kalau firstOrCreate menemukan type lama tanpa category)
        if (!$assetType->asset_category_id) {
            $assetType->update(['asset_category_id' => $category->id]);
        }

        // ============ Assets ============
        $batchTI = AssetBatch::forceCreate([
            'asset_type_id' => $assetType->id,
            'name' => 'Laptop TI Test',
            'brand' => 'Asus',
            'price' => 10000000,
            'purchase_date' => now()->subYear(),
        ]);
        $this->assetTI = Asset::forceCreate([
            'asset_id' => 'EQU/2026/UM/0001',
            'asset_batch_id' => $batchTI->id,
            'serial_number' => 'SN-TI-001',
            'unit_id' => $this->unitTI->id,
            'location' => 'Lab TI',
            'condition' => 'Baik',
            'status' => 'Tersedia',
            'qr_code' => 'QR-TI-001',
        ]);

        $batchTE = AssetBatch::forceCreate([
            'asset_type_id' => $assetType->id,
            'name' => 'Laptop TE Test',
            'brand' => 'Lenovo',
            'price' => 10000000,
            'purchase_date' => now()->subYear(),
        ]);
        $this->assetTE = Asset::forceCreate([
            'asset_id' => 'EQU/2026/UM/0002',
            'asset_batch_id' => $batchTE->id,
            'serial_number' => 'SN-TE-001',
            'unit_id' => $this->unitTE->id,
            'location' => 'Lab TE',
            'condition' => 'Baik',
            'status' => 'Tersedia',
            'qr_code' => 'QR-TE-001',
        ]);
    }

    /**
     * Helper: buat tiket maintenance via HTTP POST (jalur riil, bukan forceCreate).
     */
    private function createTicket(User $reporter, Asset $asset, array $override = []): Maintenance
    {
        $response = $this->actingAs($reporter)->post(route('maintenances.store'), array_merge([
            'asset_id' => $asset->id,
            'type' => 'Kuratif',
            'maintenance_date' => now()->format('Y-m-d'),
            'description' => 'Laptop tidak bisa menyala',
            'technician' => $reporter->name,
        ], $override));

        $response->assertSessionHasNoErrors();
        return Maintenance::latest('id')->first();
    }

    // ============================================================
    //  GROUP 1 — Pembuatan Tiket
    // ============================================================

    public function test_new_ticket_defaults_to_menunggu_verifikasi(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        $this->assertEquals('Menunggu Verifikasi', $ticket->status);
        $this->assertEquals(0, (int) $ticket->cost);
        $this->assertNull($ticket->verified_at);
        $this->assertNull($ticket->verified_by);
        $this->assertEquals($this->mahasiswa->id, $ticket->recorded_by);

        // Aset BELUM berubah status
        $this->assertEquals('Tersedia', $this->assetTI->fresh()->status);
    }

    // ============================================================
    //  GROUP 2 — Verifikasi (Happy Path + RBAC)
    // ============================================================

    public function test_kalab_can_verify_ticket_in_own_unit(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        $response = $this->actingAs($this->kalabTI)->post(
            route('maintenances.verify', $ticket),
            ['verification_notes' => 'Kerusakan valid, silakan proses']
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('maintenances.show', $ticket));

        $ticket->refresh();
        $this->assertEquals('Diterima', $ticket->status);
        $this->assertEquals($this->kalabTI->id, $ticket->verified_by);
        $this->assertNotNull($ticket->verified_at);
        $this->assertEquals('Kerusakan valid, silakan proses', $ticket->verification_notes);

        // Asset sekarang berstatus Maintenance
        $this->assertEquals('Maintenance', $this->assetTI->fresh()->status);
    }

    public function test_sarpras_can_verify_any_ticket(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        $response = $this->actingAs($this->sarpras)->post(
            route('maintenances.verify', $ticket),
            ['verification_notes' => 'OK']
        );

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Diterima', $ticket->fresh()->status);
    }

    // ============================================================
    //  GROUP 3 — Unit-Scope & Denial
    // ============================================================

    public function test_kalab_cannot_verify_ticket_in_other_unit(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        // Kalab TE mencoba verify tiket aset TI
        $response = $this->actingAs($this->kalabTE)->post(
            route('maintenances.verify', $ticket),
            ['verification_notes' => 'Coba verify']
        );

        $response->assertStatus(403);
        $this->assertEquals('Menunggu Verifikasi', $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->verified_by);
    }

    public function test_mahasiswa_cannot_verify(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        $response = $this->actingAs($this->mahasiswa)->post(
            route('maintenances.verify', $ticket),
            ['verification_notes' => 'Coba']
        );

        $response->assertStatus(403);
        $this->assertEquals('Menunggu Verifikasi', $ticket->fresh()->status);
    }

    public function test_cannot_verify_already_verified_ticket(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        // Verifikasi pertama
        $this->actingAs($this->kalabTI)->post(
            route('maintenances.verify', $ticket),
            ['verification_notes' => 'OK']
        );

        // Coba verifikasi ulang — harus ditolak
        $response = $this->actingAs($this->kalabTI)->post(
            route('maintenances.verify', $ticket),
            ['verification_notes' => 'Verifikasi ulang']
        );

        $response->assertStatus(403);
        $this->assertEquals('Diterima', $ticket->fresh()->status);
        // verification_notes tidak berubah
        $this->assertEquals('OK', $ticket->fresh()->verification_notes);
    }

    // ============================================================
    //  GROUP 4 — Update Status oleh Teknisi
    // ============================================================

    public function test_teknisi_cannot_update_before_verification(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        $response = $this->actingAs($this->teknisi)->put(
            route('maintenances.update', $ticket),
            [
                'status' => 'Dalam Proses',
                'cost' => 500000,
                'technician' => 'Teknisi A',
            ]
        );

        $response->assertStatus(403);
        $this->assertEquals('Menunggu Verifikasi', $ticket->fresh()->status);
        $this->assertEquals(0, (int) $ticket->fresh()->cost);
    }

    public function test_teknisi_can_update_after_verification(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        // Verifikasi dulu
        $this->actingAs($this->kalabTI)->post(
            route('maintenances.verify', $ticket),
            ['verification_notes' => 'OK']
        );

        // Teknisi update progres
        $response = $this->actingAs($this->teknisi)->put(
            route('maintenances.update', $ticket),
            [
                'status' => 'Dalam Proses',
                'cost' => 500000,
                'technician' => 'Teknisi A',
                'description' => 'Sedang diganti motherboard',
            ]
        );

        $response->assertSessionHasNoErrors();
        $ticket->refresh();
        $this->assertEquals('Dalam Proses', $ticket->status);
        $this->assertEquals(500000, (int) $ticket->cost);
        $this->assertEquals('Teknisi A', $ticket->technician);
    }

    // ============================================================
    //  GROUP 5 — Siklus Penutupan (Asset Return)
    // ============================================================

    public function test_completing_maintenance_returns_asset_to_available(): void
    {
        $ticket = $this->createTicket($this->mahasiswa, $this->assetTI);

        // Verifikasi → asset berubah ke Maintenance
        $this->actingAs($this->kalabTI)->post(
            route('maintenances.verify', $ticket),
            ['verification_notes' => 'OK']
        );
        $this->assertEquals('Maintenance', $this->assetTI->fresh()->status);

        // Selesaikan
        $response = $this->actingAs($this->teknisi)->put(
            route('maintenances.update', $ticket),
            [
                'status' => 'Selesai',
                'cost' => 500000,
                'technician' => 'Teknisi A',
            ]
        );

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Selesai', $ticket->fresh()->status);

        // Asset kembali ke Tersedia + kondisi Baik
        $this->assertEquals('Tersedia', $this->assetTI->fresh()->status);
        $this->assertEquals('Baik', $this->assetTI->fresh()->condition);
    }

    // ============================================================
    //  GROUP 6 — Filter & Statistik Index
    // ============================================================

    public function test_index_filters_by_status(): void
    {
        $ticket1 = $this->createTicket($this->mahasiswa, $this->assetTI);
        $ticket2 = $this->createTicket($this->mahasiswa, $this->assetTI);

        // Verifikasi tiket 1
        $this->actingAs($this->kalabTI)->post(
            route('maintenances.verify', $ticket1),
            ['verification_notes' => 'OK']
        );

        $response = $this->actingAs($this->sarpras)
            ->get(route('maintenances.index', ['status' => 'Menunggu Verifikasi']));

        $response->assertStatus(200);
        $response->assertSee($ticket2->maintenance_id);
        $response->assertDontSee($ticket1->maintenance_id);
    }

    public function test_index_shows_status_cards(): void
    {
        $this->createTicket($this->mahasiswa, $this->assetTI);

        $response = $this->actingAs($this->sarpras)->get(route('maintenances.index'));

        $response->assertStatus(200);
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Dalam Proses');
        $response->assertSee('Selesai');
    }

    // ============================================================
    //  GROUP 7 — Data Isolation
    // ============================================================

    public function test_mahasiswa_only_sees_own_tickets(): void
    {
        $ticket1 = $this->createTicket($this->mahasiswa, $this->assetTI);
        $ticket2 = $this->createTicket($this->mahasiswaLain, $this->assetTI);

        $response = $this->actingAs($this->mahasiswa)->get(route('maintenances.index'));

        $response->assertStatus(200);
        $response->assertSee($ticket1->maintenance_id);
        $response->assertDontSee($ticket2->maintenance_id);
    }
}
