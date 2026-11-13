<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            // Tipe dokumen: apakah sesi submit ini untuk aset atau jasa
            $table->string('request_type', 20)->default('aset')->after('request_id');

            // Sub-kategori jasa (hanya terisi bila request_type = 'jasa')
            // Nilai: pemeliharaan | instalasi | pelatihan | sewa | konsultasi
            $table->string('service_category', 50)->nullable()->after('request_type');

            $table->index(['request_type', 'service_category'], 'ar_type_cat_idx');
        });

        // Header tidak lagi menyimpan unit/prioritas/alasan — pindah ke item.
        // Di-nullable-kan (bukan di-drop) supaya data historis tetap utuh
        // dan view lama tidak error.
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->change();
            $table->string('priority', 50)->nullable()->change();
            $table->string('alasan_pengajuan', 100)->nullable()->change();
            $table->text('reason')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropIndex('ar_type_cat_idx');
            $table->dropColumn(['request_type', 'service_category']);
        });
    }
};
