<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code_id')->unique(); // QCD-001
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->string('code_content')->unique();
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index('qr_code_id');
            $table->index('code_content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};