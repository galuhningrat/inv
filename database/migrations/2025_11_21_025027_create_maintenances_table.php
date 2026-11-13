<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_id')->unique(); // MNT-001
            $table->foreignId('asset_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['Preventif', 'Corrective', 'Predictive', 'Emergency']);
            $table->date('maintenance_date');
            $table->decimal('cost', 12, 2);
            $table->text('description');
            $table->string('technician');
            $table->enum('status', ['Pending', 'Dalam Proses', 'Selesai'])->default('Selesai');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index('maintenance_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};