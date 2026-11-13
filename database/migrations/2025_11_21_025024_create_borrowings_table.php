<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->string('borrowing_id')->unique(); // BRW-001
            $table->foreignId('asset_id')->constrained()->onDelete('restrict');
            $table->string('borrower_name');
            $table->enum('borrower_role', ['Dosen', 'Mahasiswa', 'Staff', 'Karyawan']);
            $table->date('borrow_date');
            $table->date('return_date');
            $table->date('actual_return_date')->nullable();
            $table->text('purpose');
            $table->enum('status', ['Aktif', 'Selesai', 'Terlambat'])->default('Aktif');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('borrowing_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};