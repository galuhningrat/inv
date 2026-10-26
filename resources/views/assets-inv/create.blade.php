@extends('layouts.app')

@section('title', 'Tambah Aset Fisik')
@section('page-title', 'Tambah Aset Fisik Baru')

@section('content')
    {{-- ============================================================ --}}
    {{-- PEMILIH JENIS ASET – HERO CARD --}}
    {{-- ============================================================ --}}
    <div class="asset-type-selector">
        <div class="asset-type-selector__inner">
            <h2 class="asset-type-selector__title">📋 Pilih Jenis Aset</h2>
            <p class="asset-type-selector__subtitle">
                Tentukan kategori aset sebelum mengisi formulir di bawah.
            </p>

            <div class="asset-type-cards">
                {{-- Card Aset Fisik (aktif) --}}
                <a href="{{ route('assets-inv.create') }}"
                    class="asset-type-card {{ request()->routeIs('assets-inv.create') ? 'active' : '' }}">
                    <div class="asset-type-card__icon">📦</div>
                    <div class="asset-type-card__body">
                        <h5>Aset Fisik</h5>
                        <p>Barang berwujud: komputer, furnitur, peralatan lab, dan sejenisnya</p>
                    </div>
                    @if (request()->routeIs('assets-inv.create'))
                        <span class="asset-type-card__check">✓</span>
                    @endif
                </a>

                {{-- Card Aset Non-Fisik --}}
                <a href="{{ route('intangible-assets.create') }}"
                    class="asset-type-card {{ request()->routeIs('intangible-assets.create') ? 'active' : '' }}">
                    <div class="asset-type-card__icon">💾</div>
                    <div class="asset-type-card__body">
                        <h5>Aset Non-Fisik <span class="badge-prototype">Prototipe</span></h5>
                        <p>Lisensi software, HAKI, jurnal ilmiah, domain/hosting</p>
                    </div>
                    @if (request()->routeIs('intangible-assets.create'))
                        <span class="asset-type-card__check">✓</span>
                    @endif
                </a>
            </div>

            <div class="asset-type-selector__hint">
                💡 <strong>Tips:</strong> Pilih jenis aset yang sesuai. Halaman ini khusus untuk <strong>Aset
                    Fisik</strong>.
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- FORM TAMBAH ASET FISIK --}}
    {{-- ============================================================ --}}
    <div class="data-table-container" style="margin-top: 2rem;">
        <div class="table-header">
            <h3 class="table-title">Formulir Aset Fisik</h3>
            <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
        </div>

        <div style="padding: 2rem;">
            <form action="{{ route('assets-inv.store') }}" method="POST" enctype="multipart/form-data" id="assetForm">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Aset / Model <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') error @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="asset_type_id">Jenis Aset <span style="color: red;">*</span></label>
                        <select id="asset_type_id" name="asset_type_id"
                            class="form-control @error('asset_type_id') error @enderror" required
                            onchange="handleCategoryChange()">
                            <option value="">Pilih Jenis</option>
                            @foreach ($assetTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('asset_type_id')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- FIELD UMUM (Merek, Model, Harga) --}}
                <div class="form-row">
                    <div class="form-group general-brand-group">
                        <label for="brand">Merek <span style="color: red;">*</span></label>
                        <input type="text" id="brand" name="brand"
                            class="form-control @error('brand') error @enderror" value="{{ old('brand') }}">
                        @error('brand')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group general-model-group">
                        <label for="model">Tipe / Model <small style="font-weight: normal;">(opsional)</small></label>
                        <input type="text" id="model" name="model" class="form-control"
                            value="{{ old('model') }}" placeholder="mis. ExpertCenter D700">
                    </div>
                    <div class="form-group">
                        <label for="price">Harga Pembelian (Satuan) <span style="color: red;">*</span></label>
                        <input type="number" id="price" name="price"
                            class="form-control @error('price') error @enderror" value="{{ old('price') }}" min="0"
                            step="any" onwheel="this.blur()" required>
                        @error('price')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_date">Tanggal Pembelian <span style="color: red;">*</span></label>
                        <input type="date" id="purchase_date" name="purchase_date"
                            class="form-control @error('purchase_date') error @enderror"
                            value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- 🔥 WADAH UNTUK FIELD DINAMIS BATCH 🔥 --}}
                <div id="dynamicBatchFields"
                    style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: none;">
                    <h4
                        style="margin-top: 0; margin-bottom: 1rem; font-size: 1rem; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
                        ⚙️ Spesifikasi Khusus Kategori
                    </h4>
                    <div class="form-row" id="batchFieldsContainer" style="flex-wrap: wrap;"></div>
                </div>

                {{-- 🔥 BUNGKUS DENGAN ID ACCOUNTING SECTION 🔥 --}}
                <div id="accountingSection">
                    <hr style="margin: 1.5rem 0;">
                    <h4 style="margin-bottom: 1rem;">Data Akuntansi <small
                            style="font-weight: normal; color: var(--text-secondary);">(opsional)</small></h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="funding_source">Sumber Pendanaan</label>
                            <select id="funding_source" name="funding_source" class="form-control">
                                <option value="">-- Tidak ditentukan --</option>
                                @foreach ($fundingSources as $fs)
                                    <option value="{{ $fs }}"
                                        {{ old('funding_source') === $fs ? 'selected' : '' }}>
                                        {{ $fs }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="economic_life_years">Umur Ekonomis (Tahun)</label>
                            <input type="number" id="economic_life_years" name="economic_life_years"
                                class="form-control" value="{{ old('economic_life_years') }}" min="1"
                                max="100" placeholder="mis. 5">
                        </div>
                        <div class="form-group">
                            <label for="residual_value">Nilai Residu (Rp)</label>
                            <input type="number" id="residual_value" name="residual_value" class="form-control"
                                value="{{ old('residual_value') }}" min="0" step="any"
                                onwheel="this.blur()">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-group">
                        <label for="penanggung_jawab_id">Penanggung Jawab Aset / PIC</label>
                        <select id="penanggung_jawab_id" name="penanggung_jawab_id" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach ($usersByLevel as $level => $usersInLevel)
                                <optgroup label="{{ $level }}">
                                    @foreach ($usersInLevel as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('penanggung_jawab_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 🔥 BUNGKUS DENGAN ID LOCATION SECTION 🔥 --}}
                <div id="locationSection">
                    <hr style="margin: 1.5rem 0;">
                    <h4 style="margin-bottom: 1rem;">Lokasi Penempatan</h4>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="kategori">Kategori <span style="color: red;">*</span></label>
                            <select id="kategori" class="form-control" required onchange="updateUnitOptions()">
                                <option value="">Pilih Kategori</option>
                                @foreach ($units->pluck('category')->unique() as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="unit_id">Unit <span style="color: red;">*</span></label>
                            <select id="unit_id" name="unit_id" class="form-control @error('unit_id') error @enderror"
                                required disabled onchange="updateLocationOptions()">
                                <option value="">-- Pilih Kategori dulu --</option>
                            </select>
                            @error('unit_id')
                                <div class="error-message" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location_id">Lokasi Spesifik <span style="color: red;">*</span></label>
                        <select id="location_id" name="location_id"
                            class="form-control @error('location_id') error @enderror" required disabled>
                            <option value="">-- Pilih Unit dulu --</option>
                        </select>
                        @error('location_id')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label id="location_detail_label" for="location_detail">Detail Penempatan
                            <small style="font-weight: normal;">(opsional, mis. "di dalam lemari")</small>
                        </label>
                        <input type="text" id="location_detail" name="location_detail" class="form-control"
                            value="{{ old('location_detail') }}">
                        @error('location_detail')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Kuantitas &amp; Detail Per Unit</h4>

                <div class="form-group">
                    <label for="quantity">Kuantitas <span style="color: red;">*</span></label>
                    <input type="number" id="quantity" name="quantity" class="form-control" value="1"
                        min="1" max="100" onchange="renderUnitBlocks()" required>
                </div>

                <div class="unit-table-wrapper">
                    <table class="unit-table" id="unitTable">
                        <thead>
                            <tr id="tableHeaderRow">
                                <th style="width: 4rem;">Unit Ke</th>
                                <th class="general-serial-header">Nomor Seri Pabrik (S/N) <span
                                        style="color: red;">*</span></th>
                                <th id="kondisiHeader" style="width: 10rem;">Kondisi <span style="color: red;">*</span>
                                </th>
                                <th id="fotoHeader" style="width: 14rem;">Foto Unit</th>
                            </tr>
                        </thead>
                        <tbody id="unitBlocksContainer"></tbody>
                    </table>
                </div>

                <div class="btn-group" style="margin-top: 1.5rem;">
                    <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan Aset Fisik</button>
                </div>
            </form>
        </div>
    </div>

    <script id="unitsData" type="application/json">
        @json($unitsForJs)
    </script>

    <script>
        window.assetSchemas = {!! json_encode($schemas) !!};
    </script>
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

        .badge-prototype {
            font-size: 0.65rem;
            font-weight: 600;
            background: #fff3cd;
            color: #856404;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            border: 1px solid #ffc107;
            white-space: nowrap;
        }

        .unit-table-wrapper {
            overflow-x: auto;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .unit-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 640px;
        }

        .unit-table th,
        .unit-table td {
            padding: 0.6rem 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .unit-table thead th {
            background: var(--light-bg);
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .unit-table tbody tr:last-child td {
            border-bottom: none;
        }

        .unit-table .form-control {
            margin: 0;
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
        const allUnits = JSON.parse(document.getElementById('unitsData').textContent);
        let currentSchema = {};

        function updateUnitOptions() {
            const kategori = document.getElementById('kategori').value;
            const unitSelect = document.getElementById('unit_id');
            const locationSelect = document.getElementById('location_id');

            unitSelect.innerHTML = '<option value="">Pilih Unit</option>';
            locationSelect.innerHTML = '<option value="">-- Pilih Unit dulu --</option>';
            locationSelect.disabled = true;

            if (!kategori) {
                unitSelect.disabled = true;
                unitSelect.innerHTML = '<option value="">-- Pilih Kategori dulu --</option>';
                return;
            }

            allUnits.filter(u => u.category === kategori).forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.name;
                unitSelect.appendChild(opt);
            });
            unitSelect.disabled = false;
        }

        function updateLocationOptions() {
            const unitId = parseInt(document.getElementById('unit_id').value);
            const locationSelect = document.getElementById('location_id');
            const locationDetail = document.getElementById('location_detail');
            const detailLabel = document.getElementById('location_detail_label');

            locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';

            if (!unitId) {
                locationSelect.disabled = true;
                locationSelect.required = false;
                return;
            }

            const unit = allUnits.find(u => u.id === unitId);

            if (!unit || unit.locations.length === 0) {
                locationSelect.innerHTML = '<option value="">-- Tidak ada lokasi spesifik untuk unit ini --</option>';
                locationSelect.disabled = true;
                locationSelect.required = false;
                locationDetail.required = true;
                detailLabel.innerHTML =
                    'Detail Penempatan <span style="color:red;">*</span> <small style="font-weight: normal;">(wajib, unit ini belum punya daftar lokasi spesifik)</small>';
            } else {
                unit.locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.id;
                    opt.textContent = loc.name;
                    locationSelect.appendChild(opt);
                });
                locationSelect.disabled = false;
                locationSelect.required = true;
                locationDetail.required = false;
                detailLabel.innerHTML =
                    'Detail Penempatan <small style="font-weight: normal;">(opsional, mis. "di dalam lemari")</small>';
            }
        }

        function handleCategoryChange() {
            const typeId = document.getElementById('asset_type_id').value;
            const dynamicBatchContainer = document.getElementById('dynamicBatchFields');
            const generalBrand = document.querySelector('.general-brand-group');
            const generalModel = document.querySelector('.general-model-group');
            const generalSerialHeader = document.querySelector('.general-serial-header');
            const qtyInput = document.getElementById('quantity');
            const locationSection = document.getElementById('locationSection');
            const accountingSection = document.getElementById('accountingSection');

            // Reset
            document.getElementById('batchFieldsContainer').innerHTML = '';
            dynamicBatchContainer.style.display = 'none';
            generalBrand.style.display = 'block';
            generalModel.style.display = 'block';
            generalSerialHeader.style.display = 'table-cell';

            // Reset Kuantitas & Section
            qtyInput.disabled = false;
            qtyInput.max = 100;
            if (locationSection) locationSection.style.display = 'block';
            if (accountingSection) accountingSection.style.display = 'block';

            if (!typeId || !window.assetSchemas[typeId]) {
                currentSchema = {};
                renderUnitBlocks();
                return;
            }

            currentSchema = window.assetSchemas[typeId];

            const isStaticAsset = currentSchema.is_static_asset || false;

            if (currentSchema.hide_general) {
                generalBrand.style.display = 'none';
                generalModel.style.display = 'none';
                generalSerialHeader.style.display = 'none';
                document.getElementById('brand').removeAttribute('required');
            } else {
                document.getElementById('brand').setAttribute('required', 'required');
            }

            if (currentSchema.max_qty) {
                qtyInput.value = currentSchema.max_qty;
                qtyInput.max = currentSchema.max_qty;
                qtyInput.disabled = true;
            }

            if (isStaticAsset) {
                if (locationSection) locationSection.style.display = 'none';
                if (accountingSection) accountingSection.style.display = 'none';
                const unitSelect = document.getElementById('unit_id');
                const locSelect = document.getElementById('location_id');
                if (unitSelect) unitSelect.removeAttribute('required');
                if (locSelect) locSelect.removeAttribute('required');
            }

            renderBatchFields();
        }

        function renderBatchFields() {
            const container = document.getElementById('batchFieldsContainer');
            const dynamicBatchContainer = document.getElementById('dynamicBatchFields');
            const headerRow = document.getElementById('tableHeaderRow');
            const kondisiHeader = document.getElementById('kondisiHeader');
            const fotoHeader = document.getElementById('fotoHeader');

            // 🔥 Sembunyikan header Kondisi & Foto jika flag aktif
            if (kondisiHeader) {
                kondisiHeader.style.display = currentSchema.hide_condition ? 'none' : 'table-cell';
            }
            if (fotoHeader) {
                fotoHeader.style.display = currentSchema.hide_image ? 'none' : 'table-cell';
            }

            container.innerHTML = '';
            let hasBatchFields = false;

            for (const [key, field] of Object.entries(currentSchema)) {
                if (field.in_batch) {
                    hasBatchFields = true;
                    let inputHtml = '';

                    if (field.type === 'select') {
                        let options = `<option value="">-- Pilih --</option>`;
                        for (const [val, label] of Object.entries(field.options)) {
                            options += `<option value="${val}">${label}</option>`;
                        }
                        inputHtml =
                            `<select name="category_data[${key}]" id="batch_input_${key}" class="form-control" onchange="handleDependencies()" ${field.required ? 'required' : ''}>${options}</select>`;
                    } else if (field.type === 'textarea') {
                        inputHtml =
                            `<textarea name="category_data[${key}]" id="batch_input_${key}" class="form-control" rows="2" ${field.required ? 'required' : ''}></textarea>`;
                    } else if (field.type === 'file') {
                        const isMultiple = field.multiple ? 'multiple' : '';
                        const nameAttr = field.multiple ? `category_data[${key}][]` : `category_data[${key}]`;
                        inputHtml =
                            `<input type="file" name="${nameAttr}" class="form-control" accept=".pdf,.jpg,.jpeg,.png" ${isMultiple} ${field.required ? 'required' : ''}>`;
                    } else {
                        inputHtml =
                            `<input type="${field.type}" name="category_data[${key}]" id="batch_input_${key}" class="form-control" ${field.required ? 'required' : ''}>`;
                    }

                    const colStyle = field.type === 'textarea' ? 'flex: 1 1 100%;' :
                        'flex: 1 1 calc(33.333% - 1rem); min-width: 250px;';
                    const initialDisplay = field.show_if ? 'none' : 'block';

                    container.innerHTML += `
                        <div class="form-group conditional-field" data-field="${key}" style="padding-right: 1rem; box-sizing: border-box; display: ${initialDisplay}; ${colStyle}">
                            <label>${field.label} ${field.required ? '<span style="color:red;">*</span>' : ''}</label>
                            ${inputHtml}
                        </div>
                    `;
                }
            }

            if (hasBatchFields) {
                dynamicBatchContainer.style.display = 'block';
            }

            for (const [key, field] of Object.entries(currentSchema)) {
                if (field.in_unit) {
                    const th = document.createElement('th');
                    th.className = `conditional-header dynamic-header`;
                    th.dataset.field = key;
                    th.style.display = field.show_if ? 'none' : 'table-cell';
                    th.innerHTML = `${field.label} ${field.required ? '<span style="color:red;">*</span>' : ''}`;
                    headerRow.insertBefore(th, kondisiHeader);
                }
            }

            handleDependencies();
        }

        function handleDependencies() {
            for (const [key, field] of Object.entries(currentSchema)) {
                let isConditionMet = true;

                if (field.show_if) {
                    for (const [depKey, allowedValues] of Object.entries(field.show_if)) {
                        const depInput = document.getElementById(`batch_input_${depKey}`);
                        const currentValue = depInput ? depInput.value : '';
                        if (!allowedValues.includes(currentValue)) {
                            isConditionMet = false;
                            break;
                        }
                    }
                }

                const batchField = document.querySelector(`.conditional-field[data-field="${key}"]`);
                if (batchField) {
                    batchField.style.display = isConditionMet ? 'block' : 'none';
                }

                const headerToToggle = document.querySelector(`.conditional-header[data-field="${key}"]`);
                if (headerToToggle) {
                    headerToToggle.style.display = isConditionMet ? 'table-cell' : 'none';
                }

                const inputEl = document.getElementById(`batch_input_${key}`);
                if (inputEl) {
                    if (isConditionMet && field.required) {
                        inputEl.setAttribute('required', 'required');
                    } else {
                        inputEl.removeAttribute('required');
                    }
                }
            }

            renderUnitBlocks();
        }

        function renderUnitBlocks() {
            const qtyInput = document.getElementById('quantity');
            const container = document.getElementById('unitBlocksContainer');
            const hideGeneral = currentSchema.hide_general || false;
            const hideCondition = currentSchema.hide_condition || false;
            const hideImage = currentSchema.hide_image || false; // 🔥 Definisikan hideImage

            let qty = parseInt(qtyInput.value);
            if (isNaN(qty) || qty < 1) qty = 1;

            container.innerHTML = '';

            for (let i = 0; i < qty; i++) {
                let rowHtml = `<td style="font-weight: 600; text-align: center;">#${i + 1}</td>`;

                if (!hideGeneral) {
                    rowHtml += `<td><input type="text" name="serial_numbers[]" class="form-control" required></td>`;
                }

                for (const [key, field] of Object.entries(currentSchema)) {
                    if (field.in_unit) {
                        let inputHtml = '';
                        let displayStyle = 'table-cell';

                        if (field.show_if) {
                            let isConditionMet = true;
                            for (const [depKey, allowedValues] of Object.entries(field.show_if)) {
                                const depInput = document.getElementById(`batch_input_${depKey}`);
                                const currentValue = depInput ? depInput.value : '';
                                if (!allowedValues.includes(currentValue)) {
                                    isConditionMet = false;
                                    break;
                                }
                            }
                            if (!isConditionMet) displayStyle = 'none';
                        }

                        if (field.type === 'select') {
                            let options = `<option value="">-- Pilih --</option>`;
                            for (const [val, label] of Object.entries(field.options)) {
                                options += `<option value="${val}">${label}</option>`;
                            }
                            inputHtml =
                                `<select name="unit_data[${i}][${key}]" class="form-control" ${field.required ? 'required' : ''}>${options}</select>`;
                        } else if (field.type === 'file') {
                            inputHtml =
                                `<input type="file" name="unit_data[${i}][${key}]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" ${field.required ? 'required' : ''}>`;
                        } else {
                            inputHtml =
                                `<input type="${field.type}" name="unit_data[${i}][${key}]" class="form-control" ${field.required ? 'required' : ''}>`;
                        }

                        rowHtml += `<td style="display: ${displayStyle};">${inputHtml}</td>`;
                    }
                }

                // 🔥 LOGIKA KONDISI
                let conditionHtml = '';
                if (hideCondition) {
                    conditionHtml = `<input type="hidden" name="conditions[]" value="Baik">`;
                } else {
                    conditionHtml = `
                        <select name="conditions[]" class="form-control" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    `;
                }

                // 🔥 RENDER BARIS DENGAN PENGECEKAN
                rowHtml += `
                    <td style="display: ${hideCondition ? 'none' : 'table-cell'};">
                        ${conditionHtml}
                    </td>
                    <td style="display: ${hideImage ? 'none' : 'table-cell'};">
                        <input type="file" name="images[${i}]" class="form-control" accept="image/*">
                    </td>
                `;

                const tr = document.createElement('tr');
                tr.innerHTML = rowHtml;
                container.appendChild(tr);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            handleCategoryChange();
        });
    </script>
@endpush
