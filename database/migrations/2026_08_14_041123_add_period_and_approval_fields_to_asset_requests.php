<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->integer('period_month')->nullable()->after('unit_id');
            $table->integer('period_year')->nullable()->after('period_month');
        });

        // Tambahkan unique constraint (1 request per unit per bulan)
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