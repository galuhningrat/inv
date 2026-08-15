@extends('layouts.app')

@section('title', 'Edit Aset')
@section('page-title', 'Edit Aset: ' . $asset->name)

@section('content')
    @php
        $groupParams = collect(request()->only(['group_name', 'group_type_id', 'group_brand']))->filter();
        $hasGroupContext = $groupParams->count() === 3 && is_numeric($groupParams['group_type_id'] ?? null);
        $backUrl = $hasGroupContext
            ? route('assets-inv.group-detail', [
                'name' => $groupParams['group_name'],
                'type_id' => $groupParams['group_type_id'],
                'brand' => $groupParams['group_brand'],
            ])
            : route('assets-inv.index');
    @endphp

    <div class="data-table-container" style="margin-top: 2rem;">
        <div class="table-header">
            <h3 class="table-title">Formulir Edit Aset Fisik</h3>
            <a href="{{ $backUrl }}" class="btn btn-secondary">← Kembali</a>
        </div>

        {{-- Tampilkan semua error global --}}
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

                @if ($hasGroupContext)
                    <input type="hidden" name="group_name" value="{{ $groupParams['group_name'] }}">
                    <input type="hidden" name="group_type_id" value="{{ $groupParams['group_type_id'] }}">
                    <input type="hidden" name="group_brand" value="{{ $groupParams['group_brand'] }}">
                @endif

                {{-- ============================================================ --}}
                {{--  INFORMASI UTAMA                                            --}}
                {{-- ============================================================ --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Aset <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') error @enderror" value="{{ old('name', $asset->name) }}"
                            required>
                        @error('name')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="asset_type_id">Jenis Aset <span style="color: red;">*</span></label>
                        <select id="asset_type_id" name="asset_type_id"
                            class="form-control @error('asset_type_id') error @enderror" required>
                            <option value="">Pilih Jenis</option>
                            @foreach ($assetTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('asset_type_id', $asset->asset_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('asset_type_id')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="brand">Merek <span style="color: red;">*</span></label>
                        <input type="text" id="brand" name="brand"
                            class="form-control @error('brand') error @enderror" value="{{ old('brand', $asset->brand) }}"
                            required>
                        @error('brand')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="price">Harga Pembelian <span style="color: red;">*</span></label>
                        <input type="number" id="price" name="price"
                            class="form-control @error('price') error @enderror" value="{{ old('price', $asset->price) }}"
                            min="0" required>
                        @error('price')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="purchase_date">Tanggal Pembelian <span style="color: red;">*</span></label>
                    <input type="date" id="purchase_date" name="purchase_date"
                        class="form-control @error('purchase_date') error @enderror"
                        value="{{ old('purchase_date', $asset->purchase_date->format('Y-m-d')) }}" required>
                    @error('purchase_date')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
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
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Lokasi Penempatan</h4>

                {{-- ============================================================ --}}
                {{--  LOKASI PENEMPATAN                                            --}}
                {{-- ============================================================ --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="kategori">Kategori <span style="color: red;">*</span></label>
                        <select id="kategori" class="form-control" required onchange="updateUnitOptions()">
                            <option value="">Pilih Kategori</option>
                            @foreach ($units->pluck('category')->unique() as $cat)
                                <option value="{{ $cat }}"
                                    {{ $asset->unit && $asset->unit->category === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
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
                    <label for="location_id">Lokasi Spesifik</label>
                    <select id="location_id" name="location_id"
                        class="form-control @error('location_id') error @enderror" disabled>
                        <option value="">-- Pilih Unit dulu --</option>
                    </select>
                    @error('location_id')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label id="location_detail_label" for="location_detail">Detail Penempatan</label>
                    <input type="text" id="location_detail" name="location_detail"
                        class="form-control @error('location_detail') error @enderror"
                        value="{{ old('location_detail', $asset->location_detail) }}">
                    @error('location_detail')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                    <small class="form-text" id="location_detail_hint">(opsional, mis. "di dalam lemari")</small>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Detail Unit</h4>

                {{-- ============================================================ --}}
                {{--  DETAIL UNIT                                                 --}}
                {{-- ============================================================ --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="serial_number">Nomor Seri / Kode Unik <span style="color: red;">*</span></label>
                        <input type="text" id="serial_number" name="serial_number"
                            class="form-control @error('serial_number') error @enderror"
                            value="{{ old('serial_number', $asset->serial_number) }}" required>
                        @error('serial_number')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="condition">Kondisi <span style="color: red;">*</span></label>
                        <select id="condition" name="condition" class="form-control @error('condition') error @enderror"
                            required>
                            <option value="">Pilih Kondisi</option>
                            <option value="Baik" {{ old('condition', $asset->condition) === 'Baik' ? 'selected' : '' }}>
                                Baik
                            </option>
                            <option value="Rusak Ringan"
                                {{ old('condition', $asset->condition) === 'Rusak Ringan' ? 'selected' : '' }}>
                                Rusak Ringan
                            </option>
                            <option value="Rusak Berat"
                                {{ old('condition', $asset->condition) === 'Rusak Berat' ? 'selected' : '' }}>
                                Rusak Berat
                            </option>
                        </select>
                        @error('condition')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Gambar Aset</h4>

                <div class="form-group">
                    @if ($asset->image)
                        <div style="margin-bottom: 1rem;">
                            <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}"
                                style="max-width: 200px; border-radius: 8px; box-shadow: var(--shadow);">
                            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem;">
                                Gambar saat ini
                            </p>
                        </div>
                    @endif
                    <input type="file" id="image" name="image" class="form-control" accept="image/*"
                        onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview"></div>
                    <small class="form-text">Upload gambar baru untuk mengganti (opsional, max 2MB)</small>
                    @error('image')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <a href="{{ $backUrl }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Update Aset</button>
                </div>
            </form>
        </div>
    </div>

    <script id="unitsData" type="application/json">
        @json($unitsForJs)
    </script>
@endsection

@push('styles')
    <style>
        @media (max-width: 768px) {
            .data-table-container {
                margin: 0.5rem;
            }

            .table-header {
                flex-direction: column;
                align-items: stretch !important;
            }

            .table-title {
                font-size: 1.1rem;
            }

            .form-row {
                grid-template-columns: 1fr !important;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        const allUnits = JSON.parse(document.getElementById('unitsData').textContent);

        const initialUnitId = {{ $asset->unit_id ?? 'null' }};
        const initialLocationId = {{ $asset->location_id ?? 'null' }};
        const initialCategory = @json($asset->unit->category ?? '');

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
                locationSelect.innerHTML = '<option value="">-- Tidak ada lokasi spesifik untuk unit ini --</option>';
                locationSelect.disabled = true;
                locationSelect.required = false;
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
                        `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; height: auto; border-radius: 8px; margin-top: 1rem;">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const kategoriSelect = document.getElementById('kategori');
            if (initialCategory) {
                kategoriSelect.value = initialCategory;
            }
            updateUnitOptions();
        });
    </script>
@endpush
