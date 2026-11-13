<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Status lama 'Nonaktif' tidak ada di daftar status baru.
        // Nilai 'Dihentikan' adalah padanan yang paling dekat secara makna
        // (aset tidak lagi digunakan secara permanen).
        DB::table('intangible_assets')
            ->where('status', 'Nonaktif')
            ->update(['status' => 'Dihentikan']);

        $total = DB::table('intangible_assets')
            ->where('status', 'Dihentikan')
            ->count();
        \Log::info("Migration normalized {$total} legacy status values.");
    }

    public function down(): void
    {
        // Sama seperti migration license_type, down() sengaja NO-OP.
        // Rollback hanya via restore backup.
    }
};
