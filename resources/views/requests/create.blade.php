@extends('layouts.app')

@section('title', 'Ajukan Aset')
@section('page-title', 'Ajukan Aset Baru')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Ajukan Aset Baru</h3>
            <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <form action="{{ route('requests.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="asset_name">Nama Aset <span style="color: red;">*</span></label>
                        <input type="text" id="asset_name" name="asset_name"
                            class="form-control @error('asset_name') error @enderror" value="{{ old('asset_name') }}"
                            required>
                        @error('asset_name')
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
                                    {{ $type->name }}
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
                        <label for="quantity">Jumlah <span style="color: red;">*</span></label>
                        <input type="number" id="quantity" name="quantity"
                            class="form-control @error('quantity') error @enderror" value="{{ old('quantity', 1) }}" min="1"
                            required>
                        @error('quantity')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="estimated_price">Estimasi Harga (per unit)</label>
                        <input type="number" id="estimated_price" name="estimated_price"
                            class="form-control @error('estimated_price') error @enderror"
                            value="{{ old('estimated_price') }}" min="0">
                        @error('estimated_price')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="priority">Prioritas <span style="color: red;">*</span></label>
                        <select id="priority" name="priority" class="form-control @error('priority') error @enderror"
                            required>
                            @foreach($priorities as $p)
                                <option value="{{ $p }}" {{ old('priority', 'Sedang') === $p ? 'selected' : '' }}>{{ $p }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason">Alasan Pengajuan <span style="color: red;">*</span></label>
                    <textarea id="reason" name="reason" class="form-control @error('reason') error @enderror" rows="4"
                        required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <a href="{{ route('requests.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
@endsection