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
            $table->foreignId('disbursed_by')->nullable()
                ->after('confirmation_notes')->constrained('users')->onDelete('set null');
            $table->timestamp('disbursed_at')->nullable()->after('disbursed_by');
            $table->text('disbursement_notes')->nullable()->after('disbursed_at');
        });

        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check CHECK (status IN ('Pending','Diverifikasi','Disetujui','Dana Cair','Dikonfirmasi','Ditolak','Diterima'))");
    }

    public function down(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropForeign(['disbursed_by']);
            $table->dropColumn(['disbursed_by', 'disbursed_at', 'disbursement_notes']);
        });

        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check");
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check CHECK (status IN ('Pending','Diverifikasi','Disetujui','Dikonfirmasi','Ditolak','Diterima'))");
    }
};