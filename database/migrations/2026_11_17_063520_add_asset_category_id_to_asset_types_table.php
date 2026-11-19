<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom relasi
        Schema::table('asset_types', function (Blueprint $table) {
            $table->foreignId('asset_category_id')->nullable()->after('id')->constrained('asset_categories')->nullOnDelete();
        });

        // 2. Insert 9 Kategori Utama
        $categories = [
            ['code' => 'FUR', 'name' => 'Furniture', 'description' => 'Perabotan dan furnitur kampus'],
            ['code' => 'EQU', 'name' => 'Equipment', 'description' => 'Peralatan elektronik dan perkakas'],
            ['code' => 'ATK', 'name' => 'Alat Tulis Kantor', 'description' => 'ATK dan perlengkapan administrasi'],
            ['code' => 'MES', 'name' => 'Mesin', 'description' => 'Mesin-mesin operasional'],
            ['code' => 'KEN', 'name' => 'Kendaraan', 'description' => 'Kendaraan operasional'],
            ['code' => 'BNG', 'name' => 'Bangunan', 'description' => 'Gedung, tanah, dan prasarana'],
            ['code' => 'KSC', 'name' => 'Komponen & Suku Cadang Habis Pakai', 'description' => 'Suku cadang dan habis pakai'],
            ['code' => 'SPB', 'name' => 'Sarana Pembelajaran', 'description' => 'Sarana penunjang akademik'],
            ['code' => 'ARS', 'name' => 'Arsip', 'description' => 'Dokumen dan arsip institusi'],
        ];
        DB::table('asset_categories')->insert($categories);

        // 3. Mapping Subkategori (AssetType) ke Kategori Utama (AssetCategory)
        $map = [
            'KOM' => 'SPB', // Komputer
            'JAR' => 'SPB', // Jaringan
            'LAB' => 'SPB', // Lab
            'KPB' => 'SPB', // Koleksi Perpustakaan (Baru)
            'ELK' => 'EQU', // Elektronik
            'APK' => 'EQU', // Alat & Perkakas (Ganti dari ATK lama)
            'MEB' => 'FUR', // Mebel
            'FUR' => 'FUR', // Furnitur Custom
            'OTO' => 'KEN', // Kendaraan
            'TNH' => 'BNG', // Tanah
            'GDG' => 'BNG', // Gedung
            'PRS' => 'BNG', // Prasarana
            'ARS' => 'ARS', // Arsip
            'ATK' => 'ATK', // Alat Tulis Kantor
            'LAN' => 'SPB', // Lainnya (bisa disesuaikan)
        ];

        foreach ($map as $typeCode => $catCode) {
            $catId = DB::table('asset_categories')->where('code', $catCode)->value('id');
            DB::table('asset_types')->where('code', $typeCode)->update(['asset_category_id' => $catId]);
        }

        // 4. Perbaikan Data Khusus: Ganti "ATK" lama menjadi "APK" (Alat & Perkakas)
        DB::table('asset_types')->where('code', 'ATK')->where('name', 'Alat & Perkakas')->update([
            'code' => 'APK',
            'name' => 'Alat & Perkakas',
            'asset_category_id' => DB::table('asset_categories')->where('code', 'EQU')->value('id')
        ]);

        // 5. Tambah "ATK" Baru (Alat Tulis Kantor)
        DB::table('asset_types')->insert([
            'code' => 'ATK',
            'name' => 'Alat Tulis Kantor',
            'description' => 'Pena, kertas, map, dll',
            'asset_category_id' => DB::table('asset_categories')->where('code', 'ATK')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Pisahkan KPB dan ARS (Mengubah nama yang lama jika ada)
        DB::table('asset_types')->where('code', 'ARS')->where('name', 'Koleksi Perpustakaan & Arsip')->update([
            'name' => 'Arsip',
        ]);

        // Tambah KPB jika belum ada
        if (!DB::table('asset_types')->where('code', 'KPB')->exists()) {
            DB::table('asset_types')->insert([
                'code' => 'KPB',
                'name' => 'Koleksi Perpustakaan',
                'description' => 'Buku, jurnal, manuskrip',
                'asset_category_id' => DB::table('asset_categories')->where('code', 'SPB')->value('id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->dropForeign(['asset_category_id']);
            $table->dropColumn('asset_category_id');
        });
    }
};
