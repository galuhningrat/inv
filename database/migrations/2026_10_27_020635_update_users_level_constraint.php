<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus CHECK constraint lama pada kolom 'level'
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_level_check');

        // 2. (Opsional) Ubah tipe data kolom menjadi VARCHAR(50) agar lebih fleksibel
        // Ini mencegah error serupa di masa depan jika ada penambahan jabatan baru.
        DB::statement('ALTER TABLE users ALTER COLUMN level TYPE VARCHAR(50)');
    }

    public function down(): void
    {
        // Tidak perlu melakukan rollback karena kita hanya melonggarkan aturan
    }
};
