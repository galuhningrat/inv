<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tambah kolom kategori (khusus item Non-Fisik) di level ITEM, bukan di
        //    header asset_requests. Sebelum ini, kategori non-fisik baru ditentukan
        //    Sarpras saat registrasi (AssetRequestController::receive()) — pemohon
        //    di form pengajuan tidak pernah diminta memilihnya, sehingga kolom
        //    "Jenis Aset" di halaman Detail Pengajuan selalu tampil "-" untuk item
        //    Non-Fisik. Kolom ini dibiarkan nullable karena tidak relevan untuk item
        //    Fisik (yang klasifikasinya memakai asset_type_id).
        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->string('category')->nullable()->after('specification');
        });

        // 2. Perluas daftar kategori aset non-fisik. Sebelumnya hanya 5 nilai tetap
        //    (Software, HAKI/Paten, Jurnal Ilmiah, Domain/Hosting, Kurikulum) lewat
        //    CHECK constraint di kolom intangible_assets.category, sehingga hal-hal
        //    umum seperti lisensi cloud/SaaS, akun digital bernama pengguna, dan
        //    sertifikat digital tidak punya kategori yang pas dan berakhir masuk ke
        //    "Software" atau "Lainnya".
        DB::statement("ALTER TABLE intangible_assets DROP CONSTRAINT IF EXISTS intangible_assets_category_check");
        DB::statement("ALTER TABLE intangible_assets ADD CONSTRAINT intangible_assets_category_check
            CHECK (category IN ('Software','Cloud/SaaS','Akun Digital','Domain/Hosting','Jurnal Ilmiah','HAKI/Paten','Sertifikat Digital','Kurikulum','Lainnya'))");
    }

    public function down(): void
    {
        // Constraint lama tidak mengenal 'Lainnya' sama sekali, jadi semua nilai baru
        // (termasuk 'Lainnya') dipetakan balik ke 'Software' sebagai fallback paling
        // aman supaya tidak melanggar constraint lama.
        DB::table('intangible_assets')
            ->whereIn('category', ['Cloud/SaaS', 'Akun Digital', 'Sertifikat Digital', 'Lainnya'])
            ->update(['category' => 'Software']);

        DB::statement("ALTER TABLE intangible_assets DROP CONSTRAINT IF EXISTS intangible_assets_category_check");
        DB::statement("ALTER TABLE intangible_assets ADD CONSTRAINT intangible_assets_category_check
            CHECK (category IN ('Software','HAKI/Paten','Jurnal Ilmiah','Domain/Hosting','Kurikulum'))");

        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->dropColumn('category');
            });
    }
};