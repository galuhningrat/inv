<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique(); // 2024/01/KOM-0001
            $table->string('name');
            $table->foreignId('asset_type_id')->constrained()->onDelete('restrict');
            $table->string('brand');
            $table->string('serial_number')->unique();
            $table->decimal('price', 12, 2);
            $table->date('purchase_date');
            $table->string('location');
            $table->enum('condition', ['Baik', 'Rusak Ringan', 'Rusak Berat'])->default('Baik');
            $table->enum('status', ['Tersedia', 'Dipinjam', 'Maintenance'])->default('Tersedia');
            $table->string('image')->nullable();
            $table->string('qr_code')->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index('asset_id');
            $table->index('serial_number');
            $table->index('qr_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};