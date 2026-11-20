<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_request_id')->constrained()->onDelete('cascade');
            $table->string('vendor_name');
            $table->string('vendor_contact')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->string('offer_number')->nullable();
            $table->date('offer_date');
            $table->date('valid_until')->nullable();

            // Keuangan
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->string('quotation_file')->nullable();

            // Status: Draft, Diajukan, Dipilih, Ditolak
            $table->string('status')->default('Draft');

            $table->foreignId('submitted_by')->constrained('users')->onDelete('restrict');

            // Field Penetapan Vendor
            $table->boolean('is_selected')->default(false);
            $table->text('selection_reason')->nullable(); // Wajib jika bukan termurah
            $table->foreignId('selected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('selected_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_offers');
    }
};
