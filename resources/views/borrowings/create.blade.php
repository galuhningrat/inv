@extends('layouts.app')

@section('title', 'Pinjam Aset')
@section('page-title', 'Pinjam Aset')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Pinjam Aset</h3>
            <a href="{{ route('borrowings.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <form action="{{ route('borrowings.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="borrower_name">Nama Peminjam <span style="color: red;">*</span></label>
                        <input type="text" id="borrower_name" name="borrower_name"
                            class="form-control @error('borrower_name') error @enderror" value="{{ old('borrower_name') }}"
                            required>
                        @error('borrower_name')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="borrower_role">Jabatan <span style="color: red;">*</span></label>
                        <select id="borrower_role" name="borrower_role"
                            class="form-control @error('borrower_role') error @enderror" required>
                            <option value="">Pilih Jabatan</option>
                            @foreach($borrowerRoles as $role)
                                <option value="{{ $role }}" {{ old('borrower_role') === $role ? 'selected' : '' }}>{{ $role }}
                                </option>
                            @endforeach
                        </select>
                        @error('borrower_role')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="asset_id">Pilih Aset <span style="color: red;">*</span></label>
                    <select id="asset_id" name="asset_id" class="form-control @error('asset_id') error @enderror" required>
                        <option value="">Pilih Aset</option>
                        @foreach($availableAssets as $typeName => $assets)
                            <optgroup label="{{ $typeName }}">
                                @foreach($assets as $asset)
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

                <div class="form-row">
                    <div class="form-group">
                        <label for="borrow_date">Tanggal Pinjam <span style="color: red;">*</span></label>
                        <input type="date" id="borrow_date" name="borrow_date"
                            class="form-control @error('borrow_date') error @enderror"
                            value="{{ old('borrow_date', date('Y-m-d')) }}" required>
                        @error('borrow_date')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="return_date">Tanggal Kembali <span style="color: red;">*</span></label>
                        <input type="date" id="return_date" name="return_date"
                            class="form-control @error('return_date') error @enderror"
                            value="{{ old('return_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                        @error('return_date')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="purpose">Tujuan Peminjaman <span style="color: red;">*</span></label>
                    <textarea id="purpose" name="purpose" class="form-control @error('purpose') error @enderror" rows="3"
                        required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <a href="{{ route('borrowings.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Pinjam</button>
                </div>
            </form>
        </div>
    </div>
@endsection