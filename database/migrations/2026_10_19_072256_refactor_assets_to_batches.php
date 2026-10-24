<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel baru: asset_batches
        Schema::create('asset_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_type_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('brand');
            $table->string('model')->nullable();
            $table->decimal('price', 12, 2);
            $table->date('purchase_date');
            $table->string('funding_source')->nullable();
            $table->integer('economic_life_years')->nullable();
            $table->decimal('residual_value', 12, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tambah foreign key ke tabel assets
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('asset_batch_id')->nullable()->after('id')->constrained('asset_batches')->nullOnDelete();
        });

        // 3. MIGRASI DATA LAMA (Pindahkan data dari assets ke asset_batches)
        $existingAssets = DB::table('assets')->get();
        $batches = [];

        foreach ($existingAssets as $asset) {
            $batchKey = $asset->name . '|' . $asset->brand . '|' . $asset->asset_type_id . '|' . $asset->price;

            if (!isset($batches[$batchKey])) {
                $batchId = DB::table('asset_batches')->insertGetId([
                    'asset_type_id' => $asset->asset_type_id,
                    'name' => $asset->name,
                    'brand' => $asset->brand,
                    'model' => $asset->model ?? null,
                    'price' => $asset->price,
                    'purchase_date' => $asset->purchase_date,
                    'funding_source' => $asset->funding_source ?? null,
                    'economic_life_years' => $asset->economic_life_years ?? null,
                    'residual_value' => $asset->residual_value ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $batches[$batchKey] = $batchId;
            }

            DB::table('assets')->where('id', $asset->id)->update([
                'asset_batch_id' => $batches[$batchKey]
            ]);
        }

        // 4. Hapus kolom yang sudah pindah ke asset_batches
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'asset_type_id',
                'brand',
                'model',
                'price',
                'funding_source',
                'economic_life_years',
                'residual_value',
                'purchase_date'
            ]);
        });
    }

    public function down(): void
    {
        // Rollback logic (opsional)
    }
};
