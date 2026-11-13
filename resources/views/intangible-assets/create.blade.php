@extends('layouts.app')

@section('title', 'Tambah Aset Non-Fisik')
@section('page-title', 'Tambah Aset Non-Fisik')

@section('content')
    {{-- ============================================================ --}}
    {{--  PEMILIH JENIS ASET – HERO CARD                              --}}
    {{-- ============================================================ --}}
    <div class="asset-type-selector">
        <div class="asset-type-selector__inner">
            <h2 class="asset-type-selector__title">📋 Pilih Jenis Aset</h2>
            <p class="asset-type-selector__subtitle">
                Tentukan kategori aset sebelum mengisi formulir di bawah.
            </p>

            <div class="asset-type-cards">
                <a href="{{ route('assets-inv.create') }}"
                    class="asset-type-card {{ request()->routeIs('assets-inv.create') ? 'active' : '' }}">
                    <div class="asset-type-card__icon">📦</div>
                    <div class="asset-type-card__body">
                        <h5>Aset Fisik</h5>
                        <p>Barang berwujud: komputer, furnitur, peralatan lab, dan sejenisnya</p>
                    </div>
                </a>

                <a href="{{ route('intangible-assets.create') }}"
                    class="asset-type-card {{ request()->routeIs('intangible-assets.create') ? 'active' : '' }}">
                    <div class="asset-type-card__icon">💾</div>
                    <div class="asset-type-card__body">
                        <h5>Aset Non-Fisik</h5>
                        <p>Lisensi software, HAKI, jurnal ilmiah, domain/hosting</p>
                    </div>
                    @if (request()->routeIs('intangible-assets.create'))
                        <span class="asset-type-card__check">✓</span>
                    @endif
                </a>
            </div>

            <div class="asset-type-selector__hint">
                💡 <strong>Tips:</strong> Pilih jenis aset yang sesuai. Halaman ini khusus untuk <strong>Aset
                    Non-Fisik</strong>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{--  FORM TAMBAH ASET NON-FISIK                                  --}}
    {{-- ============================================================ --}}
    <div class="data-table-container" style="margin-top: 2rem;">
        <div class="table-header">
            <h3 class="table-title">Formulir Aset Non-Fisik</h3>
            <a href="{{ route('intangible-assets.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
        </div>

        <div style="padding: 2rem;">
            <form action="{{ route('intangible-assets.store') }}" method="POST" enctype="multipart/form-data"
                id="assetForm">
                @csrf

                {{-- BAGIAN 1: INFORMASI UTAMA --}}
                <h4 style="margin-bottom: 1rem;">Informasi Utama</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Kategori <span style="color:red;">*</span></label>
                        <select id="category" name="category" class="form-control" required
                            onchange="handleCategoryChange()">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $val => $label)
                                <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Nama Aset <span style="color:red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                            required>
                    </div>
                </div>

                {{-- 🔥 POIN 13: DROPDOWN STATUS ADMINISTRATIF 🔥 --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status Administratif <span style="color:red;">*</span></label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Aktif" {{ old('status', 'Aktif') === 'Aktif' ? 'selected' : '' }}>Aktif
                            </option>
                            <option value="Belum Aktif" {{ old('status') === 'Belum Aktif' ? 'selected' : '' }}>Belum Aktif
                            </option>
                            <option value="Ditangguhkan" {{ old('status') === 'Ditangguhkan' ? 'selected' : '' }}>
                                Ditangguhkan</option>
                            <option value="Dihentikan" {{ old('status') === 'Dihentikan' ? 'selected' : '' }}>Dihentikan
                            </option>
                            <option value="Kadaluarsa" {{ old('status') === 'Kadaluarsa' ? 'selected' : '' }}>Kadaluarsa
                            </option>
                        </select>
                        <small style="display: block; margin-top: 0.25rem; color: #64748b; font-size: 0.8rem;">
                            Status operasional/administratif (terpisah dari status masa berlaku otomatis).
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1 1 100%;">
                        <label for="description">Deskripsi Aset <small
                                style="font-weight: normal; color: #64748b;">(Opsional)</small></label>
                        <textarea id="description" name="description" class="form-control" rows="2"
                            placeholder="Penjelasan singkat mengenai aset ini...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" id="vendorGroup">
                        <label for="vendor">Vendor / Penyedia <span style="color:red;">*</span></label>
                        <input type="text" id="vendor" name="vendor" class="form-control"
                            value="{{ old('vendor') }}">
                    </div>
                </div>

                {{-- BAGIAN 2: ATRIBUT DINAMIS (JSON) --}}
                <div id="dynamicAttributesSection"
                    style="display: none; background: #f8fafc; border: 1px dashed #cbd5e1; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <h4
                        style="margin-top: 0; margin-bottom: 1rem; font-size: 1rem; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
                        ⚙️ Spesifikasi Khusus Kategori
                    </h4>
                    <div class="form-row" id="dynamicAttributesContainer" style="flex-wrap: wrap;"></div>
                </div>

                {{-- BAGIAN 3: FINANSIAL & LEGAL --}}
                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Aspek Finansial &amp; Legalitas</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Harga Pembelian/Nilai Perolehan (Rp) <span style="color:red;">*</span></label>
                        <input type="number" id="price" name="price" class="form-control"
                            value="{{ old('price') }}" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="acquisition_method">Metode Perolehan</label>
                        <select id="acquisition_method" name="acquisition_method" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            <option value="Pembelian" {{ old('acquisition_method') === 'Pembelian' ? 'selected' : '' }}>
                                Pembelian</option>
                            <option value="Hibah" {{ old('acquisition_method') === 'Hibah' ? 'selected' : '' }}>Hibah /
                                Bantuan</option>
                            <option value="Pengembangan Internal"
                                {{ old('acquisition_method') === 'Pengembangan Internal' ? 'selected' : '' }}>Pengembangan
                                Internal</option>
                            <option value="Kerja Sama" {{ old('acquisition_method') === 'Kerja Sama' ? 'selected' : '' }}>
                                Kerja Sama / MoU</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="activation_date">Tanggal Perolehan / Aktivasi <span
                                style="color:red;">*</span></label>
                        <input type="date" id="activation_date" name="activation_date" class="form-control"
                            value="{{ old('activation_date', date('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="funding_source">Sumber Pendanaan</label>
                        <select id="funding_source" name="funding_source" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach ($fundingSources as $fs)
                                <option value="{{ $fs }}"
                                    {{ old('funding_source') === $fs ? 'selected' : '' }}>
                                    {{ $fs }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contract_number">Nomor Kontrak / Sertifikat / SK</label>
                        <input type="text" id="contract_number" name="contract_number" class="form-control"
                            value="{{ old('contract_number') }}">
                    </div>
                </div>

                {{-- BAGIAN 4: MASA BERLAKU --}}
                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Masa Berlaku &amp; Hak Penggunaan</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="license_type">Jenis Hak / Lisensi <span style="color:red;">*</span></label>
                        <select id="license_type" name="license_type" class="form-control" required
                            onchange="handleLicenseChange()">
                            <option value="">Pilih Kategori Terlebih Dahulu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="billing_model">Model Pembayaran</label>
                        <select id="billing_model" name="billing_model" class="form-control">
                            <option value="">Pilih Kategori Terlebih Dahulu</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" id="expiryGroup" style="display: none;">
                    <div class="form-group">
                        <label for="expiry_date">Tanggal Kedaluwarsa <span style="color:red;">*</span></label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control"
                            value="{{ old('expiry_date') }}">
                    </div>
                    <div class="form-group">
                        <label for="reminder_days">Ingatkan Sebelum Habis</label>
                        <select id="reminder_days" name="reminder_days" class="form-control">
                            <option value="">Tidak ada pengingat</option>
                            <option value="30" {{ old('reminder_days') == '30' ? 'selected' : '' }}>30 hari</option>
                            <option value="14" {{ old('reminder_days') == '14' ? 'selected' : '' }}>14 hari</option>
                            <option value="7" {{ old('reminder_days') == '7' ? 'selected' : '' }}>7 hari</option>
                        </select>
                    </div>
                </div>

                {{-- BAGIAN 5: PENEMPATAN DIGITAL (POIN 10) --}}
                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Penempatan &amp; Pengelolaan</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="unit_id">Unit Pengelola <span style="color:red;">*</span></label>
                        <select id="unit_id" name="unit_id" class="form-control" required>
                            <option value="">Pilih Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->category }}: {{ $unit->name }}</option>
                            @endforeach
                        </select>
                        <small style="display: block; margin-top: 0.25rem; color: #64748b; font-size: 0.8rem;">
                            Unit yang memiliki dan bertanggung jawab atas aset ini secara administratif.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="pic_id">PIC / Penanggung Jawab Harian</label>
                        <select id="pic_id" name="pic_id" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach ($usersByLevel as $level => $usersInLevel)
                                <optgroup label="{{ $level }}">
                                    @foreach ($usersInLevel as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('pic_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small style="display: block; margin-top: 0.25rem; color: #64748b; font-size: 0.8rem;">
                            Orang yang mengurus operasional aset (opsional untuk aset kolektif).
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="quota">Kapasitas / Kuota Pengguna</label>
                        <input type="text" id="quota" name="quota" class="form-control"
                            value="{{ old('quota') }}" placeholder='mis. "100 Users", "Unlimited"'>
                    </div>
                </div>
                <div class="form-group">
                    <label for="access_url">URL Akses / Dasbor Admin</label>
                    <input type="url" id="access_url" name="access_url" class="form-control"
                        value="{{ old('access_url') }}" placeholder="https://...">
                </div>

                <div class="form-group"
                    style="margin-top: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px dashed #cbd5e1;">
                    <label for="certificate_file" style="font-weight: 600;">Dokumen Pendukung / Bukti Kepemilikan</label>
                    <input type="file" id="certificate_file" name="certificate_file" class="form-control"
                        accept=".pdf,.jpg,.jpeg,.png">
                    <small style="display: block; margin-top: 0.5rem; color: #64748b; line-height: 1.5;">
                        <strong>Format:</strong> PDF, JPG, JPEG, PNG. <strong>Maksimal:</strong> 5MB.<br>
                        <strong>Sesuaikan dengan Kategori:</strong><br>
                        • <em>Software/Cloud/Jurnal:</em> Bukti lisensi, invoice, atau kontrak langganan.<br>
                        • <em>HAKI/Paten:</em> Sertifikat pendaftaran atau surat pengesahan.<br>
                        • <em>Domain/Hosting:</em> Bukti registrasi atau invoice tagihan.<br>
                        • <em>Kurikulum:</em> SK Pengesahan atau dokumen hak penggunaan.<br>
                        <span style="color: #ef4444;">*File disimpan secara privat dan aman. Hanya dapat diakses oleh
                            Admin/Sarpras.</span>
                    </small>
                </div>

                {{-- 🔥 POIN 16: LINK KE PERMINTAAN PENGADAAN 🔥 --}}
                @if (isset($availableRequests) && $availableRequests->count() > 0)
                    <div class="form-group"
                        style="margin-top: 1.5rem; background: #eff6ff; padding: 1rem; border-radius: 8px; border: 1px dashed #93c5fd;">
                        <label for="asset_request_id" style="font-weight: 600; color: #1e40af;">🔗 Tautkan ke Permintaan
                            Pengadaan (Opsional)</label>
                        <select id="asset_request_id" name="asset_request_id" class="form-control">
                            <option value="">-- Tidak ditautkan (Aset berdiri sendiri) --</option>
                            @foreach ($availableRequests as $req)
                                <option value="{{ $req->id }}"
                                    {{ old('asset_request_id') == $req->id ? 'selected' : '' }}>
                                    {{ $req->request_number }} —
                                    {{ $req->items->where('item_type', 'non-fisik')->first()->name ?? 'N/A' }}
                                    ({{ $req->created_at->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                        <small style="display: block; margin-top: 0.5rem; color: #1e40af; font-size: 0.8rem;">
                            Gunakan field ini jika aset yang Anda catat berasal dari pengajuan pengadaan yang sudah
                            disetujui.
                            Jika aset diperoleh secara mandiri (tanpa pengajuan), biarkan kosong.
                        </small>
                    </div>
                @endif

                <div class="btn-group" style="margin-top: 2rem;">
                    <a href="{{ route('intangible-assets.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan Aset Non-Fisik</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .asset-type-selector {
            background: var(--card-background);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 2rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .asset-type-selector__inner {
            max-width: 900px;
            margin: 0 auto;
        }

        .asset-type-selector__title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 0.25rem;
            color: var(--text-primary);
        }

        .asset-type-selector__subtitle {
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin: 0 0 1.75rem;
        }

        .asset-type-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .asset-type-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 1.5rem 1.75rem;
            border: 2px solid var(--border-color);
            border-radius: 14px;
            text-decoration: none;
            color: var(--text-primary);
            background: var(--light-bg);
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .asset-type-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-color);
        }

        .asset-type-card.active {
            border-color: var(--primary-color);
            background: color-mix(in srgb, var(--primary-color) 8%, var(--card-background));
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .asset-type-card__icon {
            font-size: 2.2rem;
            flex-shrink: 0;
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--card-background);
            border-radius: 14px;
            box-shadow: inset 0 0 0 1px var(--border-color);
        }

        .asset-type-card__body {
            flex: 1;
            min-width: 0;
        }

        .asset-type-card__body h5 {
            margin: 0 0 0.3rem;
            font-size: 1.05rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .asset-type-card__body p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .asset-type-card__check {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .asset-type-selector__hint {
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            background: var(--light-bg);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        @media screen and (max-width: 640px) {
            .asset-type-cards {
                grid-template-columns: 1fr;
            }

            .asset-type-card {
                padding: 1rem 1.25rem;
            }

            .asset-type-card__icon {
                font-size: 1.8rem;
                width: 52px;
                height: 52px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.intangibleSchemas = {!! json_encode($schemas) !!};
        let currentSchema = {};

        function handleCategoryChange() {
            const category = document.getElementById('category').value;
            const vendorGroup = document.getElementById('vendorGroup');
            const vendorInput = document.getElementById('vendor');
            const dynamicSection = document.getElementById('dynamicAttributesSection');
            const container = document.getElementById('dynamicAttributesContainer');

            const licenseSelect = document.getElementById('license_type');
            const billingSelect = document.getElementById('billing_model');

            container.innerHTML = '';
            dynamicSection.style.display = 'none';

            if (!category || !window.intangibleSchemas[category]) {
                vendorGroup.style.display = 'block';
                vendorInput.setAttribute('required', 'required');
                licenseSelect.innerHTML = '<option value="">Pilih Kategori Terlebih Dahulu</option>';
                billingSelect.innerHTML = '<option value="">Pilih Kategori Terlebih Dahulu</option>';
                return;
            }

            currentSchema = window.intangibleSchemas[category];

            if (currentSchema.hide_vendor) {
                vendorGroup.style.display = 'none';
                vendorInput.removeAttribute('required');
            } else {
                vendorGroup.style.display = 'block';
                vendorInput.setAttribute('required', 'required');
            }

            const oldLicenseType = "{{ old('license_type') }}";
            const oldBillingModel = "{{ old('billing_model') }}";

            licenseSelect.innerHTML = '<option value="">Pilih Jenis Hak</option>';
            billingSelect.innerHTML = '<option value="">Pilih Model Pembayaran</option>';

            if (currentSchema.license_types) {
                for (const [val, label] of Object.entries(currentSchema.license_types)) {
                    const selected = (oldLicenseType === val) ? 'selected' : '';
                    licenseSelect.innerHTML += `<option value="${val}" ${selected}>${label}</option>`;
                }
            }

            if (currentSchema.billing_models) {
                for (const [val, label] of Object.entries(currentSchema.billing_models)) {
                    const selected = (oldBillingModel === val) ? 'selected' : '';
                    billingSelect.innerHTML += `<option value="${val}" ${selected}>${label}</option>`;
                }
            }

            let hasFields = false;
            for (const [key, field] of Object.entries(currentSchema)) {
                if (typeof field === 'object' && field !== null && field.in_batch) {
                    hasFields = true;
                    const inputId = `cd_${key}`; // ID unik untuk aksesibilitas
                    let inputHtml = '';

                    if (field.type === 'select') {
                        let options = `<option value="">-- Pilih --</option>`;
                        for (const [val, label] of Object.entries(field.options)) {
                            options += `<option value="${val}">${label}</option>`;
                        }
                        inputHtml =
                            `<select id="${inputId}" name="category_data[${key}]" class="form-control" ${field.required ? 'required' : ''}>${options}</select>`;
                    } else if (field.type === 'textarea') {
                        inputHtml =
                            `<textarea id="${inputId}" name="category_data[${key}]" class="form-control" rows="2" ${field.required ? 'required' : ''}></textarea>`;
                    } else {
                        // 🔥 FIX: Deklarasikan placeholder & extraAttrs sebelum digunakan
                        let placeholder = '';
                        let extraAttrs = '';
                        if (field.type === 'url') placeholder = 'placeholder="https://..."';
                        if (field.type === 'email') placeholder = 'placeholder="nama@email.com"';
                        if (field.type === 'number') extraAttrs = 'min="0" step="any"';

                        inputHtml =
                            `<input id="${inputId}" type="${field.type}" name="category_data[${key}]" class="form-control" ${placeholder} ${extraAttrs} ${field.required ? 'required' : ''}>`;
                    }

                    container.innerHTML += `
                        <div class="form-group" style="flex: 1 1 calc(33.333% - 1rem); min-width: 250px;">
                            <label for="${inputId}">${field.label} ${field.required ? '<span style="color:red;">*</span>' : ''}</label>
                            ${inputHtml}
                        </div>
                    `;
                }
            }

            if (hasFields) {
                dynamicSection.style.display = 'block';
            }

            handleLicenseChange();
        }

        function handleLicenseChange() {
            const licenseType = document.getElementById('license_type').value;
            const expiryGroup = document.getElementById('expiryGroup');
            const expiryDateInput = document.getElementById('expiry_date');
            const reminderInput = document.getElementById('reminder_days');

            if (licenseType === 'Subscription' || licenseType === 'Registered Right') {
                expiryGroup.style.display = 'flex';
                expiryDateInput.setAttribute('required', 'required');
            } else {
                expiryGroup.style.display = 'none';
                expiryDateInput.removeAttribute('required');
                expiryDateInput.value = '';
                if (reminderInput) reminderInput.value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            handleCategoryChange();
            handleLicenseChange();
        });
    </script>
@endpush
