<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus constraint lama
        DB::statement('ALTER TABLE intangible_assets DROP CONSTRAINT IF EXISTS intangible_assets_license_type_check');
        // Ubah tipe data menjadi VARCHAR(50) agar fleksibel
        DB::statement('ALTER TABLE intangible_assets ALTER COLUMN license_type TYPE VARCHAR(50)');
    }

    public function down(): void
    {
        // Tidak perlu rollback karena hanya constraint lama
    }
};
