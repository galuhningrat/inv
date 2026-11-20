<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_request_item_id')->constrained()->onDelete('cascade');

            // Harga dari vendor ini spesifik untuk item ini
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2); // (unit_price * qty) - discount + tax

            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_offer_items');
    }
};
