<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_request_items', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('approved_at');
            $table->text('completion_notes')->nullable()->after('completed_at');
            $table->string('bast_file')->nullable()->after('completion_notes');
            $table->string('executor')->nullable()->after('bast_file');       // vendor / penyedia / pelaksana
            $table->decimal('actual_cost', 15, 2)->nullable()->after('executor'); // biaya real (opsional)
        });
    }

    public function down(): void
    {
        Schema::table('service_request_items', function (Blueprint $table) {
            $table->dropColumn(['completed_at', 'completion_notes', 'bast_file', 'executor', 'actual_cost']);
        });
    }
};
