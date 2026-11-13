<?php

use App\Models\AssetType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Perluasan kategori aset fisik untuk skala universitas. Murni ADDITIVE —
        // tidak ada rename atau migrasi data aset yang sudah ada, dengan alasan:
        //
        // 1. Kode "FUR" (Furniture) yang sekarang berjalan itu SUDAH jelas
        //    (bukan "BULU" seperti yang sempat diklaim di draf revisi — kolom
        //    asset_types.code adalah varchar(3), jadi "BULU" 4 huruf tidak
        //    mungkin pernah tersimpan di sana). Tidak ada yang perlu di-rename.
        // 2. Kode "ATK" memang berpotensi rancu dengan "Alat Tulis Kantor", tapi
        //    memutuskan aset "ATK" yang sudah ada sebenarnya alat lab atau bukan
        //    butuh dilihat satu-satu — tidak aman ditebak lewat migration.
        //    Solusinya: tambah "LAB" sebagai kategori baru untuk aset BARU ke
        //    depan, sementara aset "ATK" yang sudah ada dibiarkan apa adanya
        //    sampai ada yang meninjau ulang secara manual mana yang perlu
        //    dipindah.
        //
        // Semua kode di bawah persis 3 karakter, sesuai batas kolom varchar(3).
        $newTypes = [
            ['code' => 'TNH', 'name' => 'Tanah & Lahan', 'description' => 'Lahan kampus, lahan percobaan, area parkir'],
            ['code' => 'GDG', 'name' => 'Gedung & Bangunan', 'description' => 'Rektorat, gedung fakultas, ruang kelas, auditorium, asrama'],
            ['code' => 'LAB', 'name' => 'Peralatan & Instrumen Laboratorium', 'description' => 'Mikroskop, spektrofotometer, alat uji, dan peralatan lab spesifik lainnya'],
            ['code' => 'OTO', 'name' => 'Kendaraan Operasional', 'description' => 'Bus kampus, mobil dinas, motor operasional, ambulans'],
            ['code' => 'ARS', 'name' => 'Koleksi Perpustakaan & Arsip', 'description' => 'Buku, jurnal cetak, manuskrip, karya seni, arsip institusi'],
            ['code' => 'PRS', 'name' => 'Prasarana Umum', 'description' => 'Instalasi listrik, plumbing, pagar, penerangan jalan kampus'],
        ];

        foreach ($newTypes as $type) {
            // updateOrCreate supaya migration aman dijalankan ulang (idempotent)
            // kalau suatu saat perlu di-rerun di lingkungan lain.
            AssetType::updateOrCreate(['code' => $type['code']], $type);
        }
    }

    public function down(): void
    {
        // Hanya hapus baris yang migration ini yang tambahkan. Aman selama tidak
        // ada aset yang sudah dibuat memakai kategori baru ini — kalau ada,
        // constraint foreign key di assets.asset_type_id akan mencegah drop-nya
        // (mengharuskan aset itu direklasifikasi dulu sebelum rollback).
        AssetType::whereIn('code', ['TNH', 'GDG', 'LAB', 'OTO', 'ARS', 'PRS'])->delete();
    }
};
