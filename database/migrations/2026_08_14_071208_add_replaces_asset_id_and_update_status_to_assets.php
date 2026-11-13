<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('replaces_asset_id')->nullable()->after('asset_request_id');
            $table->foreign('replaces_asset_id')->references('id')->on('assets')->onDelete('set null');
        });

        DB::statement("ALTER TABLE assets ALTER COLUMN status TYPE VARCHAR(50) USING status::VARCHAR(50)");
        DB::statement("ALTER TABLE assets ALTER COLUMN status SET DEFAULT 'Tersedia'");

        DB::table('assets')->whereNull('status')->update(['status' => 'Tersedia']);
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['replaces_asset_id']);
            $table->dropColumn('replaces_asset_id');
        });

        DB::statement("ALTER TABLE assets ALTER COLUMN status TYPE VARCHAR(50)");
    }
};