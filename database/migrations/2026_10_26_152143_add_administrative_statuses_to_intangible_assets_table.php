<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // 🔥 PostgreSQL: gunakan ALTER COLUMN ... TYPE
            DB::statement('ALTER TABLE intangible_assets ALTER COLUMN status TYPE VARCHAR(50)');
            DB::statement("ALTER TABLE intangible_assets ALTER COLUMN status SET DEFAULT 'Aktif'");
        } elseif ($driver === 'mysql') {
            // MySQL: gunakan MODIFY
            DB::statement("ALTER TABLE intangible_assets MODIFY status VARCHAR(50) DEFAULT 'Aktif'");
        }
        // SQLite: kolom status di SQLite tidak bisa di-ALTER mudah,
        // tapi biasanya di SQLite sudah VARCHAR by default.
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // Kembalikan ke VARCHAR(50) default (tidak revert ke ENUM karena riskan data)
            DB::statement('ALTER TABLE intangible_assets ALTER COLUMN status TYPE VARCHAR(50)');
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE intangible_assets MODIFY status ENUM('Aktif', 'Kadaluarsa', 'Nonaktif') DEFAULT 'Aktif'");
        }
    }
};
