<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Field akuntansi tambahan — semua nullable/opsional. Tidak ada satu pun
        // yang diwajibkan di form: "Sumber Pendanaan" sempat diusulkan sebagai
        // wajib (*) di draf revisi, tapi berbeda dari penghapusan lock bulanan
        // kemarin yang punya konfirmasi langsung dari PJ Pengadaan, tidak ada
        // konfirmasi serupa untuk field ini — jadi dibuat opsional dulu supaya
        // tidak memaksa isian yang belum tentu benar-benar diperlukan, dan supaya
        // ~30+ aset yang sudah ada tidak tiba-tiba "kurang data wajib".
        //
        // "Dua Nomor Seri" dari draf revisi TIDAK menambah kolom baru di sini —
        // serial_number yang sudah ada cukup direlabel di UI jadi "Nomor Seri
        // Pabrik", dan asset_id yang sudah auto-generate ditampilkan sebagai
        // "Label Aset Kampus". Menambah kolom baru untuk hal yang sudah
        // terwakili itu cuma duplikasi data.
        Schema::table('assets', function (Blueprint $table) {
            $table->string('model')->nullable()->after('brand');
            $table->string('funding_source')->nullable()->after('price');
            $table->unsignedSmallInteger('economic_life_years')->nullable()->after('funding_source');
            $table->decimal('residual_value', 15, 2)->nullable()->after('economic_life_years');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['model', 'funding_source', 'economic_life_years', 'residual_value']);
        });
    }
};
