<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Buat tabel baris item (header-detail)
        Schema::create('asset_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_type_id')->constrained()->onDelete('restrict');
            $table->string('item_name');
            $table->string('specification')->nullable();
            $table->integer('quantity');
            $table->string('unit')->default('Pcs');
            $table->decimal('estimated_price_per_unit', 12, 2)->nullable();
            $table->timestamps();
        });

        // 2. Migrasi data lama ke tabel items SEBELUM kolom lama dihapus
        $oldRequests = DB::table('asset_requests')->get();
        foreach ($oldRequests as $old) {
            DB::table('asset_request_items')->insert([
                'asset_request_id' => $old->id,
                'asset_type_id'    => $old->asset_type_id,
                'item_name'        => $old->asset_name,
                'quantity'         => $old->quantity,
                'unit'             => 'Pcs',
                'estimated_price_per_unit' => $old->estimated_price,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // 3. HAPUS CONSTRAINT LAMA TERLEBIH DAHULU (PENTING AGAR TIDAK CHECK VIOLATION)
        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_priority_check");
        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check");

        // 4. Update data priority lama -> 3-level baru
        DB::table('asset_requests')->whereIn('priority', ['Rendah', 'Sedang'])->update(['priority' => 'Normal']);
        DB::table('asset_requests')->where('priority', 'Tinggi')->update(['priority' => 'Mendesak']);
        DB::table('asset_requests')->where('priority', 'Urgent')->update(['priority' => 'Sangat Mendesak']);

        // 5. Tambah kolom baru di header
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->enum('jenis_barang', ['Habis Pakai', 'Tidak Habis Pakai', 'Jasa'])
                ->default('Tidak Habis Pakai')->after('requester_id');
            $table->enum('kategori_barang', ['ATK', 'Konsumsi', 'Alat', 'Furniture', 'Lainnya'])
                ->default('Alat')->after('jenis_barang');
            $table->enum('alasan_pengajuan', ['Pengadaan Baru', 'Penggantian', 'Pengisian Kembali'])
                ->default('Pengadaan Baru')->after('kategori_barang');
            $table->foreignId('related_asset_id')->nullable()
                ->after('alasan_pengajuan')->constrained('assets')->onDelete('set null');
            $table->foreignId('verified_by')->nullable()
                ->after('requester_id')->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verified_at');
        });

        // 6. Pasang constraint baru
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_priority_check CHECK (priority IN ('Normal','Mendesak','Sangat Mendesak'))");
        DB::statement("ALTER TABLE asset_requests ALTER COLUMN priority SET DEFAULT 'Normal'");
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check CHECK (status IN ('Pending','Diverifikasi','Disetujui','Ditolak','Diterima'))");

        // 7. Hapus kolom lama dari header
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropColumn(['asset_name', 'asset_type_id', 'quantity', 'estimated_price']);
        });
    }

    public function down(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->string('asset_name')->nullable();
            $table->foreignId('asset_type_id')->nullable()->constrained()->onDelete('restrict');
            $table->integer('quantity')->nullable();
            $table->decimal('estimated_price', 12, 2)->nullable();
        });

        // Kembalikan data item pertama tiap request ke kolom lama (best-effort)
        $items = DB::table('asset_request_items')->get()->groupBy('asset_request_id');
        foreach ($items as $requestId => $itemGroup) {
            $first = $itemGroup->first();
            DB::table('asset_requests')->where('id', $requestId)->update([
                'asset_name' => $first->item_name,
                'asset_type_id' => $first->asset_type_id,
                'quantity' => $first->quantity,
                'estimated_price' => $first->estimated_price_per_unit,
            ]);
        }

        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropForeign(['related_asset_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['jenis_barang', 'kategori_barang', 'alasan_pengajuan', 'related_asset_id', 'verified_by', 'verified_at', 'verification_notes']);
        });

        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_priority_check");
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_priority_check CHECK (priority IN ('Rendah','Sedang','Tinggi','Urgent'))");

        DB::statement("ALTER TABLE asset_requests DROP CONSTRAINT IF EXISTS asset_requests_status_check");
        DB::statement("ALTER TABLE asset_requests ADD CONSTRAINT asset_requests_status_check CHECK (status IN ('Pending','Disetujui','Ditolak'))");

        Schema::dropIfExists('asset_request_items');
    }
};
