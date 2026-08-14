<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE borrowings DROP CONSTRAINT IF EXISTS borrowings_status_check");

        Schema::table('borrowings', function (Blueprint $table) {
            $table->foreignId('kalab_approved_by')->nullable()->after('approved_by')->constrained('users')->onDelete('set null');
            $table->timestamp('kalab_approved_at')->nullable()->after('kalab_approved_by');
            $table->text('kalab_rejection_notes')->nullable()->after('kalab_approved_at');
        });

        DB::statement("ALTER TABLE borrowings ADD CONSTRAINT borrowings_status_check CHECK (status IN ('Menunggu Persetujuan Kalab','Aktif','Selesai','Terlambat','Ditolak'))");
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropForeign(['kalab_approved_by']);
            $table->dropColumn(['kalab_approved_by', 'kalab_approved_at', 'kalab_rejection_notes']);
        });

        DB::statement("ALTER TABLE borrowings DROP CONSTRAINT IF EXISTS borrowings_status_check");
        DB::statement("ALTER TABLE borrowings ADD CONSTRAINT borrowings_status_check CHECK (status IN ('Aktif','Selesai','Terlambat'))");
    }
};