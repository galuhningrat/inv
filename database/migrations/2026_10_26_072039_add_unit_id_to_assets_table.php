<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Cek dulu agar tidak error jika kolomnya sudah ada (misal di DB lokal)
            if (! Schema::hasColumn('assets', 'unit_id')) {
                // Menambahkan foreign key unit_id
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
        });
    }
};
