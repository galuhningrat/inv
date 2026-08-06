<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('asset_request_id')->nullable()
                ->after('penanggung_jawab_id')
                ->constrained('asset_requests')->onDelete('set null');
        });

        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check");
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check CHECK (status IN ('Pending','Disetujui','Ditolak','Diterima'))");
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_request_id');
        });

        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check");
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check CHECK (status IN ('Pending','Disetujui','Ditolak'))");
    }
};
