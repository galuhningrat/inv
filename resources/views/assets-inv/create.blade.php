@extends('layouts.app')

@section('title', 'Tambah Aset')
@section('page-title', 'Tambah Aset Baru')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Tambah Aset Baru</h3>
            <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <form action="{{ route('assets-inv.store') }}" method="POST" enctype="multipart/form-data" id="assetForm">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Aset <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') error @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="asset_type_id">Jenis Aset <span style="color: red;">*</span></label>
                        <select id="asset_type_id" name="asset_type_id"
                            class="form-control @error('asset_type_id') error @enderror" required>
                            <option value="">Pilih Jenis</option>
                            @foreach($assetTypes as $type)
                                <option value="{{ $type->id }}" {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
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
                        <input type="text" id="brand" name="brand" class="form-control @error('brand') error @enderror"
                            value="{{ old('brand') }}" required>
                        @error('brand')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="serial_number">Nomor Seri/Kode Unik <span style="color: red;">*</span></label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="serial_number" name="serial_number"
                                class="form-control @error('serial_number') error @enderror"
                                value="{{ old('serial_number') }}" required>
                            <button type="button" class="btn btn-secondary" onclick="generateQrCode()"
                                style="white-space: nowrap;">
                                Generate
                            </button>
                        </div>
                        @error('serial_number')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Harga Pembelian <span style="color: red;">*</span></label>
                        <input type="number" id="price" name="price" class="form-control @error('price') error @enderror"
                            value="{{ old('price') }}" min="0" required>
                        @error('price')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
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

                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Lokasi <span style="color: red;">*</span></label>
                        <select id="location" name="location" class="form-control @error('location') error @enderror"
                            required>
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}" {{ old('location') === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>
                        @error('location')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="condition">Kondisi <span style="color: red;">*</span></label>
                        <select id="condition" name="condition" class="form-control @error('condition') error @enderror"
                            required>
                            <option value="">Pilih Kondisi</option>
                            <option value="Baik" {{ old('condition') === 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak Ringan" {{ old('condition') === 'Rusak Ringan' ? 'selected' : '' }}>Rusak
                                Ringan</option>
                            <option value="Rusak Berat" {{ old('condition') === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat
                            </option>
                        </select>
                        @error('condition')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Gambar Aset</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*"
                        onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview"></div>
                    <small class="form-text">Upload gambar aset (opsional, max 2MB)</small>
                    @error('image')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function generateQrCode() {
            const typeSelect = document.getElementById('asset_type_id');
            if (!typeSelect.value) {
                showToast('Pilih jenis aset terlebih dahulu', 'error');
                return;
            }

            fetch('{{ route('assets.generate-qrcode') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ asset_type_id: typeSelect.value })
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('serial_number').value = data.qr_code;
                    showToast('Kode unik berhasil dibuat!', 'success');
                })
                .catch(error => {
                    showToast('Gagal membuat kode unik', 'error');
                });
        }

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