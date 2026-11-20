<?php

namespace Tests\Feature;

use App\Models\AssetCategory;
use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\AssetType;
use App\Models\ProcurementOffer;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostRefactorGapsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $pjPengadaan;
    protected User $mahasiswa;
    protected AssetRequest $assetRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $unit = Unit::create(['name' => 'Test Gap Unit', 'category' => 'Test', 'asset_id_code' => 'UM']);

        $this->pjPengadaan = User::factory()->create(['level' => 'PJ Pengadaan', 'unit_id' => $unit->id]);
        $this->mahasiswa = User::factory()->create(['level' => 'Mahasiswa', 'unit_id' => $unit->id]);

        $this->assetRequest = AssetRequest::create([
            'requester_id' => $this->mahasiswa->id,
            'unit_id' => $unit->id,
            'request_type' => 'aset',
            'status' => 'Disetujui',
            'period_month' => now()->month,
            'period_year' => now()->year,
            'alasan_pengajuan' => 'Pengadaan Baru',
            'priority' => 'Normal',
            'reason' => 'Test',
        ]);

        AssetRequestItem::forceCreate([
            'asset_request_id' => $this->assetRequest->id,
            'unit_id' => $unit->id,
            'item_type' => 'Fisik',
            'sifat_barang' => 'Tidak Habis Pakai',
            'item_name' => 'Test Item',
            'quantity' => 1,
            'unit' => 'Unit',
            'estimated_price_per_unit' => 1000,
            'approval_status' => 'approved',
            'priority' => 'Normal',
            'alasan_pengajuan' => 'Pengadaan Baru',
            'reason' => 'Test',
        ]);
    }

    // ============================================================
    // 🔥 CELAH 1: AssetType::getOrdered() Harus Dinamis
    // ============================================================

    public function test_asset_type_get_ordered_is_dynamic_from_db(): void
    {
        // 1. Pastikan jumlah tipe saat ini
        $initialCount = AssetType::getOrdered()->count();

        // 2. Buat Kategori & Tipe Baru yang TIDAK ADA di hardcoded lama
        $newCategory = AssetCategory::create(['code' => 'Z99', 'name' => 'Kategori Test Z99']);
        $newType = AssetType::create([
            'code' => 'ZZZ',
            'name' => 'Tipe Baru ZZZ',
            'asset_category_id' => $newCategory->id,
        ]);

        // 3. Panggil getOrdered() lagi
        $orderedTypes = AssetType::getOrdered();

        // 4. Harus ada penambahan count dan Tipe Baru masuk
        $this->assertEquals($initialCount + 1, $orderedTypes->count());
        $this->assertTrue(
            $orderedTypes->contains('code', 'ZZZ'),
            'Tipe ZZZ yang baru dibuat harus muncul di getOrdered()'
        );
    }

    // ============================================================
    // 🔥 CELAH 2: File Private (Disimpan di Disk 'local')
    // ============================================================

    public function test_procurement_quotation_is_saved_to_local_storage(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $file = UploadedFile::fake()->create('penawaran_rahasia.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->pjPengadaan)->post(
            route('procurement.store', $this->assetRequest),
            [
                'vendor_name' => 'PT. Rahasia',
                'offer_date' => now()->format('Y-m-d'),
                'quotation_file' => $file,
                'items' => [
                    $this->assetRequest->items->first()->id => [
                        'unit_price' => 100000,
                    ]
                ]
            ]
        );

        $response->assertSessionHasNoErrors();

        $offer = ProcurementOffer::latest('id')->first();

        // Assert: File harus ada di disk 'local', TIDAK BOLEH ada di disk 'public'
        Storage::disk('local')->assertExists($offer->quotation_file);
        Storage::disk('public')->assertMissing($offer->quotation_file);
    }

    public function test_authorized_user_can_download_private_quotation(): void
    {
        Storage::fake('local');

        // Buat offer beserta file palsu di storage 'local'
        $path = 'procurement-quotations/dummy.pdf';
        Storage::disk('local')->put($path, 'Isi File');

        $offer = ProcurementOffer::create([
            'asset_request_id' => $this->assetRequest->id,
            'vendor_name' => 'PT. Rahasia',
            'offer_date' => now(),
            'subtotal' => 100000,
            'total_price' => 100000,
            'status' => 'Diajukan',
            'submitted_by' => $this->pjPengadaan->id,
            'quotation_file' => $path,
        ]);

        // Akses route download
        $response = $this->actingAs($this->pjPengadaan)->get(route('procurement.download-quotation', $offer));

        $response->assertStatus(200);
        $response->assertDownload('dummy.pdf'); // Laravel akan cek header Content-Disposition
    }

    public function test_unauthorized_user_cannot_download_private_quotation(): void
    {
        Storage::fake('local');

        $offer = ProcurementOffer::create([
            'asset_request_id' => $this->assetRequest->id,
            'vendor_name' => 'PT. Rahasia',
            'offer_date' => now(),
            'subtotal' => 100000,
            'total_price' => 100000,
            'status' => 'Diajukan',
            'submitted_by' => $this->pjPengadaan->id,
            'quotation_file' => 'procurement-quotations/dummy.pdf',
        ]);

        // Akses sebagai Mahasiswa
        $response = $this->actingAs($this->mahasiswa)->get(route('procurement.download-quotation', $offer));

        // Harus 403 Forbidden
        $response->assertStatus(403);
    }
}
