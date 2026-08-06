<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check");

        Schema::table('asset_requests', function (Blueprint $table) {
            $table->foreignId('confirmed_by')->nullable()
                ->after('approval_notes')->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            $table->text('confirmation_notes')->nullable()->after('confirmed_at');
        });

        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check CHECK (status IN ('Pending','Diverifikasi','Disetujui','Dikonfirmasi','Ditolak','Diterima'))");

        // Tambah kolom gambar per item
        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->string('image')->nullable()->after('estimated_price_per_unit');
        });
    }

    public function down(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['confirmed_by', 'confirmed_at', 'confirmation_notes']);
        });

        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check");
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check CHECK (status IN ('Pending','Diverifikasi','Disetujui','Ditolak','Diterima'))");
    }
};
