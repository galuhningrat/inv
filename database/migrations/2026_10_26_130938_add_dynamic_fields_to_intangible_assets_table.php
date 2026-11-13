<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intangible_assets', function (Blueprint $table) {
            // Menyimpan atribut khusus kategori (JSON)
            $table->json('category_data')->nullable()->after('category');

            // Memisahkan model pembayaran dari jenis lisensi
            $table->string('billing_model')->nullable()->after('license_type');

            // Enkripsi product_key (akan ditangani oleh cast di model, tapi tipe datanya perlu diubah ke text untuk menampung string terenkripsi yang panjang)
            $table->text('product_key')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('intangible_assets', function (Blueprint $table) {
            $table->dropColumn(['category_data', 'billing_model']);
            $table->string('product_key')->nullable()->change();
        });
    }
};
