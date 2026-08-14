<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetRequest;

class AssignUnitSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset semua unit_id ke null dulu (bersihkan assignment sebelumnya)
        User::whereNotNull('unit_id')->update(['unit_id' => null]);
        Asset::whereNotNull('unit_id')->update(['unit_id' => null]);
        AssetRequest::whereNotNull('unit_id')->update(['unit_id' => null]);

        // 2. Ambil ID unit
        $umum = Unit::where('name', 'Umum')->first()->id;
        $ti = Unit::where('name', 'Teknik Informatika')->first()->id;
        $labkom = Unit::where('name', 'Laboratorium Komputer')->first()->id;

        // 3. Assign USERS
        // Kaprodi → Teknik Informatika
        User::where('id', 4)->update(['unit_id' => $ti]);

        // Galuh & Siswo T → Lab Komputer
        User::whereIn('id', [6, 8])->update(['unit_id' => $labkom]);

        // Admin, Sarpras, Rektor, Keuangan, PJ Pengadaan, Tim Pemeliharaan, Administrasi → Umum
        User::whereIn('id', [1, 2, 3, 5, 7, 9, 10])->update(['unit_id' => $umum]);

        // 4. Assign ASSETS
        // "Ruang IT" → Teknik Informatika
        Asset::where('location', 'Ruang IT')->update(['unit_id' => $ti]);

        // "lab TI", "LABKOM", "Laboratorium", "Ruang Server", "Toolbox", "Lemari Komponen", "Laci Biru A" → Lab Komputer
        Asset::where('location', 'LIKE', '%lab TI%')
            ->orWhere('location', 'LIKE', '%LABKOM%')
            ->orWhere('location', 'Laboratorium')
            ->orWhere('location', 'Ruang Server')
            ->orWhere('location', 'Toolbox')
            ->orWhere('location', 'Lemari Komponen')
            ->orWhere('location', 'Laci Biru A')
            ->update(['unit_id' => $labkom]);

        // Sisanya → Umum
        Asset::whereNull('unit_id')->update(['unit_id' => $umum]);

        // 5. Assign REQUESTS
        AssetRequest::whereIn('request_id', ['REQ-001', 'REQ-003'])->update(['unit_id' => $labkom]);
        AssetRequest::where('request_id', 'REQ-002')->update(['unit_id' => $umum]);

        // 6. Output verifikasi
        $this->info('=== UNIT ASSIGNMENT SELESAI ===');
        $this->info('Users:');
        foreach (User::with('unit')->get() as $user) {
            $this->line("  {$user->name} → " . ($user->unit->name ?? 'Tidak ada unit'));
        }

        $this->info("\nAssets per unit:");
        foreach (Unit::all() as $unit) {
            $count = Asset::where('unit_id', $unit->id)->count();
            if ($count > 0) {
                $this->line("  {$unit->name}: {$count} aset");
            }
        }

        $this->info("\nRequests:");
        foreach (AssetRequest::with('unit')->get() as $req) {
            $this->line("  {$req->request_id} → " . ($req->unit->name ?? 'Tidak ada unit'));
        }
    }
}