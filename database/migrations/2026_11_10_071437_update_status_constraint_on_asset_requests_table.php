<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus aturan (constraint) yang lama
        DB::statement('ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check');

        // 2. Buat aturan baru yang mengizinkan 'Diterima Sebagian'
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check
            CHECK (status IN ('Pending', 'Diverifikasi', 'Disetujui', 'Ditolak', 'Dana Cair', 'Dikonfirmasi', 'Diterima', 'Diterima Sebagian', 'Selesai'))");
    }

    public function down(): void
    {
        // Kembalikan ke aturan lama jika migrasi di-rollback
        DB::table('asset_requests')->where('status', 'Diterima Sebagian')->update(['status' => 'Dikonfirmasi']);

        DB::statement('ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check');
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check
            CHECK (status IN ('Pending', 'Diverifikasi', 'Disetujui', 'Ditolak', 'Dana Cair', 'Dikonfirmasi', 'Diterima', 'Selesai'))");
    }
};
