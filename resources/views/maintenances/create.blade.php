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
                                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name }} (ID: {{ $asset->asset_id }})
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
                        <label for="maintenance_date">Tanggal Pemeliharaan <span style="color: red;">*</span></label>
                        <input type="date" id="maintenance_date" name="maintenance_date"
                            class="form-control @error('maintenance_date') error @enderror"
                            value="{{ old('maintenance_date', date('Y-m-d')) }}" required>
                        @error('maintenance_date')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="cost">Biaya <span style="color: red;">*</span></label>
                        <input type="number" id="cost" name="cost" class="form-control @error('cost') error @enderror"
                            value="{{ old('cost', 0) }}" min="0" required>
                        @error('cost')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="technician">Nama Teknisi <span style="color: red;">*</span></label>
                        <input type="text" id="technician" name="technician"
                            class="form-control @error('technician') error @enderror" value="{{ old('technician') }}"
                            required>
                        @error('technician')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi Pemeliharaan <span style="color: red;">*</span></label>
                    <textarea id="description" name="description" class="form-control @error('description') error @enderror"
                        rows="3" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection