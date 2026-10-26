<?php

namespace App\Console\Commands;

use App\Models\IntangibleAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MigrateLegacyCertificates extends Command
{
    protected $signature = 'intangible:migrate-certificates {--dry-run : Tampilkan rencana tanpa mengeksekusi}';

    protected $description = 'Pindahkan file sertifikat lama dari disk public ke disk local (privat)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $assets = IntangibleAsset::whereNotNull('certificate_file')->get();

        $this->info("Total aset dengan file: {$assets->count()}");
        if ($dryRun) {
            $this->warn('Mode DRY-RUN — tidak ada file yang akan dipindahkan.');
        }

        $stats = ['moved' => 0, 'skipped' => 0, 'missing' => 0, 'failed' => 0];

        foreach ($assets as $asset) {
            $path = $asset->certificate_file;

            // Sudah ada di local disk → skip
            if (Storage::disk('local')->exists($path)) {
                $stats['skipped']++;
                $this->line("⏭️  SKIP  [{$asset->asset_code}] {$path}");

                continue;
            }

            // Tidak ada di public disk → tandai missing
            if (! Storage::disk('public')->exists($path)) {
                $stats['missing']++;
                $this->warn("❓ MISS  [{$asset->asset_code}] {$path}");

                continue;
            }

            if ($dryRun) {
                $stats['moved']++;
                $this->info("📦 WOULD MOVE [{$asset->asset_code}] {$path}");

                continue;
            }

            try {
                // Copy content, then delete from public
                $content = Storage::disk('public')->get($path);
                Storage::disk('local')->put($path, $content);
                Storage::disk('public')->delete($path);

                $stats['moved']++;
                $this->info("✅ MOVED [{$asset->asset_code}] {$path}");
            } catch (\Exception $e) {
                $stats['failed']++;
                $this->error("❌ FAIL  [{$asset->asset_code}] {$path} — {$e->getMessage()}");
                Log::error("Failed to migrate certificate: {$path}", ['error' => $e->getMessage()]);
            }
        }

        $this->newLine();
        $this->table(
            ['Status', 'Jumlah'],
            [
                ['Moved',   $stats['moved']],
                ['Skipped', $stats['skipped']],
                ['Missing', $stats['missing']],
                ['Failed',  $stats['failed']],
            ]
        );

        if (! $dryRun && $stats['moved'] > 0) {
            $this->info('Migrasi selesai. Jalankan ulang perintah ini untuk memverifikasi.');
        }
        if ($stats['failed'] > 0) {
            $this->warn('⚠️  Ada file yang gagal — cek log aplikasi untuk detail.');
        }

        return self::SUCCESS;
    }
}
