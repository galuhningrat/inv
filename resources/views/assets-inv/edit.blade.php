@extends('layouts.app')

@section('title', 'Edit Aset')
@section('page-title', 'Edit Unit Aset: ' . $asset->asset_id)

@section('content')
    @php
        $backUrl = route('assets-inv.group-detail', ['batch_id' => $asset->asset_batch_id]);
    @endphp

    <div class="data-table-container" style="margin-top: 2rem;">
        <div class="table-header">
            <h3 class="table-title">Formulir Edit Unit Aset</h3>
            <a href="{{ $backUrl }}" class="btn btn-secondary">← Kembali</a>
        </div>

        @if ($errors->any())
            <div
                style="background: #fee2e2; border: 1px solid #ef4444; border-radius: 8px; padding: 1rem; margin: 1rem 2rem; color: #991b1b;">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="padding: 2rem;">
            <form action="{{ route('assets-inv.update', $asset) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- INFORMASI BATCH (READ ONLY) --}}
                <div
                    style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 0.5rem; color: #1e40af;">Informasi Batch / Model Aset (Hanya Baca)</h4>
                    <p style="margin: 0 0 0.25rem;"><strong>Nama Model:</strong> {{ $asset->batch->name }}</p>
                    <p style="margin: 0 0 0.25rem;"><strong>Merek / Tipe:</strong> {{ $asset->batch->brand }}
                        {{ $asset->batch->model }}</p>
                    <p style="margin: 0 0 0.25rem;"><strong>Harga Satuan:</strong> Rp
                        {{ number_format($asset->batch->price, 0, ',', '.') }}</p>
                    <p style="margin: 0;"><strong>Tanggal Pembelian:</strong>
                        {{ $asset->batch->purchase_date ? $asset->batch->purchase_date->format('d F Y') : '-' }}</p>
                </div>

                <div class="form-group">
                    <label>Label Aset Kampus</label>
                    <input type="text" class="form-control" value="{{ $asset->asset_id }}" disabled
                        style="font-family: monospace; background: var(--light-bg);">
                </div>

                {{-- 🔥 WADAH UNTUK FIELD DINAMIS UNIT 🔥 --}}
                <div class="form-row">
                    <div class="form-group" id="serialNumberGroup">
                        <label for="serial_number">Nomor Seri Pabrik (S/N) <span style="color: red;">*</span></label>
                        <input type="text" id="serial_number" name="serial_number" class="form-control"
                            value="{{ old('serial_number', $asset->serial_number) }}">
                    </div>
                    <div class="form-group">
                        <label for="condition">Kondisi <span style="color: red;">*</span></label>
                        <select id="condition" name="condition" class="form-control" required>
                            <option value="Baik" {{ old('condition', $asset->condition) === 'Baik' ? 'selected' : '' }}>
                                Baik</option>
                            <option value="Rusak Ringan"
                                {{ old('condition', $asset->condition) === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan
                            </option>
                            <option value="Rusak Berat"
                                {{ old('condition', $asset->condition) === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-row" id="dynamicUnitFields"
                    style="display: none; background: #f8fafc; border: 1px dashed #cbd5e1; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <h4 style="width: 100%; margin-top: 0; margin-bottom: 1rem; font-size: 1rem; color: #1e293b;">⚙️
                        Spesifikasi Tambahan</h4>
                </div>

                <div class="form-group">
                    <label for="penanggung_jawab_id">Penanggung Jawab Aset / PIC</label>
                    <select id="penanggung_jawab_id" name="penanggung_jawab_id" class="form-control">
                        <option value="">-- Tidak ditentukan --</option>
                        @foreach ($usersByLevel as $level => $usersInLevel)
                            <optgroup label="{{ $level }}">
                                @foreach ($usersInLevel as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('penanggung_jawab_id', $asset->penanggung_jawab_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Lokasi Penempatan</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kategori">Kategori <span style="color: red;">*</span></label>
                        <select id="kategori" class="form-control" required onchange="updateUnitOptions()">
                            <option value="">Pilih Kategori</option>
                            @foreach ($units->pluck('category')->unique() as $cat)
                                <option value="{{ $cat }}"
                                    {{ $asset->unit && $asset->unit->category === $cat ? 'selected' : '' }}>
                                    {{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="unit_id">Unit <span style="color: red;">*</span></label>
                        <select id="unit_id" name="unit_id" class="form-control" required disabled
                            onchange="updateLocationOptions()">
                            <option value="">-- Pilih Kategori dulu --</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location_id">Lokasi Spesifik</label>
                    <select id="location_id" name="location_id" class="form-control" disabled>
                        <option value="">-- Pilih Unit dulu --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label id="location_detail_label" for="location_detail">Detail Penempatan</label>
                    <input type="text" id="location_detail" name="location_detail" class="form-control"
                        value="{{ old('location_detail', $asset->location_detail) }}">
                    <small class="form-text" id="location_detail_hint">(opsional, mis. "di dalam lemari")</small>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Gambar Fisik Unit</h4>

                <div class="form-group">
                    @if ($asset->image)
                        <div style="margin-bottom: 1rem;">
                            <img src="{{ $asset->image_url }}" style="max-width: 200px; border-radius: 8px;">
                            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem;">Gambar saat
                                ini</p>
                        </div>
                    @endif
                    <input type="file" id="image" name="image" class="form-control" accept="image/*"
                        onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview"></div>
                </div>

                <div class="btn-group" style="margin-top: 1rem;">
                    <a href="{{ $backUrl }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Update Data Unit</button>
                </div>
            </form>
        </div>
    </div>

    <script id="unitsData" type="application/json"> @json($unitsForJs) </script>
    <script>
        window.assetSchemas = {!! json_encode($schemas) !!};
        window.currentAssetTypeId = {{ $asset->batch->asset_type_id }};
        window.currentUnitData = {!! json_encode($asset->unit_data ?? (object) []) !!};
    </script>
@endsection

@push('scripts')
    <script>
        const allUnits = JSON.parse(document.getElementById('unitsData').textContent);
        const initialUnitId = {{ $asset->unit_id ?? 'null' }};
        const initialLocationId = {{ $asset->location_id ?? 'null' }};
        const initialCategory = @json($asset->unit->category ?? '');

        // --- LOGIKA LOKASI (SAMA SEPERTI SEBELUMNYA) ---
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

            if (initialUnitId) {
                const matchingUnit = allUnits.find(u => u.id === initialUnitId && u.category === kategori);
                if (matchingUnit) {
                    unitSelect.value = initialUnitId;
                    updateLocationOptions();
                }
            }
        }

        function updateLocationOptions() {
            const unitId = parseInt(document.getElementById('unit_id').value);
            const locationSelect = document.getElementById('location_id');
            const locationDetail = document.getElementById('location_detail');
            const detailLabel = document.getElementById('location_detail_label');
            const hint = document.getElementById('location_detail_hint');

            locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';

            if (!unitId) {
                locationSelect.disabled = true;
                locationSelect.required = false;
                return;
            }

            const unit = allUnits.find(u => u.id === unitId);

            if (!unit || unit.locations.length === 0) {
                locationSelect.innerHTML = '<option value="">-- Tidak ada lokasi spesifik --</option>';
                locationSelect.disabled = true;
                locationDetail.required = true;
                detailLabel.innerHTML = 'Detail Penempatan <span style="color:red;">*</span>';
                hint.textContent = '(wajib, unit ini belum punya daftar lokasi spesifik)';
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
                detailLabel.innerHTML = 'Detail Penempatan';
                hint.textContent = '(opsional, mis. "di dalam lemari")';

                if (initialLocationId) {
                    const exists = unit.locations.some(l => l.id === initialLocationId);
                    if (exists) {
                        locationSelect.value = initialLocationId;
                    }
                }
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML =
                        `<img src="${e.target.result}" style="max-width: 200px; border-radius: 8px; margin-top: 1rem;">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }

        // --- LOGIKA FIELD DINAMIS UNIT ---
        function renderDynamicUnitFields() {
            const schema = window.assetSchemas[window.currentAssetTypeId] || {};
            const container = document.getElementById('dynamicUnitFields');
            const serialGroup = document.getElementById('serialNumberGroup');

            // Cek Sembunyikan S/N
            if (schema.hide_general) {
                serialGroup.style.display = 'none';
                document.getElementById('serial_number').removeAttribute('required');
            } else {
                serialGroup.style.display = 'block';
                document.getElementById('serial_number').setAttribute('required', 'required');
            }

            let hasUnitFields = false;
            for (const [key, field] of Object.entries(schema)) {
                if (field.in_unit) {
                    hasUnitFields = true;
                    let val = window.currentUnitData[key] || '';
                    let inputHtml = '';

                    if (field.type === 'select') {
                        let options = `<option value="">-- Pilih --</option>`;
                        for (const [optVal, optLabel] of Object.entries(field.options)) {
                            options += `<option value="${optVal}" ${val == optVal ? 'selected' : ''}>${optLabel}</option>`;
                        }
                        inputHtml =
                            `<select name="unit_data[${key}]" class="form-control" ${field.required ? 'required' : ''}>${options}</select>`;

                    } else if (field.type === 'file') {
                        // 🔥 LOGIKA BARU: TAMPILKAN FILE YANG SUDAH ADA 🔥
                        let existingFilesHtml = '';
                        if (val) {
                            existingFilesHtml +=
                                '<div style="margin-bottom: 0.5rem; padding: 0.5rem; background: #f1f5f9; border-radius: 6px; font-size: 0.85rem;"><strong>File saat ini:</strong><br>';
                            if (Array.isArray(val)) {
                                val.forEach((filePath, idx) => {
                                    existingFilesHtml +=
                                        `<a href="/storage/${filePath}" target="_blank" style="color: #0ea5e9; text-decoration: underline; display: inline-block; margin-right: 10px;">📄 File ${idx + 1}</a>`;
                                });
                            } else {
                                existingFilesHtml +=
                                    `<a href="/storage/${val}" target="_blank" style="color: #0ea5e9; text-decoration: underline;">📄 Lihat File</a>`;
                            }
                            existingFilesHtml += '</div>';
                        }

                        const requiredAttr = (field.required && !val) ? 'required' : '';
                        inputHtml =
                            `${existingFilesHtml}<input type="file" name="unit_data[${key}]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" ${requiredAttr}>`;

                    } else {
                        inputHtml =
                            `<input type="${field.type}" name="unit_data[${key}]" class="form-control" value="${val}" ${field.required ? 'required' : ''}>`;
                    }

                    let display = 'block';
                    if (field.show_if) {
                        display = 'none';
                        for (const [depKey, allowed] of Object.entries(field.show_if)) {
                            display = 'block';
                        }
                    }

                    container.innerHTML += `
                    <div class="form-group conditional-field" style="width: 50%; padding-right: 1rem; box-sizing: border-box; display: ${display};">
                        <label>${field.label} ${field.required && !val ? '<span style="color:red;">*</span>' : ''}</label>
                        ${inputHtml}
                    </div>
                `;
                }
            }

            if (hasUnitFields) container.style.display = 'flex';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const kategoriSelect = document.getElementById('kategori');
            if (initialCategory) kategoriSelect.value = initialCategory;
            updateUnitOptions();
            renderDynamicUnitFields();
        });
    </script>
@endpush
