<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->foreignId('unit_id')
                ->nullable()
                ->after('asset_request_id')
                ->constrained('units')
                ->nullOnDelete();

            $table->string('priority', 50)->nullable()->after('unit_id');
            $table->string('alasan_pengajuan', 100)->nullable()->after('priority');
            $table->text('reason')->nullable()->after('alasan_pengajuan');
            $table->foreignId('related_asset_id')
                ->nullable()
                ->after('reason')
                ->constrained('assets')
                ->nullOnDelete();

            // Index untuk reviewer grouping by unit
            $table->index(['asset_request_id', 'unit_id'], 'ari_request_unit_idx');
        });
    }

    public function down(): void
    {
        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->dropIndex('ari_request_unit_idx');
            $table->dropConstrainedForeignId('related_asset_id');
            $table->dropColumn(['unit_id', 'priority', 'alasan_pengajuan', 'reason']);
        });
    }
};
