<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique(); // REQ-001
            $table->foreignId('requester_id')->constrained('users')->onDelete('restrict');
            $table->string('asset_name');
            $table->foreignId('asset_type_id')->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('estimated_price', 12, 2)->nullable();
            $table->enum('priority', ['Rendah', 'Sedang', 'Tinggi', 'Urgent'])->default('Sedang');
            $table->text('reason');
            $table->enum('status', ['Pending', 'Disetujui', 'Ditolak'])->default('Pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('request_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_requests');
    }
};