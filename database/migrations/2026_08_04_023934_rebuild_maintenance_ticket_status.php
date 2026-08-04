<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Migrasi data type lama -> baru SEBELUM ubah constraint
        DB::table('maintenances')->where('type', 'Corrective')->update(['type' => 'Kuratif']);
        DB::table('maintenances')->where('type', 'Predictive')->update(['type' => 'Preventif']);
        DB::table('maintenances')->where('type', 'Emergency')->update(['type' => 'Emergensi']);

        // 2. Migrasi data status lama -> baru
        DB::table('maintenances')->where('status', 'Pending')->update(['status' => 'Diterima']);
        // 'Dalam Proses' dan 'Selesai' namanya sudah cocok, tidak perlu diubah

        // 3. Ganti constraint type
        DB::statement("ALTER TABLE maintenances DROP CONSTRAINT IF EXISTS maintenances_type_check");
        DB::statement("ALTER TABLE maintenances ADD CONSTRAINT maintenances_type_check CHECK (type IN ('Preventif','Kuratif','Emergensi'))");

        // 4. Ganti constraint status
        DB::statement("ALTER TABLE maintenances DROP CONSTRAINT IF EXISTS maintenances_status_check");
        DB::statement("ALTER TABLE maintenances ADD CONSTRAINT maintenances_status_check CHECK (status IN ('Diterima','Dalam Proses','Menunggu Komponen','Selesai'))");
        DB::statement("ALTER TABLE maintenances ALTER COLUMN status SET DEFAULT 'Diterima'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE maintenances DROP CONSTRAINT IF EXISTS maintenances_type_check");
        DB::statement("ALTER TABLE maintenances ADD CONSTRAINT maintenances_type_check CHECK (type IN ('Preventif','Corrective','Predictive','Emergency'))");

        DB::statement("ALTER TABLE maintenances DROP CONSTRAINT IF EXISTS maintenances_status_check");
        DB::statement("ALTER TABLE maintenances ADD CONSTRAINT maintenances_status_check CHECK (status IN ('Pending','Dalam Proses','Selesai'))");
        DB::statement("ALTER TABLE maintenances ALTER COLUMN status SET DEFAULT 'Selesai'");
    }
};