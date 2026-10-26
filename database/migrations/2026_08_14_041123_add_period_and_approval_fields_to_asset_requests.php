<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Pastikan kolom unit_id ada terlebih dahulu
        Schema::table('asset_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_requests', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            }
        });

        // 2. Tambahkan kolom period_month dan period_year
        Schema::table('asset_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_requests', 'period_month')) {
                $table->integer('period_month')->nullable();
            }
            if (! Schema::hasColumn('asset_requests', 'period_year')) {
                $table->integer('period_year')->nullable();
            }
        });

        // 3. Baru tambahkan unique constraint setelah kolomnya lengkap
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->unique(['unit_id', 'period_month', 'period_year'], 'unique_request_per_month');
        });
    }

    public function down()
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropUnique('unique_request_per_month');
            $table->dropColumn(['period_month', 'period_year']);
        });
    }
};
