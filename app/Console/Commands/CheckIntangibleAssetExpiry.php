<?php

namespace App\Console\Commands;

use App\Models\IntangibleAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckIntangibleAssetExpiry extends Command
{
    protected $signature = 'intangible:check-expiry';

    protected $description = 'Update status aset non-fisik yang sudah melewati tanggal kedaluwarsa';

    public function handle()
    {
        $expired = IntangibleAsset::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->startOfDay()->toDateString())
            ->where('status', 'Aktif')
            ->update(['status' => 'Kadaluarsa']);

        $this->info("✅ {$expired} aset ditandai sebagai Kadaluarsa.");
        Log::info("Intangible asset expiry check: {$expired} assets marked as Kadaluarsa.");

        return Command::SUCCESS;
    }
}
