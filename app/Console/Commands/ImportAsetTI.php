<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetBatch;
use App\Models\AssetType;
use App\Models\QrCode;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportAsetTI extends Command
{
    protected $signature = 'import:aset-ti';

    protected $description = 'Import data Aset TI dari file CSV ke database SINADAS';

    public function handle()
    {
        // 1. Safety Check
        $currentDatabase = DB::connection()->getDatabaseName();
        $this->info("🔗 Menghubungkan ke database: {$currentDatabase}");

        if (str_contains($currentDatabase, 'testing')) {
            $this->error('❌ BERHENTI! Anda sedang terhubung ke database TESTING.');

            return;
        }

        if (! $this->confirm("Apakah Anda yakin ingin meng-import data ke database {$currentDatabase}?")) {
            return;
        }

        $filePath = storage_path('app/aset_ti.csv');
        if (! file_exists($filePath)) {
            $this->error("❌ File tidak ditemukan di: {$filePath}");

            return;
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return;
        }

        // 2. Pastikan Unit Tersedia (Kita buat dua unit jika belum ada)
        $unitTI = Unit::firstOrCreate(
            ['name' => 'Teknik Informatika'],
            ['category' => 'Akademik', 'type' => 'prodi']
        );
        $unitLab = Unit::firstOrCreate(
            ['name' => 'Laboratorium Komputer'],
            ['category' => 'Akademik', 'type' => 'laboratorium']
        );

        $types = [
            'KOM' => AssetType::firstOrCreate(['code' => 'KOM'], ['name' => 'Komputer & Periferal']),
            'JAR' => AssetType::firstOrCreate(['code' => 'JAR'], ['name' => 'Jaringan']),
            'MEB' => AssetType::firstOrCreate(['code' => 'MEB'], ['name' => 'Mebel']),
            'ELK' => AssetType::firstOrCreate(['code' => 'ELK'], ['name' => 'Elektronik']),
            'ATK' => AssetType::firstOrCreate(['code' => 'ATK'], ['name' => 'Alat Tulis & Perkakas']),
            'LAN' => AssetType::firstOrCreate(['code' => 'LAN'], ['name' => 'Lainnya']),
            'LAB' => AssetType::firstOrCreate(['code' => 'LAB'], ['name' => 'Laboratorium']),
        ];

        $row = 0;
        $success = 0;
        $skip = 0;

        while (($data = fgetcsv($handle, 10000, ',')) !== false) {
            $row++;
            if ($row <= 4 || empty(trim($data[0] ?? '')) || empty(trim($data[2] ?? ''))) {
                continue;
            }

            try {
                $kodeBarang = trim($data[1] ?? '');
                $namaBarang = trim($data[2] ?? '');
                $kategoriExcel = trim($data[3] ?? '');
                $merk = trim($data[4] ?? '');
                $spesifikasi = trim($data[5] ?? '');

                $jumlah = (int) ($data[6] ?? 1);
                if ($jumlah <= 0) {
                    $jumlah = 1;
                }

                $tahunRaw = trim($data[8] ?? date('Y'));
                preg_match('/\b(20\d{2}|19\d{2})\b/', $tahunRaw, $yearMatch);
                $tahun = $yearMatch[1] ?? date('Y');

                $hargaStr = trim($data[9] ?? '0');
                $price = preg_replace('/[^0-9]/', '', $hargaStr);
                if (empty($price)) {
                    $price = 0;
                }

                $kondisi = trim($data[11] ?? 'Baik');
                if (! in_array($kondisi, ['Baik', 'Rusak Ringan', 'Rusak Berat'])) {
                    $kondisi = 'Baik';
                }

                $picName = trim($data[13] ?? '');
                $lokasiStr = trim($data[14] ?? '');
                $keterangan = trim($data[15] ?? '');

                // 🔥 TENTUKAN UNIT BERDASARKAN LOKASI 🔥
                $lowerLokasi = strtolower($lokasiStr);
                $assignedUnitId = $unitTI->id; // Default ke Prodi TI

                if (str_contains($lowerLokasi, 'lab') || str_contains($lowerLokasi, 'laboratorium') || str_contains($lowerLokasi, 'labkom')) {
                    $assignedUnitId = $unitLab->id;
                }

                $assetType = $types['LAN'];
                $categoryData = [];
                $lowerName = strtolower($namaBarang);
                $lowerSpesifikasi = strtolower($spesifikasi);
                $isClassified = false;

                // --- LOGIKA KLASIFIKASI ---
                if (str_contains($lowerName, 'router') || str_contains($lowerName, 'switch') || str_contains($lowerName, 'fiber') || str_contains($lowerName, 'kabel lan') || str_contains($lowerName, 'crimping') || str_contains($lowerName, 'splicer') || str_contains($lowerName, 'otdr') || str_contains($lowerName, 'rj45') || str_contains($lowerName, 'netlink') || str_contains($lowerName, 'access point') || str_contains($lowerName, 'modem') || str_contains($lowerName, 'media converter') || str_contains($lowerName, 'htb-gs')) {
                    $assetType = $types['JAR'];
                    $isClassified = true;

                    if (str_contains($lowerName, 'router')) {
                        $categoryData['subkategori'] = 'router';
                    } elseif (str_contains($lowerName, 'switch')) {
                        $categoryData['subkategori'] = 'switch';
                    } elseif (str_contains($lowerName, 'access point')) {
                        $categoryData['subkategori'] = 'ap';
                    } elseif (str_contains($lowerName, 'modem')) {
                        $categoryData['subkategori'] = 'modem';
                    } elseif (str_contains($lowerName, 'fiber') || str_contains($lowerName, 'kabel') || str_contains($lowerName, 'rj45')) {
                        $categoryData['subkategori'] = 'kabel';
                    } else {
                        $categoryData['subkategori'] = 'lainnya';
                    }

                    if (preg_match('/([0-9]+)[- ]?port/i', $lowerName, $m)) {
                        $categoryData['jumlah_port'] = $m[1];
                    }
                    if (preg_match('/([0-9]+ ?Mbps|[0-9]+ ?Gbps|gigabit)/i', $lowerSpesifikasi, $m)) {
                        $categoryData['kecepatan'] = $m[1];
                    }
                    if (str_contains($lowerSpesifikasi, 'poe')) {
                        $categoryData['dukungan_poe'] = 'Ya';
                    }
                } elseif (str_contains($lowerName, 'desktop') || str_contains($lowerName, 'pc ') || str_contains($lowerName, 'laptop') || str_contains($lowerName, 'monitor') || str_contains($lowerName, 'keyboard') || str_contains($lowerName, 'mouse') || str_contains($lowerName, 'printer') || str_contains($lowerName, 'headphone') || str_contains($lowerName, 'aio') || str_contains($lowerName, 'deskbook')) {
                    $assetType = $types['KOM'];
                    $isClassified = true;

                    if (str_contains($lowerName, 'desktop') || str_contains($lowerName, 'pc ') || str_contains($lowerName, 'aio') || str_contains($lowerName, 'deskbook')) {
                        $categoryData['subkategori'] = 'desktop';
                    } elseif (str_contains($lowerName, 'laptop')) {
                        $categoryData['subkategori'] = 'laptop';
                    } elseif (str_contains($lowerName, 'monitor')) {
                        $categoryData['subkategori'] = 'monitor';
                    } elseif (str_contains($lowerName, 'keyboard')) {
                        $categoryData['subkategori'] = 'keyboard';
                    } elseif (str_contains($lowerName, 'mouse')) {
                        $categoryData['subkategori'] = 'mouse';
                    } elseif (str_contains($lowerName, 'printer')) {
                        $categoryData['subkategori'] = 'printer';
                    } else {
                        $categoryData['subkategori'] = 'lainnya';
                    }

                    if (preg_match('/(i[3579][- ]?[0-9]{4,5}[a-z]*|ryzen [0-9] [0-9]{4}[a-z]*)/i', $spesifikasi, $m)) {
                        $categoryData['processor'] = $m[1];
                    }
                    if (preg_match('/([0-9]+ ?GB ?RAM|[0-9]+ ?GB DDR[34])/i', $spesifikasi, $m)) {
                        $categoryData['ram'] = str_replace('RAM', '', $m[1]);
                    }
                    if (preg_match('/(SSD|HDD)[^,]*/i', $spesifikasi, $m)) {
                        $categoryData['storage'] = trim($m[0]);
                    }
                    if (preg_match('/(windows [0-9]+|mac ?os|linux)/i', $spesifikasi, $m)) {
                        $categoryData['os'] = $m[1];
                    }
                } elseif (str_contains($lowerName, 'proyektor') || str_contains($lowerName, 'infokus') || str_contains($lowerName, 'kipas') || str_contains($lowerName, 'dispenser') || str_contains($lowerName, 'tv ') || str_contains($lowerName, 'kamera') || str_contains($lowerName, 'speaker') || str_contains($lowerName, 'audio') || $lowerName === 'ac' || str_contains($lowerName, 'ac ')) {
                    $assetType = $types['ELK'];
                    $isClassified = true;

                    if (str_contains($lowerName, 'proyektor') || str_contains($lowerName, 'infokus')) {
                        $categoryData['subkategori'] = 'proyektor';
                    } elseif ($lowerName === 'ac' || str_contains($lowerName, 'ac ')) {
                        $categoryData['subkategori'] = 'ac';
                    } elseif (str_contains($lowerName, 'tv ')) {
                        $categoryData['subkategori'] = 'tv';
                    } elseif (str_contains($lowerName, 'kamera')) {
                        $categoryData['subkategori'] = 'kamera';
                    } elseif (str_contains($lowerName, 'speaker') || str_contains($lowerName, 'audio')) {
                        $categoryData['subkategori'] = 'audio';
                    } else {
                        $categoryData['subkategori'] = 'lainnya';
                    }

                    if (preg_match('/([0-9]+ ?watt)/i', $lowerSpesifikasi, $m)) {
                        $categoryData['daya_listrik'] = $m[1];
                    }
                } elseif (str_contains($lowerName, 'meja') || str_contains($lowerName, 'kursi') || str_contains($lowerName, 'lemari') || str_contains($lowerName, 'rak') || str_contains($lowerName, 'papan') || str_contains($lowerName, 'whiteboard') || str_contains($lowerName, 'karpet') || str_contains($lowerName, 'sofa') || str_contains($lowerName, 'mading')) {
                    $assetType = $types['MEB'];
                    $isClassified = true;

                    if (str_contains($lowerName, 'meja')) {
                        $categoryData['subkategori'] = 'meja';
                    } elseif (str_contains($lowerName, 'kursi')) {
                        $categoryData['subkategori'] = 'kursi';
                    } elseif (str_contains($lowerName, 'lemari')) {
                        $categoryData['subkategori'] = 'lemari';
                    } elseif (str_contains($lowerName, 'rak')) {
                        $categoryData['subkategori'] = 'rak';
                    } else {
                        $categoryData['subkategori'] = 'lainnya';
                    }

                    if (str_contains($lowerSpesifikasi, 'kayu')) {
                        $categoryData['bahan_utama'] = 'Kayu';
                    } elseif (str_contains($lowerSpesifikasi, 'besi')) {
                        $categoryData['bahan_utama'] = 'Besi';
                    } elseif (str_contains($lowerSpesifikasi, 'kaca')) {
                        $categoryData['bahan_utama'] = 'Kaca';
                    } elseif (str_contains($lowerSpesifikasi, 'plastik')) {
                        $categoryData['bahan_utama'] = 'Plastik';
                    } else {
                        $categoryData['bahan_utama'] = 'Kombinasi';
                    }
                } elseif (str_contains($kategoriExcel, 'Komponen') || str_contains($lowerName, 'sensor') || str_contains($lowerName, 'module') || str_contains($lowerName, 'resistor') || str_contains($lowerName, 'obeng') || str_contains($lowerName, 'tool') || str_contains($lowerName, 'tang') || str_contains($lowerName, 'mobo') || str_contains($lowerName, 'motherboard') || str_contains($lowerName, 'processor') || str_contains($lowerName, 'vga') || str_contains($lowerName, 'ram ') || str_contains($lowerName, 'led ') || str_contains($lowerName, 'kapasitor') || str_contains($lowerName, 'ic ') || str_contains($lowerName, 'baterai') || str_contains($lowerName, 'arduino') || str_contains($lowerName, 'esp32')) {
                    $assetType = $types['ATK'];
                    $isClassified = true;

                    if (str_contains($lowerName, 'obeng') || str_contains($lowerName, 'tang') || str_contains($lowerName, 'tool')) {
                        $categoryData['subkategori'] = 'tangan';
                    } elseif (str_contains($lowerName, 'vga') || str_contains($lowerName, 'processor')) {
                        $categoryData['subkategori'] = 'listrik';
                    } else {
                        $categoryData['subkategori'] = 'lainnya';
                    }
                }

                if (! $isClassified) {
                    $assetType = $types['LAN'];
                    $categoryData['subkategori'] = $kategoriExcel ?: 'Lainnya';
                    $categoryData['deskripsi_fungsi'] = $spesifikasi ?: $namaBarang;
                }

                if (! empty($keterangan)) {
                    $categoryData['keterangan_tambahan'] = $keterangan;
                }

                // 🔥 PEMISAHAN BATCH DAN UNIT 🔥
                $schema = AssetType::getSchema($assetType->code);
                $batchData = [];
                $unitData = [];

                foreach ($categoryData as $key => $value) {
                    if (isset($schema[$key]['in_unit']) && $schema[$key]['in_unit'] === true) {
                        $unitData[$key] = $value;
                    } else {
                        $batchData[$key] = $value; // Masuk ke batch jika bukan in_unit (termasuk subkategori)
                    }
                }

                $purchaseDate = "{$tahun}-01-01";
                $batch = AssetBatch::create([
                    'asset_type_id' => $assetType->id,
                    'name' => $namaBarang,
                    'brand' => $merk ?: null,
                    'model' => null,
                    'price' => $price,
                    'purchase_date' => $purchaseDate,
                    'funding_source' => null,
                    'category_data' => $batchData,
                ]);

                $picId = null;
                if (! empty($picName)) {
                    $user = User::where('name', 'LIKE', "%{$picName}%")->first();
                    if ($user) {
                        $picId = $user->id;
                    }
                }

                for ($i = 0; $i < $jumlah; $i++) {
                    $newAssetId = Asset::generateAssetId($assetType->id);
                    $serial = $kodeBarang ?: 'SN-'.date('ymd');
                    $serial = $serial.'-'.($i + 1);

                    $asset = Asset::create([
                        'asset_id' => $newAssetId,
                        'asset_batch_id' => $batch->id,
                        'serial_number' => $serial,
                        'location' => $lokasiStr,
                        'unit_id' => $assignedUnitId, // 🔥 Gunakan Unit yang sudah dideteksi
                        'condition' => $kondisi,
                        'status' => 'Tersedia',
                        'qr_code' => QrCode::generateCodeContent($assetType->code),
                        'penanggung_jawab_id' => $picId,
                        'unit_data' => $unitData,
                    ]);

                    QrCode::create([
                        'qr_code_id' => 'QCD-'.str_pad($asset->id, 3, '0', STR_PAD_LEFT),
                        'asset_id' => $asset->id,
                        'code_content' => $asset->qr_code,
                        'status' => 'Aktif',
                    ]);
                }

                $success++;
                $this->info("✅ Baris {$row}: {$namaBarang} ({$jumlah} unit) → {$assetType->code} | Unit: ".($assignedUnitId == $unitLab->id ? 'LabKom' : 'Prodi TI'));

            } catch (\Exception $e) {
                $skip++;
                $this->error("❌ Baris {$row}: Gagal - ".$e->getMessage());
            }
        }

        fclose($handle);
        $this->info('=========================================');
        $this->info('🎉 IMPORT SELESAI!');
        $this->info("Batch Berhasil: {$success}");
        $this->info("Baris Gagal: {$skip}");
        $this->info('=========================================');
    }
}
