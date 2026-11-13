<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Unit;
use App\Models\QrCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ElektroMesinDummySeeder extends Seeder
{
    public function run(): void
    {
        // Ambil unit
        $elektro = Unit::where('name', 'Teknik Elektro')->first();
        $mesin = Unit::where('name', 'Teknik Mesin')->first();
        $labElektro = Unit::where('name', 'Laboratorium Elektro')->first();
        $labMesin = Unit::where('name', 'Laboratorium Mesin')->first();
        $elkType = AssetType::where('code', 'ELK')->first();

        // Pastikan semua unit dan type ada
        if (!$elektro || !$mesin || !$labElektro || !$labMesin || !$elkType) {
            $this->command->error('⚠️ Unit atau AssetType tidak ditemukan! Jalankan UnitSeeder dan AssetTypeSeeder dulu.');
            return;
        }

        // ==========================================
        // 1. BUAT USER DUMMY (Kaprodi & Kalab)
        // ==========================================
        User::create([
            'name' => 'Dummy Kaprodi Elektro',
            'username' => 'kaprodielektro',
            'email' => 'kaprodi.elektro@stti.ac.id',
            'password' => Hash::make('password'),
            'level' => 'Kaprodi',
            'status' => 'Aktif',
            'unit_id' => $elektro->id,
        ]);

        User::create([
            'name' => 'Dummy Kalab Elektro',
            'username' => 'kalabelektro',
            'email' => 'kalab.elektro@stti.ac.id',
            'password' => Hash::make('password'),
            'level' => 'Kalab',
            'status' => 'Aktif',
            'unit_id' => $labElektro->id,
        ]);

        User::create([
            'name' => 'Dummy Kaprodi Mesin',
            'username' => 'kaprodimesin',
            'email' => 'kaprodi.mesin@stti.ac.id',
            'password' => Hash::make('password'),
            'level' => 'Kaprodi',
            'status' => 'Aktif',
            'unit_id' => $mesin->id,
        ]);

        User::create([
            'name' => 'Dummy Kalab Mesin',
            'username' => 'kalabmesin',
            'email' => 'kalab.mesin@stti.ac.id',
            'password' => Hash::make('password'),
            'level' => 'Kalab',
            'status' => 'Aktif',
            'unit_id' => $labMesin->id,
        ]);

        $this->command->info('✅ 4 user dummy berhasil dibuat (Kaprodi & Kalab Elektro/Mesin)');

        // ==========================================
        // 2. BUAT ASET DUMMY (2 per lab) + QR CODE
        // ==========================================
        $assetCounter = 1;

        // Data lab dengan prefix untuk serial number
        $labs = [
            ['unit' => $labElektro, 'prefix' => 'ELK'],
            ['unit' => $labMesin, 'prefix' => 'MES'],
        ];

        foreach ($labs as $labData) {
            $lab = $labData['unit'];
            $prefix = $labData['prefix'];

            for ($i = 1; $i <= 2; $i++) {
                // 🔥 GENERATE QR CODE UNIK
                $qrCode = $this->generateUniqueQrCode();

                // 🔥 GENERATE ASSET ID
                $year = date('Y');
                $month = date('m');
                $assetId = sprintf('%s/%s/%s-%04d', $year, $month, $prefix, $assetCounter);

                // 🔥 GENERATE SERIAL NUMBER UNIK (per lab dan per index)
                $serialNumber = sprintf('DUMMY-%s-%d', $prefix, $i);

                // 🔥 CREATE ASSET
                $asset = Asset::create([
                    'name' => "Osiloskop Dummy {$lab->name} #{$i}",
                    'asset_type_id' => $elkType->id,
                    'brand' => 'Dummy Brand',
                    'serial_number' => $serialNumber, // ✅ UNIK
                    'price' => 5000000,
                    'purchase_date' => now(),
                    'location' => $lab->name,
                    'condition' => 'Baik',
                    'status' => 'Tersedia',
                    'unit_id' => $lab->id,
                    'asset_id' => $assetId,
                    'qr_code' => $qrCode,
                ]);

                // 🔥 CREATE QR CODE RECORD
                QrCode::create([
                    'qr_code_id' => 'QCD-' . str_pad($asset->id, 3, '0', STR_PAD_LEFT),
                    'asset_id' => $asset->id,
                    'code_content' => $qrCode,
                    'status' => 'Aktif',
                ]);

                $this->command->info("✅ Aset dibuat: {$asset->name} (SN: {$serialNumber}, QR: {$qrCode})");
                $assetCounter++;
            }
        }

        $this->command->info("\n🎉 Seeder selesai! User dummy dan aset berhasil dibuat.");
        $this->command->info("🔑 Password semua user dummy: 'password'");
        $this->command->info("📊 Total aset baru: 4 (2 di Lab Elektro, 2 di Lab Mesin)");
    }

    /**
     * Generate QR code unik
     */
    private function generateUniqueQrCode(): string
    {
        $attempts = 0;
        do {
            $timestamp = base_convert(time() + $attempts, 10, 36);
            $random = strtoupper(Str::random(6));
            $qrCode = "DUM-{$timestamp}-{$random}";

            $exists = Asset::where('qr_code', $qrCode)->exists();
            $attempts++;
        } while ($exists && $attempts < 10);

        return $qrCode;
    }
}