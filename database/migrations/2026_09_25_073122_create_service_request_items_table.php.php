<?php

// database/migrations/2026_09_25_000003_create_service_request_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_request_id')
                ->constrained('asset_requests')
                ->cascadeOnDelete();

            // Sama seperti item aset: unit & prioritas per item
            $table->foreignId('unit_id')->constrained('units');
            $table->string('priority', 50);
            $table->string('alasan_pengajuan', 100)->nullable();
            $table->text('reason')->nullable();

            // Kategori jasa
            $table->string('service_category', 50);

            // Deskripsi umum (dipakai semua sub-kategori jasa)
            $table->string('item_name');
            $table->text('specification')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('estimated_price_per_unit', 15, 2)->nullable();

            // Field variabel per sub-kategori — lihat dokumentasi skema di bawah
            $table->json('service_data')->nullable();

            // Approval workflow (sama seperti AssetRequestItem)
            $table->string('approval_status', 20)->default('pending');
            $table->text('approval_notes')->nullable();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Rollover support (paralel dengan AssetRequestItem)
            $table->foreignId('rolled_from_item_id')->nullable()
                ->constrained('service_request_items')->nullOnDelete();

            // File pendukung (mis. BAST, proposal, penawaran vendor)
            $table->string('attachment_file')->nullable();

            $table->timestamps();

            // Index untuk reviewer grouping & filter kategori
            $table->index(['asset_request_id', 'unit_id'], 'sri_request_unit_idx');
            $table->index(['asset_request_id', 'service_category'], 'sri_request_cat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_items');
    }
};
