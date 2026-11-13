<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_level_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_level_check CHECK (level IN (
            'Admin','Sarpras','Keuangan','Kaprodi','Rektor',
            'PJ Pengadaan','Kalab','Aslab','Tim Pemeliharaan','Administrasi',
            'Karyawan','Mahasiswa'
        ))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_level_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_level_check CHECK (level IN (
            'Admin','Sarpras','Keuangan','Kaprodi','Rektor',
            'PJ Pengadaan','Kalab','Aslab','Tim Pemeliharaan','Administrasi'
        ))");
    }
};