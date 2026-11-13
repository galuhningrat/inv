<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('intangible_assets', function (Blueprint $table) {
            $table->foreignId('pic_id')->nullable()->after('unit_id')->constrained('users')->onDelete('set null');
            $table->string('product_key')->nullable()->after('quota');
            $table->string('assigned_user_email')->nullable()->after('product_key');
            $table->foreignId('asset_request_id')->nullable()->after('created_by')->constrained('asset_requests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('intangible_assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pic_id');
            $table->dropConstrainedForeignId('asset_request_id');
            $table->dropColumn(['product_key', 'assigned_user_email']);
        });
    }
};