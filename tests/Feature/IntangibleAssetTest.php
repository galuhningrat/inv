<?php

namespace Tests\Feature;

use App\Models\AssetRequest;
use App\Models\AssetRequestItem;
use App\Models\IntangibleAsset;
use App\Models\Unit;
use App\Models\User;
<<<<<<< HEAD
use Illuminate\Foundation\Testing\DatabaseTransactions;
=======
use Illuminate\Foundation\Testing\RefreshDatabase;
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IntangibleAssetTest extends TestCase
{
<<<<<<< HEAD
    use DatabaseTransactions;

    protected User $admin;
=======
    use RefreshDatabase;

    protected User $admin;

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
<<<<<<< HEAD
        $this->admin = User::factory()->create(['level' => 'Admin']);
        $this->unit = Unit::factory()->create(['name' => 'Biro Sarpras', 'category' => 'Sarpras']);
        Storage::fake('local');
    }

=======

        // Buat user admin untuk auth
        $this->admin = User::factory()->create(['level' => 'Admin']);

        // Buat unit default
        $this->unit = Unit::factory()->create([
            'name' => 'Biro Sarpras',
            'category' => 'Sarpras',
        ]);

        // Fake storage agar file tidak benar-benar ditulis ke disk
        Storage::fake('local');
    }

    /**
     * Helper: data dasar valid untuk semua test
     */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
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

<<<<<<< HEAD
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

=======
    // ============================================================
    // 🔥 GROUP 1: VALIDASI DASAR & KATEGORI (Poin 1-13)
    // ============================================================

    /** Poin 1: Membuat Software dengan data valid */
    public function test_create_software_with_valid_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => [
                'product_name' => 'Microsoft Office 365',
                'license_model' => 'Per User',
            ],
        ]));

        $response->assertRedirect(route('intangible-assets.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', [
            'name' => 'Test Asset',
            'category' => 'Software',
        ]);
    }

    /** Poin 2: Software perpetual tanpa expiry_date */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_create_perpetual_software_without_expiry(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'license_type' => 'Perpetual',
            'expiry_date' => null,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['license_type' => 'Perpetual', 'expiry_date' => null]);
    }

=======

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', [
            'license_type' => 'Perpetual',
            'expiry_date' => null,
        ]);
    }

    /** Poin 3: Software Subscription wajib expiry_date */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_subscription_requires_expiry_date(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'license_type' => 'Subscription',
<<<<<<< HEAD
            'expiry_date' => null,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $response->assertSessionHasErrors('expiry_date');
    }

=======
            'expiry_date' => null, // sengaja kosong
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));

        $response->assertSessionHasErrors('expiry_date');
    }

    /** Poin 4: Tolak expiry_date sebelum activation_date */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_reject_expiry_date_before_activation_date(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'activation_date' => '2026-12-31',
            'license_type' => 'Subscription',
<<<<<<< HEAD
            'expiry_date' => '2026-01-01',
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $response->assertSessionHasErrors('expiry_date');
    }

=======
            'expiry_date' => '2026-01-01', // lebih awal dari activation_date
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));

        $response->assertSessionHasErrors('expiry_date');
    }

    /** Poin 5: Cloud/SaaS dengan kuota */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_create_cloud_saas_with_quota(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Cloud/SaaS',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'quota' => '100 Users',
<<<<<<< HEAD
            'category_data' => ['service_name' => 'Zoom', 'product_package' => 'Business', 'user_quota' => 100],
        ]));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['category' => 'Cloud/SaaS', 'quota' => '100 Users']);
    }

=======
            'category_data' => [
                'service_name' => 'Zoom',
                'product_package' => 'Business',
                'user_quota' => 100,
            ],
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', [
            'category' => 'Cloud/SaaS',
            'quota' => '100 Users',
        ]);
    }

    /** Poin 6: Akun Digital — pastikan field assigned_user_email tidak wajib */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_create_digital_account_without_credentials(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Akun Digital',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
<<<<<<< HEAD
            'category_data' => ['account_name' => 'Test Account', 'account_type' => 'Institutional', 'account_email' => 'test@stti.ac.id'],
        ]));
        $response->assertSessionHasNoErrors();
=======
            'category_data' => [
                'account_name' => 'Test Account',
                'account_type' => 'Institutional',
                'account_email' => 'test@stti.ac.id',
            ],
        ]));

        $response->assertSessionHasNoErrors();
        // Pastikan tidak ada field password/token yang tersimpan
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $asset = IntangibleAsset::where('category', 'Akun Digital')->first();
        $this->assertNotNull($asset);
        $this->assertArrayNotHasKey('password', $asset->category_data ?? []);
    }

<<<<<<< HEAD
=======
    /** Poin 7: Domain dengan tanggal registrasi */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_create_domain_with_registration_date(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Domain/Hosting',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
<<<<<<< HEAD
            'category_data' => ['domain_name' => 'stti.ac.id', 'registrar' => 'PANDI', 'domain_reg_date' => now()->format('Y-m-d')],
        ]));
=======
            'category_data' => [
                'domain_name' => 'stti.ac.id',
                'registrar' => 'PANDI',
                'domain_reg_date' => now()->format('Y-m-d'),
            ],
        ]));

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['category' => 'Domain/Hosting']);
    }

<<<<<<< HEAD
=======
    /** Poin 9: Jurnal Ilmiah */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_create_journal_subscription(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Jurnal Ilmiah',
            'license_type' => 'Subscription',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
<<<<<<< HEAD
            'category_data' => ['journal_name' => 'IEEE Xplore', 'issn' => '1234-5678', 'coverage' => 'Full Access'],
        ]));
        $response->assertSessionHasNoErrors();
    }

=======
            'category_data' => [
                'journal_name' => 'IEEE Xplore',
                'issn' => '1234-5678',
                'coverage' => 'Full Access',
            ],
        ]));

        $response->assertSessionHasNoErrors();
    }

    /** Poin 10: HAKI — vendor harus diabaikan */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_create_haki_without_vendor(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'HAKI/Paten',
<<<<<<< HEAD
            'vendor' => 'Test Vendor',
=======
            'vendor' => 'Test Vendor', // user coba isi
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
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
<<<<<<< HEAD
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('intangible_assets', ['category' => 'HAKI/Paten', 'vendor' => '-']);
    }

=======

        $response->assertSessionHasNoErrors();
        // Vendor harus NULL karena HAKI hide_vendor = true
        $this->assertDatabaseHas('intangible_assets', [
            'category' => 'HAKI/Paten',
            'vendor' => '-',
        ]);
    }

    /** Poin 11: Sertifikat Digital */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
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
<<<<<<< HEAD
        $response->assertSessionHasNoErrors();
    }

=======

        $response->assertSessionHasNoErrors();
    }

    /** Poin 12: Kurikulum internal — vendor diabaikan, tanpa harga */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_create_internal_curriculum(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Kurikulum',
            'vendor' => null,
            'price' => 0,
            'license_type' => 'Limited Right',
<<<<<<< HEAD
            'category_data' => ['curriculum_name' => 'Kurikulum 2026', 'study_program' => 'Informatika', 'version' => '1.0'],
        ]));
        $response->assertSessionHasNoErrors();
    }

=======
            'category_data' => [
                'curriculum_name' => 'Kurikulum 2026',
                'study_program' => 'Informatika',
                'version' => '1.0',
            ],
        ]));

        $response->assertSessionHasNoErrors();
    }

    /** Poin 13: Kategori tidak valid ditolak */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_reject_invalid_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'KategoriNgawur',
        ]));
<<<<<<< HEAD
        $response->assertSessionHasErrors('category');
    }

    // 🔥 GROUP 2: FILE UPLOAD

    public function test_reject_disallowed_file_type(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 100);
=======

        $response->assertSessionHasErrors('category');
    }

    // ============================================================
    // 🔥 GROUP 2: FILE UPLOAD (Poin 14-18)
    // ============================================================

    /** Poin 14: Tolak file dengan tipe tidak diizinkan */
    public function test_reject_disallowed_file_type(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 100);

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
        $response->assertSessionHasErrors('certificate_file');
    }

    public function test_reject_file_over_5mb(): void
    {
        $file = UploadedFile::fake()->create('big.pdf', 6000);
=======

        $response->assertSessionHasErrors('certificate_file');
    }

    /** Poin 15: Tolak file melebihi 5MB */
    public function test_reject_file_over_5mb(): void
    {
        $file = UploadedFile::fake()->create('big.pdf', 6000); // 6000 KB = ~6MB

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
        $response->assertSessionHasErrors('certificate_file');
    }

    public function test_upload_valid_pdf_file(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 500);
=======

        $response->assertSessionHasErrors('certificate_file');
    }

    /** Poin 16: File valid berhasil diupload */
    public function test_upload_valid_pdf_file(): void
    {
        $file = UploadedFile::fake()->create('certificate.pdf', 500); // 500 KB

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
        $response->assertSessionHasNoErrors();
=======

        $response->assertSessionHasNoErrors();

        // File harus tersimpan di disk 'local'
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $asset = IntangibleAsset::latest()->first();
        Storage::disk('local')->assertExists($asset->certificate_file);
    }

<<<<<<< HEAD
    public function test_old_file_survives_validation_failure(): void
    {
=======
    /** Poin 17: File lama hilang saat validasi gagal — pastikan tidak ada orphan */
    public function test_old_file_survives_validation_failure(): void
    {
        // Buat aset dengan file
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $file1 = UploadedFile::fake()->create('old.pdf', 500);
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file1,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
        $asset = IntangibleAsset::latest()->first();
        $oldFilePath = $asset->certificate_file;

        $response = $this->actingAs($this->admin)->put(route('intangible-assets.update', $asset), [
            'name' => '',
=======

        $asset = IntangibleAsset::latest()->first();
        $oldFilePath = $asset->certificate_file;

        // Coba update dengan data invalid (tanpa ganti file)
        $response = $this->actingAs($this->admin)->put(route('intangible-assets.update', $asset), [
            'name' => '', // sengaja invalid
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
            'category' => 'Software',
            'price' => 1000000,
            'activation_date' => now()->format('Y-m-d'),
            'license_type' => 'Perpetual',
            'unit_id' => $this->unit->id,
            'status' => 'Aktif',
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]);
<<<<<<< HEAD
        $response->assertSessionHasErrors('name');
        Storage::disk('local')->assertExists($oldFilePath);
    }

    public function test_replace_file_deletes_old_file(): void
    {
=======

        $response->assertSessionHasErrors('name');
        // File lama harus tetap ada
        Storage::disk('local')->assertExists($oldFilePath);
    }

    /** Poin 18: Ganti file — file lama terhapus */
    public function test_replace_file_deletes_old_file(): void
    {
        // Buat aset dengan file1
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $file1 = UploadedFile::fake()->create('old.pdf', 500);
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file1,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $asset = IntangibleAsset::latest()->first();
        $oldPath = $asset->certificate_file;
        Storage::disk('local')->assertExists($oldPath);

<<<<<<< HEAD
=======
        // Update dengan file2
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $file2 = UploadedFile::fake()->create('new.pdf', 600);
        $this->actingAs($this->admin)->put(route('intangible-assets.update', $asset), $this->baseData([
            'certificate_file' => $file2,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
        $asset->refresh();
        $newPath = $asset->certificate_file;
=======

        $asset->refresh();
        $newPath = $asset->certificate_file;

        // File baru ada, file lama hilang
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        Storage::disk('local')->assertExists($newPath);
        Storage::disk('local')->assertMissing($oldPath);
        $this->assertNotEquals($oldPath, $newPath);
    }

<<<<<<< HEAD
    // 🔥 GROUP 3: KODE ASET

=======
    // ============================================================
    // 🔥 GROUP 3: KODE ASET (Poin 19-20)
    // ============================================================

    /** Poin 19: Format kode aset benar */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_asset_code_format(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $asset = IntangibleAsset::latest()->first();
        $year = date('Y');
        $this->assertMatchesRegularExpression("/^NF\/{$year}\/\d{4}$/", $asset->asset_code);
    }

<<<<<<< HEAD
=======
    /** Poin 19: Kode aset increment untuk aset berikutnya */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_asset_code_increments(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test 1', 'license_model' => 'Per User'],
        ]));
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test 2', 'license_model' => 'Per User'],
        ]));
<<<<<<< HEAD
        $assets = IntangibleAsset::orderBy('id')->get();
=======

        $assets = IntangibleAsset::orderBy('id')->get();
        $this->assertEquals($assets[0]->asset_code.'', $assets[0]->asset_code);
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $this->assertStringEndsWith('0001', $assets[0]->asset_code);
        $this->assertStringEndsWith('0002', $assets[1]->asset_code);
    }

<<<<<<< HEAD
=======
    /** Poin 20: Soft delete — kode lama tidak terpakai ulang */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_soft_deleted_asset_code_not_reused(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $firstAsset = IntangibleAsset::latest()->first();
        $firstCode = $firstAsset->asset_code;
<<<<<<< HEAD
        $firstAsset->delete();

=======

        // Soft delete
        $firstAsset->delete();

        // Buat aset baru
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category_data' => ['product_name' => 'Test 2', 'license_model' => 'Per User'],
        ]));
        $newAsset = IntangibleAsset::latest()->first();
<<<<<<< HEAD
        $this->assertNotEquals($firstCode, $newAsset->asset_code);
    }

    // 🔥 GROUP 4: REMINDER

=======

        // Kode baru harus BEDA dari yang di-soft-delete
        $this->assertNotEquals($firstCode, $newAsset->asset_code);
    }

    // ============================================================
    // 🔥 GROUP 4: REMINDER (Poin 21-23)
    // ============================================================

    /** Poin 21: Aset dengan expiry ≤30 hari muncul di reminder */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
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
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $expiring = IntangibleAsset::getExpiringSoonAssets();
        $this->assertCount(1, $expiring);
    }

<<<<<<< HEAD
    public function test_perpetual_not_in_reminder(): void
    {
        IntangibleAsset::forceCreate([
=======
    /** Poin 22: Perpetual tidak muncul di reminder */
    public function test_perpetual_not_in_reminder(): void
    {
        IntangibleAsset::forceCreate([ // 🔥 Ubah create() jadi forceCreate()
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
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
<<<<<<< HEAD
            'vendor' => 'Test Vendor',
        ]);
=======
            'vendor' => 'Test Vendor', // 🔥 Tambahkan vendor
        ]);

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $expiring = IntangibleAsset::getExpiringSoonAssets();
        $this->assertCount(0, $expiring);
    }

<<<<<<< HEAD
    public function test_soft_deleted_not_in_reminder(): void
    {
        $asset = IntangibleAsset::forceCreate([
=======
    /** Poin 23: Soft-deleted tidak muncul di reminder */
    public function test_soft_deleted_not_in_reminder(): void
    {
        $asset = IntangibleAsset::forceCreate([ // 🔥 Ubah create() jadi forceCreate()
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
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
<<<<<<< HEAD
            'vendor' => 'Test Vendor',
        ]);
        $asset->delete();
=======
            'vendor' => 'Test Vendor', // 🔥 Tambahkan vendor
        ]);
        $asset->delete();

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $expiring = IntangibleAsset::getExpiringSoonAssets();
        $this->assertCount(0, $expiring);
    }

<<<<<<< HEAD
    // 🔥 GROUP 5: AUTHORIZATION

    public function test_unauthorized_user_cannot_access(): void
    {
        $user = User::factory()->create(['level' => 'Mahasiswa']);
=======
    // ============================================================
    // 🔥 GROUP 5: AUTHORIZATION (Poin 24-25)
    // ============================================================

    /** Poin 24: User tanpa akses tidak bisa buka halaman */
    public function test_unauthorized_user_cannot_access(): void
    {
        $user = User::factory()->create(['level' => 'Mahasiswa']);

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response = $this->actingAs($user)->get(route('intangible-assets.index'));
        $response->assertStatus(403);
    }

<<<<<<< HEAD
=======
    /** Poin 25: User tanpa auth tidak bisa download dokumen */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_unauthenticated_cannot_download_certificate(): void
    {
        $file = UploadedFile::fake()->create('cert.pdf', 500);
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'certificate_file' => $file,
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
<<<<<<< HEAD
=======

        // Logout dan coba download
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        auth()->logout();
        $response = $this->get(route('intangible-assets.download-certificate', $asset));
        $response->assertRedirect('/login');
    }

<<<<<<< HEAD
    // 🔥 GROUP 6: EDIT & DETAIL

=======
    // ============================================================
    // 🔥 GROUP 6: EDIT & DETAIL (Poin 26-27)
    // ============================================================

    /** Poin 26: Halaman edit memuat data lama dengan benar */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_edit_page_loads_old_data(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'name' => 'Asset Lama',
            'category_data' => ['product_name' => 'Produk Lama', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response = $this->actingAs($this->admin)->get(route('intangible-assets.edit', $asset));
        $response->assertStatus(200);
        $response->assertSee('Asset Lama');
        $response->assertSee('Produk Lama');
    }

<<<<<<< HEAD
    public function test_category_change_handles_data(): void
    {
=======
    /** Poin 27: Perubahan kategori ditangani — data lama terhapus, data baru masuk */
    public function test_category_change_handles_data(): void
    {
        // Buat Software
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Software',
            'category_data' => ['product_name' => 'Software Lama', 'license_model' => 'Per User'],
        ]));
        $asset = IntangibleAsset::latest()->first();
        $this->assertEquals('Software Lama', $asset->category_data['product_name']);

<<<<<<< HEAD
=======
        // Ubah ke HAKI
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
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
<<<<<<< HEAD
        $asset->refresh();
        $this->assertEquals('HAKI/Paten', $asset->category);
        $this->assertEquals('Invensi Baru', $asset->category_data['work_name']);
        $this->assertArrayNotHasKey('product_name', $asset->category_data);
    }

    // 🔥 GROUP 7: FIELD SENSITIF

=======

        $asset->refresh();
        $this->assertEquals('HAKI/Paten', $asset->category);
        $this->assertEquals('Invensi Baru', $asset->category_data['work_name']);
        // Field Software lama harus hilang
        $this->assertArrayNotHasKey('product_name', $asset->category_data);
    }

    // ============================================================
    // 🔥 GROUP 7: FIELD SENSITIF (Poin 28)
    // ============================================================

    /** Poin 28: product_key tidak muncul di halaman daftar */
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function test_product_key_not_visible_in_index(): void
    {
        $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'category' => 'Software',
<<<<<<< HEAD
            'category_data' => ['product_name' => 'Test', 'license_model' => 'Per User', 'product_key' => 'SECRET-KEY-XYZ-123'],
        ]));
=======
            'category_data' => [
                'product_name' => 'Test',
                'license_model' => 'Per User',
                'product_key' => 'SECRET-KEY-XYZ-123',
            ],
        ]));

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response = $this->actingAs($this->admin)->get(route('intangible-assets.index'));
        $response->assertDontSee('SECRET-KEY-XYZ-123');
    }

<<<<<<< HEAD
    // 🔥 GROUP 8: LINK KE PENGADAAN

    public function test_duplicate_link_to_request_prevented(): void
    {
=======
    // ============================================================
    // 🔥 GROUP 8: LINK KE PENGADAAN (Poin 30)
    // ============================================================

    /** Poin 30: Aset yang ditautkan ke request tidak duplikat */
    public function test_duplicate_link_to_request_prevented(): void
    {
        // Buat request dengan 1 item non-fisik
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $request = AssetRequest::create([
            'request_type' => 'aset',
            'requester_id' => $this->admin->id,
            'unit_id' => $this->unit->id,
            'status' => 'Dikonfirmasi',
            'jenis_barang' => 'Tidak Habis Pakai',
            'kategori_barang' => 'Lainnya',
        ]);
<<<<<<< HEAD
        AssetRequestItem::forceCreate([
            'asset_request_id' => $request->id,
            'item_name' => 'Item Non-Fisik 1',
            'item_type' => 'Non-Fisik',
=======

        AssetRequestItem::forceCreate([
            'asset_request_id' => $request->id,
            'item_name' => 'Item Non-Fisik 1',
            'item_type' => 'Non-Fisik', // 🔥 Ubah ke 'Non-Fisik'
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
            'quantity' => 1,
            'approval_status' => 'approved',
        ]);

<<<<<<< HEAD
=======
        // Tautkan aset pertama
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response1 = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'asset_request_id' => $request->id,
            'category_data' => ['product_name' => 'Test 1', 'license_model' => 'Per User'],
        ]));
        $response1->assertSessionHasNoErrors();

<<<<<<< HEAD
=======
        // Coba tautkan aset kedua — harus gagal
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
        $response2 = $this->actingAs($this->admin)->post(route('intangible-assets.store'), $this->baseData([
            'asset_request_id' => $request->id,
            'category_data' => ['product_name' => 'Test 2', 'license_model' => 'Per User'],
        ]));
        $response2->assertSessionHasErrors('asset_request_id');
    }
}
