<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('rollover_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_item_id');
            $table->unsignedBigInteger('new_item_id');
            $table->integer('source_month');
            $table->integer('source_year');
            $table->integer('target_month');
            $table->integer('target_year');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('original_item_id')->references('id')->on('asset_request_items')->onDelete('cascade');
            $table->foreign('new_item_id')->references('id')->on('asset_request_items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rollover_logs');
    }
};