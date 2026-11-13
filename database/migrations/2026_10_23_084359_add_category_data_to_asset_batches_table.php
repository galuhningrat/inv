<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_batches', function (Blueprint $table) {
            // Kolom JSON untuk menyimpan field dinamis berdasarkan kategori
            $table->json('category_data')->nullable()->after('residual_value');
        });

        Schema::table('assets', function (Blueprint $table) {
            // Kolom JSON untuk field dinamis tingkat unit (jika ada)
            $table->json('unit_data')->nullable()->after('location_detail');

            // Kita longgarkan serial_number agar boleh null untuk TNH/GDG
            $table->string('serial_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('asset_batches', function (Blueprint $table) {
            $table->dropColumn('category_data');
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('unit_data');
            $table->string('serial_number')->nullable(false)->change();
        });
    }
};
