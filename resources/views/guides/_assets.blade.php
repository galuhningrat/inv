<div style="padding: 2rem;">
    <div
        style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 2rem;">
        <p style="margin: 0; color: #1e40af;">
            💡 <strong>Informasi:</strong> Formulir Tambah Aset bersifat <strong>dinamis</strong>. Kolom yang muncul
            akan menyesuaikan dengan Jenis Aset yang dipilih. Klik pada masing-masing kategori di bawah ini untuk
            melihat panduan pengisiannya.
        </p>
    </div>

    {{-- KOM --}}
    <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)">
            <span>1. Komputer & Periferal (KOM)</span>
            <span>▼</span>
        </div>
        <div class="accordion-body">
            <p><strong>Tujuan:</strong> Mencatat perangkat komputasi (Laptop, PC, Server, Monitor, Printer).</p>
            <ul class="field-list">
                <li><strong>Merek & Model:</strong> <span class="field-wajib">*</span> Wajib diisi (misal: Asus, X455LJ).
                </li>
                <li><strong>Jenis Perangkat:</strong> <span class="field-wajib">*</span> Pilih Laptop/Desktop untuk
                    memunculkan field spesifikasi.</li>
                <li><strong>Spesifikasi:</strong> Isi Prosesor, RAM, Storage, dan OS jika perangkat komputasi. (Field
                    ini akan hilang jika memilih Keyboard/Mouse).</li>
                <li><strong>Per Unit:</strong> Serial Number (S/N) wajib diisi. Untuk Laptop/PC, isi juga
                    <em>Hostname</em> dan <em>MAC Address</em>.
                </li>
            </ul>
        </div>
    </div>

    {{-- TNH --}}
    <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)">
            <span>2. Tanah & Lahan (TNH)</span>
            <span>▼</span>
        </div>
        <div class="accordion-body">
            <p><strong>Tujuan:</strong> Mencatat bidang tanah sebagai objek aset tersendiri.</p>
            <p style="color: #dc2626; font-weight: bold;">Catatan: Kategori ini tidak memerlukan Merek/S/N. Kuantitas
                dikunci 1.</p>
            <ul class="field-list">
                <li><strong>Luas Tanah (m²):</strong> <span class="field-wajib">*</span> Wajib diisi angka.</li>
                <li><strong>Nomor Sertifikat:</strong> <span class="field-wajib">*</span> Wajib diisi.</li>
                <li><strong>Alamat / Kelurahan:</strong> <span class="field-wajib">*</span> Wajib diisi sesuai dokumen.
                </li>
                <li><strong>Upload Sertifikat:</strong> Unggah scan/foto sertifikat (PDF/Gambar).</li>
            </ul>
        </div>
    </div>

    {{-- ARS --}}
    <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)">
            <span>3. Koleksi Perpustakaan & Arsip (ARS)</span>
            <span>▼</span>
        </div>
        <div class="accordion-body">
            <p><strong>Tujuan:</strong> Mencatat Buku, Jurnal, atau Arsip Dokumen.</p>
            <p style="color: #dc2626; font-weight: bold;">Catatan: Tidak perlu mengisi Merek dan Serial Number.</p>
            <ul class="field-list">
                <li><strong>Jenis Koleksi:</strong> <span class="field-wajib">*</span> Pilih "Bahan Pustaka" atau
                    "Arsip".</li>
                <li><strong>Bahan Pustaka:</strong> Isi Penulis, ISBN, Penerbit, Tahun Terbit.</li>
                <li><strong>Arsip:</strong> Isi Pencipta Arsip, Tanggal, Tingkat Akses, dan Masa Retensi.</li>
                <li><strong>Per Unit:</strong> Isi <strong>No. Aksesion / Katalog</strong> sebagai identitas unik
                    buku/arsip.</li>
            </ul>
        </div>
    </div>

    {{-- OTO --}}
    <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)">
            <span>4. Kendaraan Operasional (OTO)</span>
            <span>▼</span>
        </div>
        <div class="accordion-body">
            <p><strong>Tujuan:</strong> Mencatat Mobil, Motor, Truk, dll.</p>
            <ul class="field-list">
                <li><strong>Merek, Model, Tahun:</strong> <span class="field-wajib">*</span> Wajib diisi.</li>
                <li><strong>Per Unit:</strong> Wajib mengisi <strong>Nomor Rangka (VIN)</strong>, <strong>Nomor
                        Mesin</strong>, dan <strong>Nomor Polisi</strong>.</li>
                <li><strong>Dokumen:</strong> Unggah scan STNK pada kolom Upload STNK.</li>
            </ul>
        </div>
    </div>

    {{-- MEB / FUR / ATK --}}
    <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)">
            <span>5. Mebel (MEB/FUR) & Alat/Perkakas (ATK)</span>
            <span>▼</span>
        </div>
        <div class="accordion-body">
            <p><strong>Mebel:</strong> Tidak perlu Merek/S/N. Cukup isi Jenis Mebel, Bahan (Kayu/Besi), dan Dimensi.
                Kuantitas bisa lebih dari 1 (misal: 10 Kursi).</p>
            <p><strong>Alat & Perkakas:</strong> Pilih subkategori. Jika memilih "Peralatan Listrik", akan muncul isian
                Daya Listrik (Watt).</p>
        </div>
    </div>

    {{-- Lainnya --}}
    <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)">
            <span>6. Kategori Lainnya (ELK, LAB, GDG, PRS, LAN)</span>
            <span>▼</span>
        </div>
        <div class="accordion-body">
            <p>Untuk kategori Elektronik (ELK), Laboratorium (LAB), Gedung (GDG), Prasarana (PRS), dan Lainnya (LAN),
                silakan isi field yang muncul sesuai dengan karakteristik barang. Sistem akan otomatis menyembunyikan
                field yang tidak relevan.</p>
        </div>
    </div>
</div>
