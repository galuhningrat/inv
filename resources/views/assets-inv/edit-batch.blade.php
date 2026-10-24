@extends('layouts.app')

@section('title', 'Edit Info Batch')
@section('page-title', 'Edit Info Batch: ' . $batch->name)

@section('content')
    <div class="data-table-container" style="margin-top: 2rem;">
        <div class="table-header">
            <h3 class="table-title">Formulir Edit Informasi Induk (Batch)</h3>
            <a href="{{ route('assets-inv.group-detail', ['batch_id' => $batch->id]) }}" class="btn btn-secondary">←
                Kembali</a>
        </div>

        <div style="padding: 2rem;">
            @if ($errors->any())
                <div
                    style="background: #fee2e2; border: 1px solid #ef4444; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; color: #991b1b;">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div
                style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <p style="margin: 0; font-size: 0.9rem; color: #1e40af;">
                    ⚠️ <strong>Perhatian:</strong> Halaman ini mengubah data untuk <strong>seluruh unit</strong> yang ada di
                    dalam grup ini.
                </p>
            </div>

            <form action="{{ route('assets-inv.update-batch', $batch->id) }}" method="POST" id="editBatchForm">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Model / Aset <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', $batch->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="asset_type_id">Jenis Aset <span style="color: red;">*</span></label>
                        {{-- Hapus onchange di sini karena kita pakai EventListener di JS --}}
                        <select id="asset_type_id" name="asset_type_id" class="form-control" required>
                            @foreach (\App\Models\AssetType::getOrdered() as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('asset_type_id', $batch->asset_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group general-brand-group">
                        <label for="brand">Merek <span style="color: red;">*</span></label>
                        <input type="text" id="brand" name="brand" class="form-control"
                            value="{{ old('brand', $batch->brand) }}">
                    </div>
                    <div class="form-group general-model-group">
                        <label for="model">Tipe / Model</label>
                        <input type="text" id="model" name="model" class="form-control"
                            value="{{ old('model', $batch->model) }}">
                    </div>
                    <div class="form-group">
                        <label for="price">Harga Satuan (Rp) <span style="color: red;">*</span></label>
                        <input type="number" id="price" name="price" class="form-control"
                            value="{{ old('price', $batch->price) }}" min="0" step="any" onwheel="this.blur()"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="purchase_date">Tanggal Pembelian <span style="color: red;">*</span></label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-control"
                        value="{{ old('purchase_date', $batch->purchase_date->format('Y-m-d')) }}" required>
                </div>

                {{-- WADAH UNTUK FIELD DINAMIS BATCH --}}
                <div id="dynamicBatchFields"
                    style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: none;">
                    <h4
                        style="margin-top: 0; margin-bottom: 1rem; font-size: 1rem; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
                        ⚙️ Spesifikasi Khusus Kategori
                    </h4>
                    <div class="form-row" id="batchFieldsContainer" style="flex-wrap: wrap;"></div>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Data Akuntansi <small
                        style="font-weight: normal; color: var(--text-secondary);">(opsional)</small></h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="funding_source">Sumber Pendanaan</label>
                        <select id="funding_source" name="funding_source" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach (['Dana Yayasan', 'Hibah/Bantuan Pemerintah (LLDIKTI)', 'Dana Mandiri/UKT Mahasiswa', 'Kerja Sama Industri'] as $fs)
                                <option value="{{ $fs }}"
                                    {{ old('funding_source', $batch->funding_source) === $fs ? 'selected' : '' }}>
                                    {{ $fs }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="economic_life_years">Umur Ekonomis (Tahun)</label>
                        <input type="number" id="economic_life_years" name="economic_life_years" class="form-control"
                            value="{{ old('economic_life_years', $batch->economic_life_years) }}" min="1">
                    </div>
                    <div class="form-group">
                        <label for="residual_value">Nilai Residu (Rp)</label>
                        <input type="number" id="residual_value" name="residual_value" class="form-control"
                            value="{{ old('residual_value', $batch->residual_value) }}" min="0">
                    </div>
                </div>

                <div class="btn-group" style="margin-top: 1rem;">
                    <a href="{{ route('assets-inv.group-detail', ['batch_id' => $batch->id]) }}"
                        class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Update Info Batch</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.assetSchemas = {!! json_encode(
            \App\Models\AssetType::all()->mapWithKeys(fn($t) => [$t->id => \App\Models\AssetType::getSchema($t->code)]),
        ) !!};
        window.oldBatchData = {!! json_encode($batch->category_data ?? (object) []) !!};
    </script>
@endsection

@push('scripts')
    <script>
        // 1. Fungsi Render (Hanya dipanggil 1x saat halaman dimuat)
        function renderBatchFields() {
            const typeId = document.getElementById('asset_type_id').value;
            const schema = window.assetSchemas[typeId] || {};
            const container = document.getElementById('batchFieldsContainer');
            const wrapper = document.getElementById('dynamicBatchFields');
            const brandGroup = document.querySelector('.general-brand-group');
            const modelGroup = document.querySelector('.general-model-group');

            container.innerHTML = '';
            wrapper.style.display = 'none';
            brandGroup.style.display = 'block';
            modelGroup.style.display = 'block';

            if (schema.hide_general) {
                brandGroup.style.display = 'none';
                modelGroup.style.display = 'none';
                document.getElementById('brand').removeAttribute('required');
            } else {
                document.getElementById('brand').setAttribute('required', 'required');
            }

            let hasFields = false;
            for (const [key, field] of Object.entries(schema)) {
                if (field.in_batch) {
                    hasFields = true;
                    let val = window.oldBatchData[key] || '';
                    let inputHtml = '';

                    if (field.type === 'select') {
                        let options = `<option value="">-- Pilih --</option>`;
                        for (const [optVal, optLabel] of Object.entries(field.options)) {
                            options += `<option value="${optVal}" ${val == optVal ? 'selected' : ''}>${optLabel}</option>`;
                        }
                        inputHtml =
                            `<select name="category_data[${key}]" id="batch_input_${key}" class="form-control" onchange="toggleDependencies()" ${field.required ? 'required' : ''}>${options}</select>`;
                    } else if (field.type === 'textarea') {
                        inputHtml =
                            `<textarea name="category_data[${key}]" id="batch_input_${key}" class="form-control" rows="2" ${field.required ? 'required' : ''}>${val}</textarea>`;
                    } else {
                        inputHtml =
                            `<input type="${field.type}" name="category_data[${key}]" id="batch_input_${key}" class="form-control" value="${val}" ${field.required ? 'required' : ''}>`;
                    }

                    const colSize = field.type === 'textarea' ? '100%' : '33.33%';

                    // Sembunyikan dulu jika punya syarat show_if, nanti toggleDependencies yang memunculkan
                    let display = 'block';
                    if (field.show_if) display = 'none';

                    container.innerHTML += `
                    <div class="form-group conditional-field" data-field="${key}" style="width: ${colSize}; padding-right: 1rem; box-sizing: border-box; display: ${display};">
                        <label>${field.label} ${field.required ? '<span style="color:red;">*</span>' : ''}</label>
                        ${inputHtml}
                    </div>
                `;
                }
            }

            if (hasFields) wrapper.style.display = 'block';

            // Panggil toggle setelah render untuk ngecek kondisi awal
            toggleDependencies();
        }

        // 2. Fungsi Toggle (Hanya menyembunyikan/menampilkan, TIDAK menghapus form)
        function toggleDependencies() {
            const typeId = document.getElementById('asset_type_id').value;
            const schema = window.assetSchemas[typeId] || {};

            for (const [key, field] of Object.entries(schema)) {
                if (field.show_if) {
                    let isConditionMet = true;

                    for (const [depKey, allowedValues] of Object.entries(field.show_if)) {
                        // Baca nilai dari dropdown yang SAAT INI aktif di layar
                        const depInput = document.querySelector(`[name="category_data[${depKey}]"]`);
                        const currentValue = depInput ? depInput.value : '';

                        if (!allowedValues.includes(currentValue)) {
                            isConditionMet = false;
                            break;
                        }
                    }

                    const fieldWrapper = document.querySelector(`.conditional-field[data-field="${key}"]`);
                    if (fieldWrapper) {
                        fieldWrapper.style.display = isConditionMet ? 'block' : 'none';
                    }
                }
            }
        }

        // 3. Event Listener
        document.addEventListener('DOMContentLoaded', function() {
            // Gambar form pertama kali
            renderBatchFields();

            // Gambar ulang HANYA jika Jenis Aset utama diganti
            document.getElementById('asset_type_id').addEventListener('change', renderBatchFields);
        });
    </script>
@endpush
