<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('light_receipt_logs', function (Blueprint $table) {
            $table->id();

            // Relasi ke item pengajuan (jika item dihapus, log ikut terhapus)
            $table->foreignId('asset_request_item_id')
                ->constrained('asset_request_items')
                ->cascadeOnDelete();

            // Jumlah yang diterima pada transaksi ini
            $table->integer('received_quantity');

            // Catatan dari Sarpras (opsional)
            $table->text('notes')->nullable();

            // Path file bukti penerimaan (opsional)
            $table->string('proof_file')->nullable();

            // Siapa yang menerima (Sarpras/Admin)
            $table->foreignId('received_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('light_receipt_logs');
    }
};
