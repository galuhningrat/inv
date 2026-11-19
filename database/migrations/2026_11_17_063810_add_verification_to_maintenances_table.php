<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreignId('verified_by')->nullable()->after('recorded_by')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verified_at');
        });

        // Update Status Enum (Ganti CHECK constraint)
        DB::statement("ALTER TABLE maintenances DROP CONSTRAINT IF EXISTS maintenances_status_check");
        DB::statement("ALTER TABLE maintenances ADD CONSTRAINT maintenances_status_check CHECK (status IN ('Menunggu Verifikasi', 'Diterima', 'Dalam Proses', 'Menunggu Komponen', 'Selesai'))");
        DB::statement("ALTER TABLE maintenances ALTER COLUMN status SET DEFAULT 'Menunggu Verifikasi'");
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_by', 'verified_at', 'verification_notes']);
        });

        DB::statement("ALTER TABLE maintenances DROP CONSTRAINT IF EXISTS maintenances_status_check");
        DB::statement("ALTER TABLE maintenances ADD CONSTRAINT maintenances_status_check CHECK (status IN ('Diterima', 'Dalam Proses', 'Menunggu Komponen', 'Selesai'))");
    }
};
