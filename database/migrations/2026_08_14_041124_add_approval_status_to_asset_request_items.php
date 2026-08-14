<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->string('approval_status')->default('pending')->after('estimated_price_per_unit');
            $table->text('approval_notes')->nullable()->after('approval_status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_notes');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('rolled_from_item_id')->nullable()->after('approved_at');

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rolled_from_item_id')->references('id')->on('asset_request_items')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rolled_from_item_id']);
            $table->dropColumn(['approval_status', 'approval_notes', 'approved_by', 'approved_at', 'rolled_from_item_id']);
        });
    }
};