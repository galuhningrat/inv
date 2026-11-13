<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_request_items', function (Blueprint $table) {
            $table->enum('item_type', ['Fisik', 'Non-Fisik'])->default('Fisik')->after('asset_type_id');
        });

        DB::statement('ALTER TABLE asset_request_items ALTER COLUMN asset_type_id DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE asset_request_items SET asset_type_id = (SELECT id FROM asset_types WHERE code = 'LAN' LIMIT 1) WHERE asset_type_id IS NULL");
        DB::statement('ALTER TABLE asset_request_items ALTER COLUMN asset_type_id SET NOT NULL');
        Schema::table('asset_request_items', fn(Blueprint $t) => $t->dropColumn('item_type'));
    }
};