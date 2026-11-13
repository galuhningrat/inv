<?php

namespace App\Console\Commands;

use App\Models\AssetBatch;
use Illuminate\Console\Command;

class MigrateSubkategoriAset extends Command
{
    protected $signature = 'sinadas:migrate-subkategori';

    protected $description = 'Migrasi subkategori lama (MEB, FUR, ATK) ke opsi baru yang lebih spesifik';

    public function handle()
    {
        $this->info('Memulai migrasi data subkategori...');

        // 1. Migrasi FUR (Furnitur Custom)
        $furBatches = AssetBatch::whereHas('assetType', fn ($q) => $q->where('code', 'FUR'))->get();
        $furCount = 0;
        foreach ($furBatches as $batch) {
            $data = $batch->category_data ?? [];
            if (isset($data['subkategori'])) {
                $old = $data['subkategori'];
                if ($old === 'meja') {
                    $data['subkategori'] = 'meja_custom';
                } elseif ($old === 'lemari') {
                    $data['subkategori'] = 'lemari_builtin';
                } else {
                    $data['subkategori'] = 'lainnya';
                }

                $batch->category_data = $data;
                $batch->save();
                $furCount++;
            }
        }
        $this->info("✅ {$furCount} data FUR berhasil dimigrasi.");

        // 2. Migrasi ATK (Perkakas & Komponen)
        $atkBatches = AssetBatch::whereHas('assetType', fn ($q) => $q->where('code', 'ATK'))->get();
        $atkCount = 0;
        foreach ($atkBatches as $batch) {
            $data = $batch->category_data ?? [];
            if (isset($data['subkategori'])) {
                $old = $data['subkategori'];
                // Jika sebelumnya 'atk' (alat tulis), ubah ke 'lainnya' karena kita fokus ke perkakas/komponen
                if ($old === 'atk') {
                    $data['subkategori'] = 'lainnya';
                }

                $batch->category_data = $data;
                $batch->save();
                $atkCount++;
            }
        }
        $this->info("✅ {$atkCount} data ATK berhasil dimigrasi.");

        $this->info('🎉 Migrasi selesai! Semua data lama aman dan sudah disesuaikan.');
    }
}
