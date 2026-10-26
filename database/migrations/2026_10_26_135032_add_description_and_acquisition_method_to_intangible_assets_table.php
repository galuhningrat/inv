<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intangible_assets', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('acquisition_method')->nullable()->after('price'); // Pembelian, Hibah, Pengembangan Internal, dll
        });
    }

    public function down(): void
    {
        Schema::table('intangible_assets', function (Blueprint $table) {
            $table->dropColumn(['description', 'acquisition_method']);
        });
    }
};
