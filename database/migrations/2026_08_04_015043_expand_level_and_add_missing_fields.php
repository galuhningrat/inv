<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Perluas ENUM level di users
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_level_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_level_check CHECK (level IN (
            'Admin','Sarpras','Keuangan','Kaprodi','Rektor',
            'PJ Pengadaan','Kalab','Aslab','Tim Pemeliharaan','Administrasi'
        ))");

        // 2. Tambah penanggung_jawab di assets
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('penanggung_jawab_id')->nullable()
                ->after('location')
                ->constrained('users')->onDelete('set null');
        });

        // 3. Tambah kondisi_dipinjam & kondisi_kembali di borrowings
        Schema::table('borrowings', function (Blueprint $table) {
            $table->enum('kondisi_dipinjam', ['Baik', 'Rusak Ringan', 'Rusak Berat'])
                ->default('Baik')->after('asset_id');
            $table->enum('kondisi_kembali', ['Baik', 'Rusak Ringan', 'Rusak Berat'])
                ->nullable()->after('actual_return_date');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_level_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_level_check CHECK (level IN (
            'Admin','Sarpras','Keuangan','Kaprodi','Rektor'
        ))");

        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penanggung_jawab_id');
        });

        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn(['kondisi_dipinjam', 'kondisi_kembali']);
        });
    }
};
