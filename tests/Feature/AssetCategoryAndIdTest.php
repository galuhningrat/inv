<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetBatch;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssetCategoryAndIdTest extends TestCase
{
    use DatabaseTransactions;

    protected User $sarpras;

    protected function setUp(): void
    {
        parent::setUp();
        $unit = Unit::firstOrCreate(
            ['name' => 'Test Sarpras Unit'],
            ['category' => 'Sarpras', 'asset_id_code' => 'UM']
        );
        $this->sarpras = User::factory()->create(['level' => 'Sarpras', 'unit_id' => $unit->id]);
    }

    // ============================================================
    //  GROUP 1 — KATEGORI UTAMA (9 KATEGORI)
    // ============================================================

    public function test_nine_main_categories_exist(): void
    {
        $expectedCodes = ['FUR', 'EQU', 'ATK', 'MES', 'KEN', 'BNG', 'KSC', 'SPB', 'ARS'];

        foreach ($expectedCodes as $code) {
            $this->assertDatabaseHas('asset_categories', ['code' => $code]);
        }
    }

    public function test_all_category_codes_are_exactly_three_letters(): void
    {
        foreach (AssetCategory::all() as $cat) {
            $this->assertEquals(3, strlen($cat->code), "Kode {$cat->name} harus 3 huruf, dapat: {$cat->code}");
            $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $cat->code);
        }
    }

    public function test_atk_category_is_named_alat_tulis_kantor(): void
    {
        $atk = AssetCategory::where('code', 'ATK')->first();
        $this->assertNotNull($atk, 'Kategori ATK harus ada');
        $this->assertEquals('Alat Tulis Kantor', $atk->name);
    }

    public function test_ars_category_is_independent(): void
    {
        $ars = AssetCategory::where('code', 'ARS')->first();
        $this->assertNotNull($ars, 'Kategori ARS harus ada');
        $this->assertEquals('Arsip', $ars->name);
    }

    public function test_category_has_types_relation(): void
    {
        $spb = AssetCategory::where('code', 'SPB')->first();
        $this->assertNotNull($spb);
        $this->assertGreaterThan(0, $spb->types()->count(), 'SPB harus punya subkategori');
    }

    // ============================================================
    //  GROUP 2 — SUBKATEGORI (ASSET TYPES)
    // ============================================================

    public function test_atk_subcategory_is_alat_tulis_kantor(): void
    {
        $type = AssetType::where('code', 'ATK')->first();
        $this->assertNotNull($type, 'Subkategori ATK harus ada');
        $this->assertEquals('Alat Tulis Kantor', $type->name);
        $this->assertNotNull($type->category);
        $this->assertEquals('ATK', $type->category->code);
    }

    public function test_apk_subcategory_is_alat_perkakas(): void
    {
        $type = AssetType::where('code', 'APK')->first();
        $this->assertNotNull($type, 'Subkategori APK harus ada');
        $this->assertEquals('Alat & Perkakas', $type->name);
        $this->assertNotNull($type->category);
        $this->assertEquals('EQU', $type->category->code);
    }

    public function test_old_atk_as_alat_perkakas_no_longer_exists(): void
    {
        $oldAtk = AssetType::where('code', 'ATK')
            ->where('name', 'Alat & Perkakas')
            ->first();

        $this->assertNull($oldAtk, 'Kode ATK tidak boleh lagi dipakai untuk Alat & Perkakas');
    }

    public function test_kpb_and_ars_are_separate(): void
    {
        $kpb = AssetType::where('code', 'KPB')->first();
        $ars = AssetType::where('code', 'ARS')->first();

        $this->assertNotNull($kpb, 'KPB (Koleksi Perpustakaan) harus ada');
        $this->assertNotNull($ars, 'ARS (Arsip) harus ada');
        $this->assertNotEquals($kpb->id, $ars->id, 'KPB dan ARS harus record berbeda');
        $this->assertEquals('Koleksi Perpustakaan', $kpb->name);
        $this->assertEquals('Arsip', $ars->name);
    }

    public function test_no_merged_koleksi_perpustakaan_arsip_type(): void
    {
        $merged = AssetType::where('name', 'LIKE', '%Koleksi Perpustakaan%Arsip%')
            ->orWhere('name', 'LIKE', '%Arsip%Koleksi Perpustakaan%')
            ->get();

        $this->assertCount(0, $merged, 'Tidak boleh ada subkategori gabungan Koleksi Perpustakaan & Arsip');
    }

    public function test_known_subcategories_mapped_to_correct_category(): void
    {
        $expectedMap = [
            'KOM' => 'SPB',
            'JAR' => 'SPB',
            'LAB' => 'SPB',
            'KPB' => 'SPB',
            'ELK' => 'EQU',
            'APK' => 'EQU',
            'FUR' => 'FUR',
            'OTO' => 'KEN',
            'TNH' => 'BNG',
            'GDG' => 'BNG',
            'PRS' => 'BNG',
            'ARS' => 'ARS',
            'ATK' => 'ATK',
        ];

        foreach ($expectedMap as $typeCode => $catCode) {
            $type = AssetType::where('code', $typeCode)->first();
            if ($type) {
                $this->assertNotNull($type->asset_category_id, "{$typeCode} harus punya kategori");
                $this->assertEquals($catCode, $type->category->code, "{$typeCode} harus di bawah kategori {$catCode}");
            }
        }
    }

    // ============================================================
    //  GROUP 3 — KODE ID UNIT
    // ============================================================

    public function test_units_table_has_asset_id_code_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('units', 'asset_id_code'),
            'Kolom asset_id_code harus ada di tabel units'
        );
    }

    public function test_prodi_units_have_correct_asset_id_code(): void
    {
        $expected = [
            'Teknik Informatika' => 'TI',
            'Teknik Elektro' => 'TE',
            'Teknik Mesin' => 'TM',
        ];

        foreach ($expected as $name => $code) {
            $unit = Unit::where('name', $name)->first();
            if ($unit) {
                $this->assertEquals($code, $unit->asset_id_code, "Unit {$name} harus kode {$code}");
            }
        }
    }

    public function test_non_prodi_units_default_to_um(): void
    {
        $nonProdi = Unit::whereNotIn('name', ['Teknik Informatika', 'Teknik Elektro', 'Teknik Mesin'])
            ->whereNotNull('asset_id_code')
            ->first();

        if ($nonProdi) {
            $this->assertEquals('UM', $nonProdi->asset_id_code, 'Unit non-prodi harus UM');
        }
    }

    // ============================================================
    //  HELPER — Untuk group 4
    // ============================================================

    private function getOrCreateEquType(): AssetType
    {
        $category = AssetCategory::where('code', 'EQU')->first();
        $type = AssetType::where('code', 'ELK')->first();

        if (! $type) {
            $type = AssetType::create([
                'code' => 'ELK',
                'name' => 'Elektronik Umum',
                'asset_category_id' => $category->id,
            ]);
        }

        return $type;
    }

    private function makeBatch(AssetType $type): AssetBatch
    {
        return AssetBatch::create([
            'asset_type_id' => $type->id,
            'name' => 'Test Batch ' . uniqid(),
            'brand' => 'Test Brand',
            'price' => 1000000,
            'purchase_date' => now(),
        ]);
    }

    private function makeUnit(string $name, string $assetIdCode): Unit
    {
        return Unit::firstOrCreate(
            ['name' => $name],
            ['category' => 'Test', 'asset_id_code' => $assetIdCode]
        );
    }

    private function createAssetIn(AssetBatch $batch, Unit $unit): Asset
    {
        return Asset::create([
            'asset_batch_id' => $batch->id,
            'unit_id' => $unit->id,
            'serial_number' => 'SN-' . uniqid(),
            'location' => 'Test Location',
            'condition' => 'Baik',
            'status' => 'Tersedia',
            'qr_code' => 'QR-' . uniqid(),
        ]);
    }

    // ============================================================
    //  GROUP 4 — GENERATOR ID ASET
    // ============================================================

    public function test_generated_id_matches_correct_format(): void
    {
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unit = $this->makeUnit('Test Unit Format', 'TI');

        $asset = $this->createAssetIn($batch, $unit);

        // Format: PREFIX/YEAR/KODEUNIT/0001
        $this->assertMatchesRegularExpression(
            '/^[A-Z]{3}\/\d{4}\/[A-Z]{2}\/\d{4}$/',
            $asset->asset_id,
            "Format ID harus PREFIX/YEAR/UNIT/0001, dapat: {$asset->asset_id}"
        );
    }

    public function test_generated_id_uses_category_prefix_not_subcategory(): void
    {
        // ELK subkategori ada di bawah EQU kategori
        // Prefix yang dipakai harus EQU, BUKAN ELK
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unit = $this->makeUnit('Test Unit Prefix', 'TI');

        $asset = $this->createAssetIn($batch, $unit);

        $this->assertStringStartsWith('EQU/', $asset->asset_id);
        $this->assertStringNotContainsString('/ELK/', $asset->asset_id);
    }

    public function test_generated_id_uses_unit_asset_id_code(): void
    {
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unit = $this->makeUnit('Test Unit TM', 'TM');

        $asset = $this->createAssetIn($batch, $unit);

        $parts = explode('/', $asset->asset_id);
        $this->assertCount(4, $parts);
        $this->assertEquals('TM', $parts[2]);
    }

    public function test_generated_id_uses_current_year(): void
    {
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unit = $this->makeUnit('Test Unit Year', 'UM');

        $asset = $this->createAssetIn($batch, $unit);

        $parts = explode('/', $asset->asset_id);
        $this->assertEquals(date('Y'), $parts[1]);
    }

    public function test_sequential_numbering_increments_correctly(): void
    {
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unit = $this->makeUnit('Test Unit Seq', 'UM');

        $prefix = 'EQU/' . date('Y') . '/UM/';

        // Cek nomor terakhir yang sudah ada di DB
        $lastExisting = Asset::withTrashed()
            ->where('asset_id', 'LIKE', $prefix . '%')
            ->orderBy('asset_id', 'desc')
            ->first();
        $existingNum = $lastExisting ? (int) substr($lastExisting->asset_id, -4) : 0;

        // Buat 2 aset baru
        $asset1 = $this->createAssetIn($batch, $unit);
        $asset2 = $this->createAssetIn($batch, $unit);

        $num1 = (int) substr($asset1->asset_id, -4);
        $num2 = (int) substr($asset2->asset_id, -4);

        $this->assertEquals($existingNum + 1, $num1, 'Aset pertama harus num+1 dari existing');
        $this->assertEquals($existingNum + 2, $num2, 'Aset kedua harus num+2 dari existing');
    }

    public function test_no_duplicate_ids_across_multiple_creates(): void
    {
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unit = $this->makeUnit('Test Unit Dup', 'UM');

        $ids = [];
        for ($i = 0; $i < 5; $i++) {
            $asset = $this->createAssetIn($batch, $unit);
            $ids[] = $asset->asset_id;
        }

        $this->assertCount(5, $ids);
        $this->assertCount(5, array_unique($ids), 'Tidak boleh ada ID duplikat. Ditemukan: ' . implode(', ', $ids));
    }

    public function test_different_units_have_independent_sequences(): void
    {
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unitTI = $this->makeUnit('Test Unit Indep TI', 'TI');
        $unitTE = $this->makeUnit('Test Unit Indep TE', 'TE');

        $assetTI = $this->createAssetIn($batch, $unitTI);
        $assetTE = $this->createAssetIn($batch, $unitTE);

        // Prefix harus beda
        $this->assertStringContainsString('/TI/', $assetTI->asset_id);
        $this->assertStringContainsString('/TE/', $assetTE->asset_id);
        $this->assertNotEquals($assetTI->asset_id, $assetTE->asset_id);
    }

    public function test_old_format_pattern_is_not_used_anymore(): void
    {
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unit = $this->makeUnit('Test Unit OldFmt', 'UM');

        $asset = $this->createAssetIn($batch, $unit);

        // Format lama punya: TAHUN/BULAN/UMUM/KODE/NAMA-0001
        $this->assertStringNotContainsString('UMUM', $asset->asset_id);
        $this->assertStringNotContainsString('//', $asset->asset_id, 'Tidak boleh ada double slash (bug lama)');
        $this->assertStringNotContainsString('LAPTOP', $asset->asset_id);

        // Pastikan pemisahnya `/` dan ada 4 segmen
        $parts = explode('/', $asset->asset_id);
        $this->assertCount(4, $parts, "ID harus punya 4 segmen, dapat: {$asset->asset_id}");
    }

    public function test_asset_id_uniqueness_enforced_by_database(): void
    {
        $batch = $this->makeBatch($this->getOrCreateEquType());
        $unit = $this->makeUnit('Test Unit Uniq', 'UM');

        $asset1 = $this->createAssetIn($batch, $unit);

        // Coba buat asset lain dengan ID yang sama — harus ditolak
        $this->expectException(\Illuminate\Database\QueryException::class);

        Asset::forceCreate([
            'asset_id' => $asset1->asset_id, // Duplikat!
            'asset_batch_id' => $batch->id,
            'unit_id' => $unit->id,
            'serial_number' => 'SN-' . uniqid(),
            'location' => 'Test',
            'condition' => 'Baik',
            'status' => 'Tersedia',
            'qr_code' => 'QR-' . uniqid(),
        ]);
    }

    public function test_direct_generator_returns_correct_pattern(): void
    {
        $type = $this->getOrCreateEquType();
        $unit = $this->makeUnit('Test Direct Gen', 'TI');

        $id = Asset::generateAssetId($type->id, $unit->id);

        $this->assertMatchesRegularExpression(
            '/^EQU\/' . date('Y') . '\/TI\/\d{4}$/',
            $id,
            "Direct generator harus return EQU/YYYY/TI/XXXX, dapat: {$id}"
        );
    }

    public function test_generator_handles_unit_with_null_asset_id_code(): void
    {
        // Edge case: unit ada tapi asset_id_code null (mis. unit lama)
        // Generator harus fallback ke UM, bukan crash atau hasilkan //
        $type = $this->getOrCreateEquType();
        $unit = Unit::create([
            'name' => 'Test Unit NullCode ' . uniqid(),
            'category' => 'Test',
            'asset_id_code' => null,
        ]);

        $id = Asset::generateAssetId($type->id, $unit->id);

        $this->assertStringNotContainsString('//', $id, 'Tidak boleh ada double slash saat asset_id_code null');
        $this->assertMatchesRegularExpression(
            '/^EQU\/' . date('Y') . '\/UM\/\d{4}$/',
            $id,
            "Fallback harus ke UM, dapat: {$id}"
        );
    }
}
