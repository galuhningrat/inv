<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_batches', function (Blueprint $table) {
            // Mengubah kolom brand agar boleh kosong (nullable)
            $table->string('brand')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('asset_batches', function (Blueprint $table) {
            $table->string('brand')->nullable(false)->change();
        });
    }
};
