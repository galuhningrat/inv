@extends('layouts.app')

@section('title', 'Edit Aset Non-Fisik')
@section('page-title', 'Edit Aset Non-Fisik')

@section('content')
    <div class="data-table-container" style="margin-top: 2rem;">
        <div class="table-header">
            <h3 class="table-title">Edit: {{ $intangibleAsset->name }}</h3>
            <a href="{{ route('intangible-assets.show', $intangibleAsset) }}" class="btn btn-secondary">← Kembali ke
                Detail</a>
        </div>

        <div style="padding: 2rem;">
            <form action="{{ route('intangible-assets.update', $intangibleAsset) }}" method="POST"
                enctype="multipart/form-data" id="assetForm">
                @csrf
                @method('PUT')

                <h4 style="margin-bottom: 1rem;">Informasi Utama</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Kategori <span style="color:red;">*</span></label>
                        <select id="category" name="category" class="form-control" required
                            onchange="handleCategoryChange()">
                            @foreach ($categories as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('category', $intangibleAsset->category) === $val ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Nama Aset <span style="color:red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', $intangibleAsset->name) }}" required>
                    </div>
                </div>

                {{-- 🔥 POIN 13: DROPDOWN STATUS ADMINISTRATIF 🔥 --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status Administratif <span style="color:red;">*</span></label>
                        @php $currentStatus = old('status', $intangibleAsset->status); @endphp
                        <select id="status" name="status" class="form-control" required>
                            <option value="Aktif" {{ $currentStatus === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Belum Aktif" {{ $currentStatus === 'Belum Aktif' ? 'selected' : '' }}>Belum Aktif
                            </option>
                            <option value="Ditangguhkan" {{ $currentStatus === 'Ditangguhkan' ? 'selected' : '' }}>
                                Ditangguhkan</option>
                            <option value="Dihentikan" {{ $currentStatus === 'Dihentikan' ? 'selected' : '' }}>Dihentikan
                            </option>
                            <option value="Kadaluarsa" {{ $currentStatus === 'Kadaluarsa' ? 'selected' : '' }}>Kadaluarsa
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
                            placeholder="Penjelasan singkat mengenai aset ini...">{{ old('description', $intangibleAsset->description) }}</textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" id="vendorGroup">
                        <label for="vendor">Vendor / Penyedia <span style="color:red;">*</span></label>
                        <input type="text" id="vendor" name="vendor" class="form-control"
                            value="{{ old('vendor', $intangibleAsset->vendor) }}">
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
                            value="{{ old('price', $intangibleAsset->price) }}" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="acquisition_method">Metode Perolehan</label>
                        <select id="acquisition_method" name="acquisition_method" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @php $method = old('acquisition_method', $intangibleAsset->acquisition_method); @endphp
                            <option value="Pembelian" {{ $method === 'Pembelian' ? 'selected' : '' }}>Pembelian</option>
                            <option value="Hibah" {{ $method === 'Hibah' ? 'selected' : '' }}>Hibah / Bantuan</option>
                            <option value="Pengembangan Internal"
                                {{ $method === 'Pengembangan Internal' ? 'selected' : '' }}>Pengembangan Internal</option>
                            <option value="Kerja Sama" {{ $method === 'Kerja Sama' ? 'selected' : '' }}>Kerja Sama / MoU
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="activation_date">Tanggal Perolehan / Aktivasi <span style="color:red;">*</span></label>
                        <input type="date" id="activation_date" name="activation_date" class="form-control"
                            value="{{ old('activation_date', $intangibleAsset->activation_date->format('Y-m-d')) }}"
                            required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="funding_source">Sumber Pendanaan</label>
                        <select id="funding_source" name="funding_source" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach ($fundingSources as $fs)
                                <option value="{{ $fs }}"
                                    {{ old('funding_source', $intangibleAsset->funding_source) === $fs ? 'selected' : '' }}>
                                    {{ $fs }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contract_number">Nomor Kontrak / Sertifikat / SK</label>
                        <input type="text" id="contract_number" name="contract_number" class="form-control"
                            value="{{ old('contract_number', $intangibleAsset->contract_number) }}">
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
                        <small style="display: block; margin-top: 0.25rem; color: #64748b; font-size: 0.8rem;">
                            *Perpetual berarti hak penggunaan tidak terbatas waktu, namun dukungan/pembaruan dari vendor
                            mungkin terbatas.
                        </small>
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
                            value="{{ old('expiry_date', $intangibleAsset->expiry_date ? $intangibleAsset->expiry_date->format('Y-m-d') : '') }}">
                    </div>
                    <div class="form-group">
                        <label for="reminder_days">Ingatkan Sebelum Habis</label>
                        <select id="reminder_days" name="reminder_days" class="form-control">
                            <option value="">Tidak ada pengingat</option>
                            <option value="30"
                                {{ old('reminder_days', $intangibleAsset->reminder_days) == '30' ? 'selected' : '' }}>30
                                hari</option>
                            <option value="14"
                                {{ old('reminder_days', $intangibleAsset->reminder_days) == '14' ? 'selected' : '' }}>14
                                hari</option>
                            <option value="7"
                                {{ old('reminder_days', $intangibleAsset->reminder_days) == '7' ? 'selected' : '' }}>7 hari
                            </option>
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
                                <option value="{{ $unit->id }}"
                                    {{ old('unit_id', $intangibleAsset->unit_id) == $unit->id ? 'selected' : '' }}>
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
                                            {{ old('pic_id', $intangibleAsset->pic_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}</option>
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
                            value="{{ old('quota', $intangibleAsset->quota) }}"
                            placeholder='mis. "100 Users", "Unlimited"'>
                    </div>
                </div>
                <div class="form-group">
                    <label for="access_url">URL Akses / Dasbor Admin</label>
                    <input type="url" id="access_url" name="access_url" class="form-control"
                        value="{{ old('access_url', $intangibleAsset->access_url) }}" placeholder="https://...">
                </div>

                <div class="form-group"
                    style="margin-top: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px dashed #cbd5e1;">
                    <label for="certificate_file" style="font-weight: 600;">Dokumen Pendukung / Bukti Kepemilikan (Biarkan
                        kosong jika tidak ingin mengganti)</label>
                    <input type="file" id="certificate_file" name="certificate_file" class="form-control"
                        accept=".pdf,.jpg,.jpeg,.png">

                    @if ($intangibleAsset->certificate_file)
                        <div
                            style="margin-top: 0.5rem; padding: 0.5rem; background: #dcfce7; border-radius: 4px; border: 1px solid #86efac;">
                            <span style="color: #166534; font-size: 0.85rem;">✓ Dokumen sudah terlampir. Upload file baru
                                untuk menggantinya.</span>
                        </div>
                    @endif

                    <small style="display: block; margin-top: 0.5rem; color: #64748b; line-height: 1.5;">
                        <strong>Format:</strong> PDF, JPG, JPEG, PNG. <strong>Maksimal:</strong> 5MB.<br>
                        <strong>Panduan Dokumen Sesuai Kategori:</strong><br>
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
                            <option value="">-- Tidak ditautkan --</option>
                            @foreach ($availableRequests as $req)
                                <option value="{{ $req->id }}"
                                    {{ old('asset_request_id', $intangibleAsset->asset_request_id) == $req->id ? 'selected' : '' }}>
                                    {{ $req->request_number }} —
                                    {{ $req->items->where('item_type', 'non-fisik')->first()->name ?? 'N/A' }}
                                    ({{ $req->created_at->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                        @if ($intangibleAsset->asset_request_id)
                            <div
                                style="margin-top: 0.5rem; padding: 0.5rem; background: #dbeafe; border-radius: 4px; border: 1px solid #93c5fd;">
                                <span style="color: #1e40af; font-size: 0.85rem;">
                                    ℹ️ Aset ini sudah tertaut ke pengajuan. Pilih "Tidak ditautkan" untuk melepas tautan.
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="btn-group" style="margin-top: 2rem;">
                    <a href="{{ route('intangible-assets.show', $intangibleAsset) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Update Aset Non-Fisik</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.intangibleSchemas = {!! json_encode($schemas) !!};
        window.existingCategoryData = {!! json_encode($intangibleAsset->category_data ?? []) !!};

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

            const currentLicenseType = "{{ old('license_type', $intangibleAsset->license_type) }}";
            const currentBillingModel = "{{ old('billing_model', $intangibleAsset->billing_model) }}";

            licenseSelect.innerHTML = '<option value="">Pilih Jenis Hak</option>';
            billingSelect.innerHTML = '<option value="">Pilih Model Pembayaran</option>';

            if (currentSchema.license_types) {
                for (const [val, label] of Object.entries(currentSchema.license_types)) {
                    const selected = (currentLicenseType === val) ? 'selected' : '';
                    licenseSelect.innerHTML += `<option value="${val}" ${selected}>${label}</option>`;
                }
            }

            if (currentSchema.billing_models) {
                for (const [val, label] of Object.entries(currentSchema.billing_models)) {
                    const selected = (currentBillingModel === val) ? 'selected' : '';
                    billingSelect.innerHTML += `<option value="${val}" ${selected}>${label}</option>`;
                }
            }

            let hasFields = false;
            for (const [key, field] of Object.entries(currentSchema)) {
                if (typeof field === 'object' && field !== null && field.in_batch) {
                    hasFields = true;
                    const inputId = `cd_${key}`; // 🔥 ID unik untuk aksesibilitas
                    let inputHtml = '';

                    let oldValue = window.existingCategoryData[key] || '';

                    if (key === 'product_key' && oldValue !== '') {
                        oldValue = '';
                    }

                    if (field.type === 'select') {
                        let options = `<option value="">-- Pilih --</option>`;
                        for (const [val, label] of Object.entries(field.options)) {
                            const selected = (oldValue === val) ? 'selected' : '';
                            options += `<option value="${val}" ${selected}>${label}</option>`;
                        }
                        inputHtml =
                            `<select id="${inputId}" name="category_data[${key}]" class="form-control" ${field.required ? 'required' : ''}>${options}</select>`;
                    } else if (field.type === 'textarea') {
                        inputHtml =
                            `<textarea id="${inputId}" name="category_data[${key}]" class="form-control" rows="2" ${field.required ? 'required' : ''}>${oldValue}</textarea>`;
                    } else {
                        let placeholder = '';
                        let extraAttrs = '';
                        if (field.type === 'url') placeholder = 'placeholder="https://..."';
                        if (field.type === 'email') placeholder = 'placeholder="nama@email.com"';
                        if (field.type === 'number') extraAttrs = 'min="0" step="any"';
                        if (key === 'product_key') placeholder = 'placeholder="Kosongkan jika tidak diubah"';

                        inputHtml =
                            `<input id="${inputId}" type="${field.type}" name="category_data[${key}]" class="form-control" value="${oldValue}" ${placeholder} ${extraAttrs} ${field.required ? 'required' : ''}>`;
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

        // 🔥 POIN 17.3: Peringatan jika user mengubah kategori
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('assetForm');
            const originalCategory = "{{ $intangibleAsset->category }}";
            const hasExistingData = Object.keys(window.existingCategoryData).length > 0;

            if (form) {
                form.addEventListener('submit', function(e) {
                    const newCategory = document.getElementById('category').value;

                    // Hanya warning jika kategori BERUBAH dan ada data lama
                    if (newCategory !== originalCategory && hasExistingData) {
                        const confirmMsg =
                            '⚠️ PERHATIAN!\n\n' +
                            'Anda mengubah kategori:\n' +
                            '  DARI: ' + originalCategory + '\n' +
                            '  KE: ' + newCategory + '\n\n' +
                            'Spesifikasi khusus dari kategori lama (' + originalCategory +
                            ') akan DIHAPUS karena strukturnya berbeda dengan kategori baru.\n\n' +
                            'Data yang akan hilang mencakup ' + Object.keys(window.existingCategoryData)
                            .length + ' field.\n\n' +
                            'Apakah Anda yakin ingin melanjutkan?';

                        if (!confirm(confirmMsg)) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });
    </script>
@endpush
