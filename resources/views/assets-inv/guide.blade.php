@extends('layouts.app')

@section('title', 'Panduan Pengisian Aset')
@section('page-title', 'Panduan Pengisian Aset')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Panduan Pengisian Formulir Aset Fisik</h3>
            <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
        </div>

        <div style="padding: 2rem;">
            <div
                style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 2rem;">
                <p style="margin: 0; color: #1e40af;">
                    💡 <strong>Informasi:</strong> Formulir Tambah Aset bersifat dinamis. Kolom yang muncul akan
                    menyesuaikan dengan <strong>Jenis Aset</strong> yang Anda pilih. Pastikan Anda membaca panduan di bawah
                    ini sebelum mengisi.
                </p>
            </div>

            <div style="display: grid; gap: 1.5rem;">

                {{-- KOM --}}
                <div
                    style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background: var(--card-background);">
                    <h4 style="margin-top: 0; color: var(--primary-color);">1. Komputer & Periferal (KOM)</h4>
                    <p><strong>Wajib diisi:</strong> Merek, Model, Harga, Tanggal Beli, Serial Number.</p>
                    <p><strong>Field Khusus:</strong> Pilih <em>Jenis Perangkat</em> (Laptop/Desktop/Server) untuk
                        memunculkan isian <em>Prosesor, RAM, Storage, OS</em>. Untuk Laptop/Monitor, isi <em>Ukuran
                            Layar</em>.</p>
                    <p><strong>Per Unit:</strong> Jika Laptop/PC, isi juga <em>Hostname</em> dan <em>MAC Address</em>.</p>
                </div>

                {{-- TNH --}}
                <div
                    style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background: var(--card-background);">
                    <h4 style="margin-top: 0; color: var(--primary-color);">2. Tanah & Lahan (TNH)</h4>
                    <p style="color: #dc2626;"><strong>Catatan Khusus:</strong> Kategori ini tidak memerlukan Merek atau
                        Serial Number. Input Kuantitas dikunci otomatis menjadi 1 unit. Lokasi Penempatan (Ruang) tidak
                        wajib.</p>
                    <p><strong>Field Khusus:</strong> Wajib isi Luas Tanah (m²), Nomor Sertifikat, Status Kepemilikan
                        (HM/HGB), dan Alamat.</p>
                </div>

                {{-- ARS --}}
                <div
                    style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background: var(--card-background);">
                    <h4 style="margin-top: 0; color: var(--primary-color);">3. Koleksi Perpustakaan & Arsip (ARS)</h4>
                    <p style="color: #dc2626;"><strong>Catatan Khusus:</strong> Tidak perlu mengisi Merek dan Serial Number.
                    </p>
                    <p><strong>Field Khusus:</strong> Pilih Subkategori. Jika <em>Bahan Pustaka</em>, isi Penulis, ISBN,
                        Penerbit. Jika <em>Arsip</em>, isi Pencipta, Tanggal, dan Tingkat Akses. Identitas unik per unit
                        menggunakan <strong>No. Aksesion / Katalog</strong>.</p>
                </div>

                {{-- OTO --}}
                <div
                    style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background: var(--card-background);">
                    <h4 style="margin-top: 0; color: var(--primary-color);">4. Kendaraan Operasional (OTO)</h4>
                    <p><strong>Field Khusus:</strong> Isi Merek, Model, Tahun, Bahan Bakar, dan Transmisi.</p>
                    <p><strong>Per Unit:</strong> Nomor Rangka (VIN), Nomor Mesin, dan Nomor Polisi wajib diisi per
                        kendaraan.</p>
                </div>

                {{-- Lainnya --}}
                <div
                    style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background: var(--card-background);">
                    <h4 style="margin-top: 0; color: var(--primary-color);">5. Kategori Lainnya</h4>
                    <p>Untuk kategori seperti Elektronik (ELK), Mebel (MEB), Gedung (GDG), Prasarana (PRS), dan
                        Alat/Perkakas (ATK), silakan isi field yang muncul sesuai dengan karakteristik barang. Field yang
                        tidak relevan (seperti S/N untuk Gedung) akan disembunyikan secara otomatis oleh sistem.</p>
                </div>

            </div>
        </div>
    </div>
@endsection
