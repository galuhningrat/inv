<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Klasifikasi "Habis Pakai / Tidak Habis Pakai / Jasa" pindah ke level ITEM
        //    (di-chain dari item_type — lihat AssetRequestItem::SIFAT_BARANG_FISIK /
        //    SIFAT_BARANG_NON_FISIK), sesuai alasan yang sama dengan kenapa
        //    kategori_barang sudah dipindah lebih dulu: satu pengajuan sekarang bisa
        //    berisi banyak item, jadi satu nilai untuk seluruh pengajuan tidak lagi
        //    representatif — dan field ini juga tidak pernah dicek di receive() untuk
        //    membedakan perlakuan (semua item diproses lewat alur Aset Tetap penuh
        //    apa pun jenis_barang-nya).
        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->string('sifat_barang')->nullable()->after('item_type');

            // Kolom untuk registrasi ringan (Opsi A / graceful degradation) khusus
            // item Habis Pakai (Fisik) & Jasa (Non-Fisik): tidak masuk tabel
            // assets/intangible_assets, cukup dicatat jumlah yang diterima + bukti
            // opsional. Tidak dibuat tabel baru (consumable_receipts dsb.) karena
            // ini bukan modul stok — tidak ada tracking barang keluar, cuma catatan
            // penerimaan satu kali.
            $table->integer('received_quantity')->nullable()->after('category');
            $table->text('receipt_notes')->nullable()->after('received_quantity');
            $table->string('receipt_proof_file')->nullable()->after('receipt_notes');
        });

        // 2. Retire jenis_barang di header, sama seperti kategori_barang sebelumnya
        //    (lihat migration 2026_09_04_000000_make_kategori_barang_nullable.php).
        //    Kolom TIDAK di-drop — data historis (REQ-001 dst.) tetap utuh. CHECK
        //    constraint juga tidak disentuh: NULL otomatis lolos dari constraint enum.
        DB::statement('ALTER TABLE asset_requests ALTER COLUMN jenis_barang DROP NOT NULL');
        DB::statement('ALTER TABLE asset_requests ALTER COLUMN jenis_barang DROP DEFAULT');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE asset_requests ALTER COLUMN jenis_barang SET DEFAULT 'Tidak Habis Pakai'");
        DB::table('asset_requests')->whereNull('jenis_barang')->update(['jenis_barang' => 'Tidak Habis Pakai']);
        DB::statement('ALTER TABLE asset_requests ALTER COLUMN jenis_barang SET NOT NULL');

        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->dropColumn(['sifat_barang', 'received_quantity', 'receipt_notes', 'receipt_proof_file']);
        });
    }
};


