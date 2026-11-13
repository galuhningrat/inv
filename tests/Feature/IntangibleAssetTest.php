<?php

namespace Tests\Feature;

use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\IntangibleAsset;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IntangibleAssetTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['level' => 'Admin']);
        $this->unit = Unit::factory()->create(['name' => 'Biro Sarpras', 'category' => 'Sarpras']);
        Storage::fake('local');
    }

    private function baseData(array $override = []): array
    {
        return array_merge([
            'name' => 'Test Asset',
            'category' => 'Software',
            'vendor' => 'Test Vendor',
            'price' => 1000000,
            'activation_date' => now()->format('Y-m-d'),
            'license_type' => 'Perpetual',
            'unit_id' => $this->unit->id,
            'status' => 'Aktif',
        ], $override);
    }

    // 🔥 GROUP 1: VALIDASI DASAR & KATEGORI

    public function test_create_software_with_valid_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Microsoft Office 365', 'license_model' => 'Per User'],
        ]));
        $response->assertRedirect(route('intangible-assets.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['name' => 'Test Asset', 'category' => 'Software']);
    }

    public function test_create_perpetual_software_without_expiry(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'license_type' => 'Perpetual',
            'expiry_date' => null,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['license_type' => 'Perpetual', 'expiry_date' => null]);
    }

    public function test_subscription_requires_expiry_date(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'license_type' => 'Subscription',
            'expiry_date' => null,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $response->assertSessionHasErrors('expiry_date');
    }

    public function test_reject_expiry_date_before_activation_date(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'activation_date' => '2026-12-31',
            'license_type' => 'Subscription',
            'expiry_date' => '2026-01-01',
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $response->assertSessionHasErrors('expiry_date');
    }

    public function test_create_cloud_saas_with_quota(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Cloud/SaaS',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'quota' => '100 Users',
            'category_data' => ['service_name' => 'Zoom', 'product_package' => 'Business', 'user_quota' => 100],
        ]));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['category' => 'Cloud/SaaS', 'quota' => '100 Users']);
    }

    public function test_create_digital_account_without_credentials(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Akun Digital',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'category_data' => ['account_name' => 'Test Account', 'account_type' => 'Institutional', 'account_email' => 'test@stti.ac.id'],
        ]));
        $response->assertSessionHasNoErrors();
        $asset = IntangibleAsset::where('category', 'Akun Digital')->first();
        $this->assertNotNull($asset);
        $this->assertArrayNotHasKey('password', $asset->category_data ?? []);
    }

    public function test_create_domain_with_registration_date(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Domain/Hosting',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'category_data' => ['domain_name' => 'stti.ac.id', 'registrar' => 'PANDI', 'domain_reg_date' => now()->format('Y-m-d')],
        ]));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['category' => 'Domain/Hosting']);
    }

    public function test_create_journal_subscription(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Jurnal Ilmiah',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'category_data' => ['journal_name' => 'IEEE Xplore', 'issn' => '1234-5678', 'coverage' => 'Full Access'],
        ]));
        $response->assertSessionHasNoErrors();
    }

    public function test_create_haki_without_vendor(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'HAKI/Paten',
            'vendor' => 'Test Vendor',
            'license_type' => 'Registered Right',
            'expiry_date' => now()->addYears(20)->format('Y-m-d'),
            'category_data' => [
                'work_name' => 'Invensi Test',
                'ip_type' => 'Paten',
                'registration_number' => 'P00202600001',
                'inventor' => 'Dr. Test',
                'right_holder' => 'STTI Cirebon',
            ],
        ]));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['category' => 'HAKI/Paten', 'vendor' => '-']);
    }

    public function test_create_digital_certificate(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Sertifikat Digital',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'category_data' => [
                'cert_name' => 'SSL Wildcard',
                'cert_type' => 'SSL/TLS',
                'issuer' => "Let's Encrypt",
                'serial_number' => 'ABC123',
                'subject_domain' => '*.stti.ac.id',
            ],
        ]));
        $response->assertSessionHasNoErrors();
    }

    public function test_create_internal_curriculum(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Kurikulum',
            'vendor' => null,
            'price' => 0,
            'license_type' => 'Limited Right',
            'category_data' => ['curriculum_name' => 'Kurikulum 2026', 'study_program' => 'Informatika', 'version' => '1.0'],
        ]));
        $response->assertSessionHasNoErrors();
    }

    public function test_reject_invalid_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'KategoriNgawur',
        ]));
        $response->assertSessionHasErrors('category');
    }

    // 🔥 GROUP 2: FILE UPLOAD

    public function test_reject_disallowed_file_type(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 100);
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $response->assertSessionHasErrors('certificate_file');
    }

    public function test_reject_file_over_5mb(): void
    {
        $file = UploadedFile::fake()->create('big.pdf', 6000);
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $response->assertSessionHasErrors('certificate_file');
    }

    public function test_upload_valid_pdf_file(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 500);
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $response->assertSessionHasNoErrors();
        $asset = IntangibleAsset::latest()->first();
        Storage::disk('local')->assertExists($asset->certificate_file);
    }

    public function test_old_file_survives_validation_failure(): void
    {
        $file1 = UploadedFile::fake()->create('old.pdf', 500);
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file1,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
        $oldFilePath = $asset->certificate_file;

        $response = $this->actingAs($this->admin)->put(route('intangible-assets.update', $asset), [
            'name' => '',
            'category' => 'Software',
            'price' => 1000000,
            'activation_date' => now()->format('Y-m-d'),
            'license_type' => 'Perpetual',
            'unit_id' => $this->unit->id,
            'status' => 'Aktif',
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]);
        $response->assertSessionHasErrors('name');
        Storage::disk('local')->assertExists($oldFilePath);
    }

    public function test_replace_file_deletes_old_file(): void
    {
        $file1 = UploadedFile::fake()->create('old.pdf', 500);
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file1,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
        $oldPath = $asset->certificate_file;
        Storage::disk('local')->assertExists($oldPath);

        $file2 = UploadedFile::fake()->create('new.pdf', 600);
        $this->actingAs($this->admin)->put(route('intangible-assets.update', $asset), $this->baseData([
            'certificate_file' => $file2,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $asset->refresh();
        $newPath = $asset->certificate_file;
        Storage::disk('local')->assertExists($newPath);
        Storage::disk('local')->assertMissing($oldPath);
        $this->assertNotEquals($oldPath, $newPath);
    }

    // 🔥 GROUP 3: KODE ASET

    public function test_asset_code_format(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
        $year = date('Y');
        $this->assertMatchesRegularExpression("/^NF\/{$year}\/\d{4}$/", $asset->asset_code);
    }

    public function test_asset_code_increments(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test 1', 'license_model' => 'Per User'],
        ]));
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test 2', 'license_model' => 'Per User'],
        ]));
        $assets = IntangibleAsset::orderBy('id')->get();
        $this->assertStringEndsWith('0001', $assets[0]->asset_code);
        $this->assertStringEndsWith('0002', $assets[1]->asset_code);
    }

    public function test_soft_deleted_asset_code_not_reused(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $firstAsset = IntangibleAsset::latest()->first();
        $firstCode = $firstAsset->asset_code;
        $firstAsset->delete();

        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test 2', 'license_model' => 'Per User'],
        ]));
        $newAsset = IntangibleAsset::latest()->first();
        $this->assertNotEquals($firstCode, $newAsset->asset_code);
    }

    // 🔥 GROUP 4: REMINDER

    public function test_expiring_soon_assets_included(): void
    {
        IntangibleAsset::forceCreate([
            'asset_code' => 'NF/2026/9999',
            'name' => 'Expiring Soon',
            'category' => 'Software',
            'price' => 1000000,
            'activation_date' => now()->subYear(),
            'license_type' => 'Subscription',
            'expiry_date' => now()->addDays(7),
            'reminder_days' => 30,
            'unit_id' => $this->unit->id,
            'status' => 'Aktif',
            'created_by' => $this->admin->id,
            'category_data' => [],
            'vendor' => 'Test Vendor',
        ]);
        $expiring = IntangibleAsset::getExpiringSoonAssets();
        $this->assertCount(1, $expiring);
    }

    public function test_perpetual_not_in_reminder(): void
    {
        IntangibleAsset::forceCreate([
            'asset_code' => 'NF/2026/9998',
            'name' => 'Perpetual Asset',
            'category' => 'Software',
            'price' => 1000000,
            'activation_date' => now()->subYear(),
            'license_type' => 'Perpetual',
            'expiry_date' => null,
            'unit_id' => $this->unit->id,
            'status' => 'Aktif',
            'created_by' => $this->admin->id,
            'category_data' => [],
            'vendor' => 'Test Vendor',
        ]);
        $expiring = IntangibleAsset::getExpiringSoonAssets();
        $this->assertCount(0, $expiring);
    }

    public function test_soft_deleted_not_in_reminder(): void
    {
        $asset = IntangibleAsset::forceCreate([
            'asset_code' => 'NF/2026/9997',
            'name' => 'Deleted Asset',
            'category' => 'Software',
            'price' => 1000000,
            'activation_date' => now()->subYear(),
            'license_type' => 'Subscription',
            'expiry_date' => now()->addDays(7),
            'reminder_days' => 30,
            'unit_id' => $this->unit->id,
            'status' => 'Aktif',
            'created_by' => $this->admin->id,
            'category_data' => [],
            'vendor' => 'Test Vendor',
        ]);
        $asset->delete();
        $expiring = IntangibleAsset::getExpiringSoonAssets();
        $this->assertCount(0, $expiring);
    }

    // 🔥 GROUP 5: AUTHORIZATION

    public function test_unauthorized_user_cannot_access(): void
    {
        $user = User::factory()->create(['level' => 'Mahasiswa']);
        $response = $this->actingAs($user)->get(route('intangible-assets.index'));
        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_download_certificate(): void
    {
        $file = UploadedFile::fake()->create('cert.pdf', 500);
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
        auth()->logout();
        $response = $this->get(route('intangible-assets.download-certificate', $asset));
        $response->assertRedirect('/login');
    }

    // 🔥 GROUP 6: EDIT & DETAIL

    public function test_edit_page_loads_old_data(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'name' => 'Asset Lama',
            'category_data' => ['product_name' => 'Produk Lama', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
        $response = $this->actingAs($this->admin)->get(route('intangible-assets.edit', $asset));
        $response->assertStatus(200);
        $response->assertSee('Asset Lama');
        $response->assertSee('Produk Lama');
    }

    public function test_category_change_handles_data(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Software',
            'category_data' => ['product_name' => 'Software Lama', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
        $this->assertEquals('Software Lama', $asset->category_data['product_name']);

        $this->actingAs($this->admin)->put(route('intangible-assets.update', $asset), $this->baseData([
            'category' => 'HAKI/Paten',
            'vendor' => null,
            'license_type' => 'Registered Right',
            'expiry_date' => now()->addYears(20)->format('Y-m-d'),
            'category_data' => [
                'work_name' => 'Invensi Baru',
                'ip_type' => 'Paten',
                'registration_number' => 'P001',
                'inventor' => 'Dr. X',
                'right_holder' => 'STTI',
            ],
        ]));
        $asset->refresh();
        $this->assertEquals('HAKI/Paten', $asset->category);
        $this->assertEquals('Invensi Baru', $asset->category_data['work_name']);
        $this->assertArrayNotHasKey('product_name', $asset->category_data);
    }

    // 🔥 GROUP 7: FIELD SENSITIF

    public function test_product_key_not_visible_in_index(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Software',
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User', 'product_key' => 'SECRET-KEY-XYZ-123'],
        ]));
        $response = $this->actingAs($this->admin)->get(route('intangible-assets.index'));
        $response->assertDontSee('SECRET-KEY-XYZ-123');
    }

    // 🔥 GROUP 8: LINK KE PENGADAAN

    public function test_duplicate_link_to_request_prevented(): void
    {
        $request = AssetRequest::create([
            'request_type' => 'aset',
            'requester_id' => $this->admin->id,
            'unit_id' => $this->unit->id,
            'status' => 'Dikonfirmasi',
            'jenis_barang' => 'Tidak Habis Pakai',
            'kategori_barang' => 'Lainnya',
        ]);
        AssetRequestItem::forceCreate([
            'asset_request_id' => $request->id,
            'item_name' => 'Item Non-Fisik 1',
            'item_type' => 'Non-Fisik',
            'quantity' => 1,
            'approval_status' => 'approved',
        ]);

        $response1 = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'asset_request_id' => $request->id,
            'category_data' => ['product_name' => 'Test 1', 'license_model' => 'Per User'],
        ]));
        $response1->assertSessionHasNoErrors();

        $response2 = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'asset_request_id' => $request->id,
            'category_data' => ['product_name' => 'Test 2', 'license_model' => 'Per User'],
        ]));
        $response2->assertSessionHasErrors('asset_request_id');
    }
}
