@extends('layouts.app')

@section('title', 'Catat Pemeliharaan')
@section('page-title', 'Catat Pemeliharaan')

@section('content')
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Catat Pemeliharaan</h3>
        <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>
    <div style="padding: 2rem;">

        {{-- Pemberitahuan alur verifikasi --}}
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 1.5rem;">
            <p style="margin: 0; font-size: 0.9rem; color: #1e40af;">
                ℹ️ <strong>Alur Verifikasi:</strong> Setelah disimpan, tiket akan berstatus
                <strong>"Menunggu Verifikasi"</strong>. Kalab / PJ Sarpras / Admin akan memverifikasi
                laporan sebelum diteruskan ke Tim Pemeliharaan.
            </p>
        </div>

        <form action="{{ route('maintenances.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="asset_id">Pilih Aset <span style="color: red;">*</span></label>
                    <select id="asset_id" name="asset_id" class="form-control @error('asset_id') error @enderror"
                        required>
                        <option value="">Pilih Aset</option>
                        @foreach($assets as $typeName => $assetList)
                        <optgroup label="{{ $typeName }}">
                            @foreach($assetList as $asset)
                            <option value="{{ $asset->id }}" {{ (old('asset_id') ?? $preselectedAssetId) == $asset->id ? 'selected' : '' }}>
                                {{ $asset->batch->name ?? '-' }} (ID: {{ $asset->asset_id }})
                            </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                    @error('asset_id')
                    <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="type">Jenis Pemeliharaan <span style="color: red;">*</span></label>
                    <select id="type" name="type" class="form-control @error('type') error @enderror" required>
                        <option value="">Pilih Jenis</option>
                        @foreach($maintenanceTypes as $mType)
                        <option value="{{ $mType }}" {{ old('type') === $mType ? 'selected' : '' }}>{{ $mType }}</option>
                        @endforeach
                    </select>
                    @error('type')
                    <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="maintenance_date">Tanggal Laporan <span style="color: red;">*</span></label>
                    <input type="date" id="maintenance_date" name="maintenance_date"
                        class="form-control @error('maintenance_date') error @enderror"
                        value="{{ old('maintenance_date', date('Y-m-d')) }}" required>
                    @error('maintenance_date')
                    <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="technician">Nama Pelapor <span style="color: red;">*</span></label>
                    <input type="text" id="technician" name="technician"
                        class="form-control @error('technician') error @enderror" value="{{ old('technician', Auth::user()->name) }}" required>
                    @error('technician')
                    <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description">Deskripsi Kerusakan / Keluhan <span style="color: red;">*</span></label>
                <textarea id="description" name="description" class="form-control @error('description') error @enderror"
                    rows="4" placeholder="Jelaskan gejala kerusakan, kapan mulai terjadi, dan kondisi terkini..." required>{{ old('description') }}</textarea>
                @error('description')
                <div class="error-message" style="display: block;">{{ $message }}</div>
                @enderror
            </div>

            <div class="btn-group">
                <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success">Kirim Laporan</button>
            </div>
        </form>
    </div>
</div>
@endsection