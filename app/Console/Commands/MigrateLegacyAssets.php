<?php

namespace App\Console\Commands;

use App\Models\AssetBatch;
use App\Models\AssetType;
use Illuminate\Console\Command;

class MigrateLegacyAssets extends Command
{
    // Jalankan dengan: php artisan sinadas:migrate-legacy-assets --dry-run
    protected $signature = 'sinadas:migrate-legacy-assets {--dry-run : Tampilkan kandidat tanpa mengubah data}';
    protected $description = 'Migrasi data lama (Koleksi Perpustakaan & Arsip) ke kategori baru';

    public function handle()
    {
        $this->info('🔍 Mencari data lama dengan tipe ARS (Arsip)...');

        $arsType = AssetType::where('code', 'ARS')->first();
        $kpbType = AssetType::where('code', 'KPB')->first();

        if (!$arsType || !$kpbType) {
            $this->error('❌ Tipe ARS atau KPB tidak ditemukan di database. Pastikan migrasi sudah dijalankan.');
            return;
        }

        // Ambil semua batch yang tipenya masih ARS
        $batches = AssetBatch::where('asset_type_id', $arsType->id)->get();

        if ($batches->isEmpty()) {
            $this->info('✅ Tidak ada data lama yang perlu dimigrasi. Semua bersih!');
            return;
        }

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('⚠️ MODE DRY-RUN: Tidak ada data yang akan diubah.');
        }

        $migratedCount = 0;
        $manualReview = [];

        foreach ($batches as $batch) {
            $name = strtolower($batch->name);
            $categoryData = strtolower(json_encode($batch->category_data ?? []));

            // Heuristik sederhana: Cek kata kunci buku/jurnal di nama atau metadata
            $isPerpustakaan = str_contains($name, 'buku') ||
                str_contains($name, 'jurnal') ||
                str_contains($name, 'pustaka') ||
                str_contains($categoryData, 'isbn') ||
                str_contains($categoryData, 'penulis') ||
                str_contains($categoryData, 'penerbit');

            if ($isPerpustakaan) {
                $this->line("📦 [PERPUSTAKAAN] -> Memindahkan: {$batch->name}");
                if (!$isDryRun) {
                    $batch->update(['asset_type_id' => $kpbType->id]);
                }
                $migratedCount++;
            } else {
                // Jika tidak ada kata kunci, jangan ditebak. Masukkan ke daftar manual.
                $manualReview[] = $batch->name;
            }
        }

        $this->newLine();
        $this->info("📊 RINGKASAN:");
        $this->info("   Total Batch ARS Lama: {$batches->count()}");
        $this->info("   Berhasil dipindah ke KPB: {$migratedCount}");
        $this->info("   Perlu Tinjauan Manual: " . count($manualReview));

        if (count($manualReview) > 0) {
            $this->warn("\n⚠️ DAFTAR TINJAUAN MANUAL (Tidak dipindah otomatis):");
            foreach ($manualReview as $name) {
                $this->line("   - {$name}");
            }
            $this->line("\nSilakan ubah tipe aset di menu Master Data jika barang tersebut memang Koleksi Perpustakaan.");
        }

        if (!$isDryRun && $migratedCount > 0) {
            $this->info("\n✅ Migrasi selesai! Data berhasil dipindahkan.");
        }
    }
}
