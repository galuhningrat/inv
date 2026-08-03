@extends('layouts.app')

@section('title', 'Edit Aset')
@section('page-title', 'Edit Aset')

@section('content')
    <div class="data-table-container">
        <div class="table-header" style="flex-wrap: wrap; gap: 1rem;">
            <h3 class="table-title" style="flex: 1; min-width: 200px;">Edit Aset: {{ $asset->name }}</h3>
            <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 1rem;">
            <form action="{{ route('assets-inv.update', $asset) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Nama Aset & Jenis Aset -->
                <div class="form-row"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="name">Nama Aset <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') error @enderror"
                            value="{{ old('name', $asset->name) }}" required>
                        @error('name')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="asset_type_id">Jenis Aset <span style="color: red;">*</span></label>
                        <select id="asset_type_id" name="asset_type_id"
                            class="form-control @error('asset_type_id') error @enderror" required>
                            <option value="">Pilih Jenis</option>
                            @foreach($assetTypes as $type)
                                <option value="{{ $type->id }}" {{ old('asset_type_id', $asset->asset_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('asset_type_id')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Merek & Nomor Seri -->
                <div class="form-row"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="brand">Merek <span style="color: red;">*</span></label>
                        <input type="text" id="brand" name="brand" class="form-control @error('brand') error @enderror"
                            value="{{ old('brand', $asset->brand) }}" required>
                        @error('brand')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="serial_number">Nomor Seri/Kode Unik <span style="color: red;">*</span></label>
                        <input type="text" id="serial_number" name="serial_number"
                            class="form-control @error('serial_number') error @enderror"
                            value="{{ old('serial_number', $asset->serial_number) }}" required>
                        @error('serial_number')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Harga & Tanggal Pembelian -->
                <div class="form-row"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="price">Harga Pembelian <span style="color: red;">*</span></label>
                        <input type="number" id="price" name="price" class="form-control @error('price') error @enderror"
                            value="{{ old('price', $asset->price) }}" min="0" required>
                        @error('price')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="purchase_date">Tanggal Pembelian <span style="color: red;">*</span></label>
                        <input type="date" id="purchase_date" name="purchase_date"
                            class="form-control @error('purchase_date') error @enderror"
                            value="{{ old('purchase_date', $asset->purchase_date->format('Y-m-d')) }}" required>
                        @error('purchase_date')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Lokasi & Kondisi -->
                <div class="form-row"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="location">Lokasi <span style="color: red;">*</span></label>
                        <select id="location" name="location" class="form-control @error('location') error @enderror"
                            required>
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}" {{ old('location', $asset->location) === $loc ? 'selected' : '' }}>
                                    {{ $loc }}
                                </option>
                            @endforeach
                        </select>
                        @error('location')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="condition">Kondisi <span style="color: red;">*</span></label>
                        <select id="condition" name="condition" class="form-control @error('condition') error @enderror"
                            required>
                            <option value="">Pilih Kondisi</option>
                            <option value="Baik" {{ old('condition', $asset->condition) === 'Baik' ? 'selected' : '' }}>Baik
                            </option>
                            <option value="Rusak Ringan" {{ old('condition', $asset->condition) === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Berat" {{ old('condition', $asset->condition) === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                        @error('condition')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Gambar Aset -->
                <div class="form-group">
                    <label for="image">Gambar Aset</label>
                    @if($asset->image)
                        <div style="margin-bottom: 1rem;">
                            <img src="{{ Storage::url($asset->image) }}" alt="{{ $asset->name }}"
                                style="max-width: 200px; border-radius: 8px;">
                            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem;">Gambar saat ini
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

                <!-- Tombol Aksi -->
                <div class="btn-group" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem;">
                    <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; height: auto; border-radius: 8px; margin-top: 1rem;">`;
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
@endpush

<style>
    /* Responsive adjustments for Edit Aset */
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