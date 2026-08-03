<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\QrCode;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID tipe aset untuk mapping
        $types = AssetType::pluck('id', 'code')->toArray();
        // Hasil: ['KOM' => 1, 'ELK' => 2, dst...]

        // Data dari script.js yang sudah dikonversi
        $assets = [
            ['id' => '2024/01/KOM-0001', 'name' => 'Laptop HP ProBook', 'type' => 'KOM', 'brand' => 'HP', 'serial' => 'HP123456', 'location' => 'Ruang IT', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 8000000, 'purchaseDate' => '2024-01-15', 'image' => 'assets/Laptop-HP-ProBook.jpeg', 'qrcode' => 'HP123456789'],
            ['id' => '2024/01/KOM-0002', 'name' => 'Proyektor Epson', 'type' => 'KOM', 'brand' => 'Epson', 'serial' => 'EP789012', 'location' => 'Aula', 'condition' => 'Baik', 'status' => 'Dipinjam', 'price' => 5000000, 'purchaseDate' => '2024-01-12', 'image' => 'assets/proyektor-epson.jpeg', 'qrcode' => 'EP789012345'],
            ['id' => '2024/01/FUR-0001', 'name' => 'Meja Kerja', 'type' => 'FUR', 'brand' => 'Olympic', 'serial' => 'OL345678', 'location' => 'Ruang Dosen', 'condition' => 'Rusak Ringan', 'status' => 'Maintenance', 'price' => 2500000, 'purchaseDate' => '2024-01-10', 'image' => 'assets/meja-kerja.jpeg', 'qrcode' => 'OL345678901'],
            ['id' => '2024/01/KOM-0003', 'name' => 'Deskbook', 'type' => 'KOM', 'brand' => 'Hp', 'serial' => 'DBHP001', 'location' => '9 di lab TI, 1 di R. TI', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 10000000, 'purchaseDate' => '2024-01-01', 'image' => 'assets/deskbook.jpg', 'qrcode' => 'DBHP001BC'],
            ['id' => '2024/01/KOM-0004', 'name' => 'Keyboard', 'type' => 'KOM', 'brand' => 'Logitech, Hp', 'serial' => 'KB001', 'location' => 'Gudang', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 125000, 'purchaseDate' => '2024-01-01', 'image' => 'assets/keyboard.png', 'qrcode' => 'KB001BC'],
            ['id' => '2024/01/KOM-0005', 'name' => 'Mouse', 'type' => 'KOM', 'brand' => 'Logitech, Hp', 'serial' => 'MS001', 'location' => 'Gudang', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 70000, 'purchaseDate' => '2024-01-01', 'image' => 'assets/mouse.jpg', 'qrcode' => 'MS001BC'],
            ['id' => '2024/01/KOM-0006', 'name' => 'Monitor DELL E2211H', 'type' => 'KOM', 'brand' => 'Dell', 'serial' => 'MNDELL001', 'location' => '17 di lab TI', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 780000, 'purchaseDate' => '2024-01-01', 'image' => 'assets/monitor-dell.png', 'qrcode' => 'MNDELL001BC'],
            ['id' => '2024/01/KOM-0007', 'name' => 'PC Rakitan', 'type' => 'KOM', 'brand' => 'Asrock', 'serial' => 'PCRAK001', 'location' => '17 di lab TI', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 3000000, 'purchaseDate' => '2024-01-01', 'image' => 'assets/pc-rakitan.jpg', 'qrcode' => 'PCRAK001BC'],
            ['id' => '2024/01/KOM-0008', 'name' => 'Headphones JETE', 'type' => 'KOM', 'brand' => 'JETE', 'serial' => 'HPJETE001', 'location' => 'Gudang', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 0, 'purchaseDate' => '2024-01-01', 'image' => 'assets/headphones-jete.jpg', 'qrcode' => 'HPJETE001BC'],
            ['id' => '2024/01/JAR-0001', 'name' => 'Splicer', 'type' => 'JAR', 'brand' => 'Fusion', 'serial' => 'SPLCR001', 'location' => 'Ruang Server', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 15000000, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=Splicer', 'qrcode' => 'SPLCR001BC'],
            ['id' => '2024/01/JAR-0002', 'name' => 'Fiber optic 250 meter', 'type' => 'JAR', 'brand' => 'Generic', 'serial' => 'FO250M001', 'location' => 'Gudang', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 240000, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=Fiber+Optic', 'qrcode' => 'FO250M001BC'],
            ['id' => '2024/01/JAR-0003', 'name' => 'Switch tplink', 'type' => 'JAR', 'brand' => 'tp-link', 'serial' => 'SWTPLINK001', 'location' => 'Ruang Server', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 750000, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=Switch+TPLink', 'qrcode' => 'SWTPLINK001BC'],
            ['id' => '2024/01/JAR-0004', 'name' => 'HTB', 'type' => 'JAR', 'brand' => 'Netlink', 'serial' => 'HTB001', 'location' => 'Gudang', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 100000, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=HTB', 'qrcode' => 'HTB001BC'],
            ['id' => '2024/01/FUR-0002', 'name' => 'Rak rako', 'type' => 'FUR', 'brand' => 'Raaco', 'serial' => 'RAKRAKO001', 'location' => 'Gudang', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 450000, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=Rak+Raako', 'qrcode' => 'RAKRAKO001BC'],
            ['id' => '2024/01/ATK-0001', 'name' => 'CRIMPING BIRU schneider', 'type' => 'ATK', 'brand' => 'Schneider', 'serial' => 'CRIMPSCH001', 'location' => 'Toolbox', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 350000, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=Crimping+Schneider', 'qrcode' => 'CRIMPSCH001BC'],
            ['id' => '2024/01/LAN-0001', 'name' => 'Container Box cb 82 hijau', 'type' => 'LAN', 'brand' => 'Shinpo', 'serial' => 'CBH001', 'location' => 'Gudang', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 140000, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=Container+Box', 'qrcode' => 'CBH001BC'],
            ['id' => '2024/01/ELK-0025', 'name' => 'Sensor LDR', 'type' => 'ELK', 'brand' => 'Generic', 'serial' => 'SNSRLDR001', 'location' => 'Laci Biru A', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 400, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=Sensor+LDR', 'qrcode' => 'SNSRLDR001BC'],
            ['id' => '2024/01/ELK-0135', 'name' => 'ARDUINO UNO R3', 'type' => 'ELK', 'brand' => 'Arduino', 'serial' => 'ARDUINOUNOR3001', 'location' => 'Lemari Komponen', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 99900, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=Arduino+Uno+R3', 'qrcode' => 'ARDUINOUNOR3001BC'],
            ['id' => '2024/01/ELK-0116', 'name' => 'NodeMCU (ESP8266)', 'type' => 'ELK', 'brand' => 'Amica', 'serial' => 'NODEMCUESP8266001', 'location' => 'Lemari Komponen', 'condition' => 'Baik', 'status' => 'Tersedia', 'price' => 39500, 'purchaseDate' => '2024-01-01', 'image' => 'https://via.placeholder.com/400x300/e2e8f0/64748b?text=NodeMCU+ESP8266', 'qrcode' => 'NODEMCUESP8266001BC'],
        ];

        foreach ($assets as $data) {
            // 1. Simpan Aset
            $asset = Asset::create([
                'asset_id' => $data['id'],
                'name' => $data['name'],
                'asset_type_id' => $types[$data['type']], // Mapping string 'KOM' ke ID integer
                'brand' => $data['brand'] ?: 'No Brand',
                'serial_number' => $data['serial'],
                'price' => $data['price'],
                'purchase_date' => $data['purchaseDate'], // INI YANG SEBELUMNYA ERROR
                'location' => $data['location'] ?: 'Gudang',
                'condition' => $data['condition'],
                'status' => $data['status'],
                'image' => $data['image'],
                'qr_code' => $data['qrcode'],
            ]);

            // 2. Buat data di tabel QR Codes (sesuai script.js Anda yang memisahkan tabel qr)
            QrCode::create([
                'qr_code_id' => 'QCD-' . str_pad($asset->id, 3, '0', STR_PAD_LEFT),
                'asset_id' => $asset->id,
                'code_content' => $data['qrcode'],
                'status' => 'Aktif',
            ]);
        }
    }
}