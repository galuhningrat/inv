<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intangible_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->enum('category', ['Software', 'HAKI/Paten', 'Jurnal Ilmiah', 'Domain/Hosting', 'Kurikulum']);
            $table->string('vendor');
            $table->decimal('price', 12, 2);
            $table->date('activation_date');
            $table->string('funding_source')->nullable();
            $table->string('contract_number')->nullable();
            $table->enum('license_type', ['Berlangganan', 'Selamanya']);
            $table->date('expiry_date')->nullable();
            $table->integer('reminder_days')->nullable();
            $table->string('quota')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->string('access_url')->nullable();
            $table->string('certificate_file')->nullable();
            $table->enum('status', ['Aktif', 'Kadaluarsa', 'Nonaktif'])->default('Aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intangible_assets');
    }
};