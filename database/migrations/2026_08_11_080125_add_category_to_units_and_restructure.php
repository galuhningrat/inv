<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type');
        });

        // Label kategori untuk unit yang sudah ada (Prodi & Lab)
        DB::table('units')->whereIn('name', ['Teknik Mesin', 'Teknik Elektro', 'Teknik Informatika'])
            ->update(['category' => 'Akademik']);
        DB::table('units')->whereIn('name', ['Laboratorium Komputer', 'Laboratorium Elektro', 'Laboratorium Mesin'])
            ->update(['category' => 'Akademik']);

        // Hapus 5 unit generik yang saya buat minggu lalu (cascade otomatis hapus location anak-anaknya)
        DB::table('units')->whereIn('name', [
            'Rektorat',
            'Unit IT & Jaringan',
            'Fasilitas Umum',
            'Pelayanan Mahasiswa',
            'Unit Kerja Administratif'
        ])->delete();
    }

    public function down(): void
    {
        Schema::table('units', fn(Blueprint $t) => $t->dropColumn('category'));
    }
};