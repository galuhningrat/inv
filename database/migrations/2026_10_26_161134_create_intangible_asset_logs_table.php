<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intangible_asset_logs', function (Blueprint $table) {
            $table->id();
            // Tanpa FK constraint agar log tetap tersimpan meski aset/user force-deleted
            $table->unsignedBigInteger('intangible_asset_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // created, updated, deleted
            $table->json('changes')->nullable(); // {field: {label, old, new}}
            $table->string('description'); // human-readable
            $table->timestamps();

            $table->index('intangible_asset_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intangible_asset_logs');
    }
};
