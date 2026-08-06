@extends('layouts.app')

@section('title', 'Penerimaan Barang')
@section('page-title', 'Penerimaan Barang')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Terima Barang: {{ $assetRequest->request_id }}</h3>
            <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <div style="padding: 2rem;">
            <p><strong>Diajukan oleh:</strong> {{ $assetRequest->requester->name }}</p>
            <p><strong>Total Item:</strong> {{ $assetRequest->items->count() }} jenis barang,
                {{ $assetRequest->total_quantity }} unit</p>

            <hr style="margin: 1.5rem 0;">

            <form action="{{ route('requests.receive', $assetRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h4 style="margin-bottom: 1rem;">Data Umum (berlaku untuk semua item)</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_date">Tanggal Terima <span style="color:red;">*</span></label>
                        <input type="date" id="purchase_date" name="purchase_date"
                            class="form-control @error('purchase_date') error @enderror"
                            value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="location">Lokasi Penempatan <span style="color:red;">*</span></label>
                        <input type="text" id="location" name="location"
                            class="form-control @error('location') error @enderror" value="{{ old('location') }}" required>
                        @error('location')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="penanggung_jawab_id">Penanggung Jawab</label>
                    <select id="penanggung_jawab_id" name="penanggung_jawab_id" class="form-control">
                        <option value="">-- Pilih Penanggung Jawab --</option>
                        @foreach ($usersByLevel as $level => $usersInLevel)
                            <optgroup label="{{ $level }}">
                                @foreach ($usersInLevel as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('penanggung_jawab_id', $assetRequest->requester_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <hr style="margin: 1.5rem 0;">

                @foreach ($assetRequest->items as $item)
                    <div
                        style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 1rem;">{{ $item->item_name }} ({{ $item->quantity }}
                            {{ $item->unit }})</h4>
                        @if ($item->specification)
                            <p style="color: var(--text-secondary); margin-bottom: 1rem;">Spesifikasi:
                                {{ $item->specification }}</p>
                        @endif

                        <div class="form-row">
                            <div class="form-group">
                                <label>Merek <span style="color:red;">*</span></label>
                                <input type="text" name="brand[{{ $item->id }}]"
                                    class="form-control @error('brand.' . $item->id) error @enderror"
                                    value="{{ old('brand.' . $item->id) }}" required>
                                @error('brand.' . $item->id)
                                    <div class="error-message" style="display:block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Harga per Unit (Rp) <span style="color:red;">*</span></label>
                                <input type="number" name="prices[{{ $item->id }}]"
                                    class="form-control @error('prices.' . $item->id) error @enderror"
                                    value="{{ old('prices.' . $item->id, $item->estimated_price_per_unit) }}"
                                    min="0" required>
                                @error('prices.' . $item->id)
                                    <div class="error-message" style="display:block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Foto Barang (berlaku untuk semua unit item ini) <span
                                        style="color:red;">*</span></label>
                                <input type="file" name="images[{{ $item->id }}]"
                                    class="form-control @error('images.' . $item->id) error @enderror" accept="image/*"
                                    required>
                                @error('images.' . $item->id)
                                    <div class="error-message" style="display:block;">{{ $message }}</div>
                                @enderror
                                <small class="form-text">Format: JPG, PNG, WEBP. Maks 2MB.</small>
                            </div>
                        </div>

                        <p style="font-weight: 600; margin: 1rem 0 0.5rem;">Detail Per Unit:</p>
                        @for ($i = 0; $i < $item->quantity; $i++)
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nomor Seri Unit #{{ $i + 1 }} <span style="color:red;">*</span></label>
                                    <input type="text" name="serial_numbers[{{ $item->id }}][]"
                                        class="form-control @error('serial_numbers.' . $item->id . '.' . $i) error @enderror"
                                        value="{{ old('serial_numbers.' . $item->id . '.' . $i) }}" required>
                                    @error('serial_numbers.' . $item->id . '.' . $i)
                                        <div class="error-message" style="display:block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Kondisi Unit #{{ $i + 1 }} <span style="color:red;">*</span></label>
                                    <select name="conditions[{{ $item->id }}][]" class="form-control" required>
                                        <option value="Baik"
                                            {{ old('conditions.' . $item->id . '.' . $i, 'Baik') === 'Baik' ? 'selected' : '' }}>
                                            Baik</option>
                                        <option value="Rusak Ringan"
                                            {{ old('conditions.' . $item->id . '.' . $i) === 'Rusak Ringan' ? 'selected' : '' }}>
                                            Rusak Ringan</option>
                                        <option value="Rusak Berat"
                                            {{ old('conditions.' . $item->id . '.' . $i) === 'Rusak Berat' ? 'selected' : '' }}>
                                            Rusak Berat</option>
                                    </select>
                                </div>
                            </div>
                        @endfor
                    </div>
                @endforeach

                <div class="btn-group">
                    <a href="{{ route('requests.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Terima & Daftarkan Semua ke Inventaris</button>
                </div>
            </form>
        </div>
    </div>
@endsection
