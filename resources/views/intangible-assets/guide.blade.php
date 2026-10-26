@extends('layouts.app')

@section('title', 'Panduan Pengisian Aset Non-Fisik')
@section('page-title', 'Panduan Pengisian Aset Non-Fisik')

@section('content')
    <style>
        .accordion {
            background: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .accordion-header {
            background: var(--light-bg);
            padding: 1rem 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--text-primary);
        }

        .accordion-header:hover {
            background: #e2e8f0;
        }

        .accordion-body {
            padding: 1.5rem;
            display: none;
            border-top: 1px solid var(--border-color);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .accordion-body.active {
            display: block;
        }

        .field-list {
            margin: 0;
            padding-left: 1.5rem;
        }

        .field-list li {
            margin-bottom: 0.5rem;
        }

        .field-wajib {
            color: #dc2626;
            font-weight: bold;
        }

        .badge-kategori {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
    </style>

    <div class="data-table-container" style="max-width: 900px; margin: 0 auto;">
        <div class="table-header">
            <h3 class="table-title">Panduan Pengisian Formulir Aset Non-Fisik</h3>
            <a href="{{ route('intangible-assets.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <div style="padding: 2rem;">
            <div
                style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 2rem;">
                <p style="margin: 0; color: #1e40af;">
                    💡 <strong>Informasi:</strong> Formulir Aset Non-Fisik bersifat <strong>dinamis</strong>. Kolom yang
                    muncul
                    akan menyesuaikan dengan Kategori Aset yang dipilih. Klik pada masing-masing kategori di bawah ini untuk
                    melihat panduan pengisiannya.
                </p>
            </div>

            {{-- ATURAN UMUM --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Aturan Umum (Wajib Dibaca)</span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <ul class="field-list">
                        <li><strong>Vendor / Penyedia:</strong> <span class="field-wajib">*</span> Wajib diisi,
                            <strong>KECUALI</strong> untuk kategori HAKI/Paten dan Kurikulum (karena biasanya dikembangkan
                            sendiri oleh institusi).
                        </li>
                        <li><strong>Jenis Lisensi:</strong>
                            <ul>
                                <li><code>Perpetual (Selamanya)</code>: Tidak memerlukan tanggal kedaluwarsa.</li>
                                <li><code>Subscription (Berlangganan)</code> atau <code>Hak Terdaftar</code>:
                                    <strong>Wajib</strong> mengisi Tanggal Kedaluwarsa.
                                </li>
                            </ul>
                        </li>
                        <li><strong>Dokumen Pendukung:</strong> Format PDF/JPG/PNG, maks 5MB. Disimpan secara privat (hanya
                            Admin/Sarpras yang bisa unduh).</li>
                    </ul>
                </div>
            </div>

            {{-- 1. SOFTWARE --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>1. Perangkat Lunak / Software <span class="badge-kategori">Software</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Mencatat lisensi software (Windows, Office, MATLAB, dll).</p>
                    <ul class="field-list">
                        <li><strong>Nama Produk:</strong> <span class="field-wajib">*</span> Wajib diisi (misal: Microsoft
                            365).</li>
                        <li><strong>Model Lisensi:</strong> <span class="field-wajib">*</span> Pilih Per User / Per Device /
                            Site License.</li>
                        <li><strong>Product Key:</strong> Data sensitif. Sistem akan <strong>mengenkripsi</strong> data ini
                            di database. Di halaman detail, hanya 4 karakter terakhir yang ditampilkan.</li>
                        <li><strong>Email Pengguna:</strong> Opsional, isi jika lisensi ditugaskan ke email tertentu.</li>
                    </ul>
                </div>
            </div>

            {{-- 2. CLOUD/SAAS --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>2. Layanan Cloud / SaaS <span class="badge-kategori">Cloud/SaaS</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Mencatat langganan layanan berbasis cloud (Zoom, Google Workspace, AWS).</p>
                    <ul class="field-list">
                        <li><strong>Nama Layanan & Paket:</strong> <span class="field-wajib">*</span> Wajib diisi.</li>
                        <li><strong>Kuota Pengguna:</strong> Isi jumlah user yang diizinkan (angka).</li>
                        <li><strong>Periode Penagihan:</strong> Pilih Bulanan / Tahunan.</li>
                        <li><strong>URL Layanan:</strong> Alamat akses aplikasi (misal: https://zoom.us).</li>
                    </ul>
                </div>
            </div>

            {{-- 3. AKUN DIGITAL --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>3. Akun Digital / Subscription <span class="badge-kategori">Akun Digital</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Mencatat akun institusi (akun lab, akun admin platform, dll).</p>
                    <ul class="field-list">
                        <li><strong>Nama & Jenis Akun:</strong> <span class="field-wajib">*</span> Wajib diisi.</li>
                        <li><strong>Email Akun:</strong> <span class="field-wajib">*</span> Email yang digunakan untuk
                            login.</li>
                        <li><strong>Pemilik Administratif:</strong> Siapa yang bertanggung jawab atas akun ini.</li>
                        <li><em>Catatan Keamanan:</em> Jangan pernah memasukkan password atau token di form ini.</li>
                    </ul>
                </div>
            </div>

            {{-- 4. DOMAIN/HOSTING --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>4. Domain & Hosting <span class="badge-kategori">Domain/Hosting</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Mencatat aset digital berupa nama domain dan layanan server.</p>
                    <ul class="field-list">
                        <li><strong>Nama Domain & Registrar:</strong> <span class="field-wajib">*</span> Wajib diisi (misal:
                            stti.ac.id, PANDI).</li>
                        <li><strong>Tanggal Kedaluwarsa Domain:</strong> Sangat penting untuk pengingat perpanjangan.</li>
                        <li><strong>Layanan Hosting:</strong> Isi nama penyedia, jenis (VPS/Shared), dan tanggal
                            berakhirnya.</li>
                    </ul>
                </div>
            </div>

            {{-- 5. JURNAL ILMIAH --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>5. Jurnal Ilmiah & Database <span class="badge-kategori">Jurnal Ilmiah</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Mencatat langganan akses jurnal (IEEE, Springer, Scopus).</p>
                    <ul class="field-list">
                        <li><strong>Nama Jurnal/Database:</strong> <span class="field-wajib">*</span> Wajib diisi.</li>
                        <li><strong>ISSN:</strong> Opsional, jika yang diakses adalah jurnal spesifik.</li>
                        <li><strong>Cakupan Langganan:</strong> Full Access / Sebagian.</li>
                    </ul>
                </div>
            </div>

            {{-- 6. HAKI/PATEN --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>6. HAKI / Paten <span class="badge-kategori">HAKI/Paten</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Mencatat kekayaan intelektual milik institusi atau dosen.</p>
                    <p style="color: #dc2626; font-weight: bold;">Catatan: Field Vendor akan disembunyikan untuk kategori
                        ini.</p>
                    <ul class="field-list">
                        <li><strong>Nama Karya / Invensi:</strong> <span class="field-wajib">*</span> Judul paten/hak cipta.
                        </li>
                        <li><strong>Nomor Registrasi:</strong> <span class="field-wajib">*</span> Nomor sertifikat dari
                            Kemenkumham.</li>
                        <li><strong>Pencipta & Pemegang Hak:</strong> <span class="field-wajib">*</span> Wajib diisi.</li>
                        <li><strong>Status Proses:</strong> Dalam Proses / Disetujui.</li>
                    </ul>
                </div>
            </div>

            {{-- 7. SERTIFIKAT DIGITAL --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>7. Sertifikat Digital / SSL <span class="badge-kategori">Sertifikat Digital</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Mencatat sertifikat SSL/TLS untuk keamanan website.</p>
                    <ul class="field-list">
                        <li><strong>Nama & Jenis Sertifikat:</strong> <span class="field-wajib">*</span> Wajib diisi.</li>
                        <li><strong>Penerbit (CA):</strong> <span class="field-wajib">*</span> Misal: Let's Encrypt,
                            DigiCert.</li>
                        <li><strong>Serial Number:</strong> <span class="field-wajib">*</span> Nomor unik sertifikat.</li>
                        <li><strong>Domain/Subjek:</strong> <span class="field-wajib">*</span> Domain yang dilindungi
                            (misal: *.stti.ac.id).</li>
                    </ul>
                </div>
            </div>

            {{-- 8. KURIKULUM --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>8. Lisensi Kurikulum / Konten <span class="badge-kategori">Kurikulum</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Mencatat dokumen kurikulum atau materi berhak cipta.</p>
                    <p style="color: #dc2626; font-weight: bold;">Catatan: Field Vendor akan disembunyikan untuk kategori
                        ini.</p>
                    <ul class="field-list">
                        <li><strong>Nama Kurikulum & Prodi:</strong> <span class="field-wajib">*</span> Wajib diisi.</li>
                        <li><strong>Versi:</strong> <span class="field-wajib">*</span> Misal: 1.0, 2026-Revisi.</li>
                        <li><strong>Nomor SK Pengesahan:</strong> Opsional.</li>
                        <li><strong>Jenis Hak:</strong> Pilih Limited Right / Registered Right.</li>
                    </ul>
                </div>
            </div>

            {{-- 9. LAINNYA --}}
            <div class="accordion">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>9. Kategori Lainnya <span class="badge-kategori">Lainnya</span></span>
                    <span>▼</span>
                </div>
                <div class="accordion-body">
                    <p><strong>Tujuan:</strong> Digunakan jika aset tidak masuk ke 8 kategori di atas.</p>
                    <ul class="field-list">
                        <li><strong>Sub Kategori:</strong> <span class="field-wajib">*</span> Tuliskan jenis asetnya (misal:
                            Sertifikat Pelatihan).</li>
                        <li><strong>Alasan Masuk Lainnya:</strong> <span class="field-wajib">*</span> Jelaskan alasan
                            mengapa tidak masuk kategori utama.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleAccordion(header) {
            const body = header.nextElementSibling;
            const icon = header.querySelector('span:last-child');

            // Toggle class active
            body.classList.toggle('active');

            // Ubah icon
            if (body.classList.contains('active')) {
                icon.textContent = '▲';
                header.style.background = '#e2e8f0';
            } else {
                icon.textContent = '▼';
                header.style.background = 'var(--light-bg)';
            }
        }
    </script>
@endpush
