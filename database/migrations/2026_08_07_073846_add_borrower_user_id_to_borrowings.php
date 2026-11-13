<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->foreignId('borrower_user_id')->nullable()
                ->after('borrower_name')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', fn(Blueprint $t) => $t->dropConstrainedForeignId('borrower_user_id'));
    }
};