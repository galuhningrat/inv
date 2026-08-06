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
        <form action="{{ route('requests.store') }}" method="POST" id="requestForm">
            @csrf

            <h4 style="margin-bottom: 1rem;">Informasi Umum</h4>

            <div class="form-row">
                <div class="form-group">
                    <label for="jenis_barang">Jenis Barang <span style="color: red;">*</span></label>
                    <select id="jenis_barang" name="jenis_barang" class="form-control @error('jenis_barang') error @enderror" required>
                        @foreach($jenisBarangOptions as $opt)
                        <option value="{{ $opt }}" {{ old('jenis_barang') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('jenis_barang')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="kategori_barang">Kategori Barang <span style="color: red;">*</span></label>
                    <select id="kategori_barang" name="kategori_barang" class="form-control @error('kategori_barang') error @enderror" required>
                        @foreach($kategoriBarangOptions as $opt)
                        <option value="{{ $opt }}" {{ old('kategori_barang') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('kategori_barang')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="priority">Prioritas <span style="color: red;">*</span></label>
                    <select id="priority" name="priority" class="form-control @error('priority') error @enderror" required>
                        @foreach($priorities as $p)
                        <option value="{{ $p }}" {{ old('priority', 'Normal') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                    @error('priority')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="alasan_pengajuan">Alasan Pengajuan <span style="color: red;">*</span></label>
                    <select id="alasan_pengajuan" name="alasan_pengajuan" class="form-control @error('alasan_pengajuan') error @enderror" required onchange="toggleRelatedAsset()">
                        @foreach($alasanOptions as $opt)
                        <option value="{{ $opt }}" {{ old('alasan_pengajuan') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('alasan_pengajuan')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" id="relatedAssetGroup" style="display: {{ old('alasan_pengajuan') === 'Penggantian' ? 'block' : 'none' }};">
                    <label for="related_asset_id">Aset yang Diganti</label>
                    <select id="related_asset_id" name="related_asset_id" class="form-control @error('related_asset_id') error @enderror">
                        <option value="">-- Pilih Aset --</option>
                        @foreach($assets as $a)
                        <option value="{{ $a->id }}" {{ old('related_asset_id') == $a->id ? 'selected' : '' }}>{{ $a->asset_id }} — {{ $a->name }}</option>
                        @endforeach
                    </select>
                    @error('related_asset_id')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="reason">Alasan / Latar Belakang Pengajuan <span style="color: red;">*</span></label>
                <textarea id="reason" name="reason" class="form-control @error('reason') error @enderror" rows="3" required>{{ old('reason') }}</textarea>
                @error('reason')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
            </div>

            <hr style="margin: 1.5rem 0;">

            <h4 style="margin-bottom: 1rem;">Rincian Barang yang Diajukan</h4>

            <div id="itemsContainer">
                @php $oldItems = old('items', [[]]); @endphp
                @foreach($oldItems as $index => $oldItem)
                <div class="item-row" style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Barang <span style="color: red;">*</span></label>
                            <input type="text" name="items[{{ $index }}][item_name]" class="form-control @error('items.'.$index.'.item_name') error @enderror" value="{{ $oldItem['item_name'] ?? '' }}" required>
                            @error('items.'.$index.'.item_name')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Jenis Aset <span style="color: red;">*</span></label>
                            <select name="items[{{ $index }}][asset_type_id]" class="form-control @error('items.'.$index.'.asset_type_id') error @enderror" required>
                                <option value="">Pilih Jenis</option>
                                @foreach($assetTypes as $type)
                                <option value="{{ $type->id }}" {{ ($oldItem['asset_type_id'] ?? '') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('items.'.$index.'.asset_type_id')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Spesifikasi</label>
                            <input type="text" name="items[{{ $index }}][specification]" class="form-control" value="{{ $oldItem['specification'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Jumlah <span style="color: red;">*</span></label>
                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control @error('items.'.$index.'.quantity') error @enderror" value="{{ $oldItem['quantity'] ?? 1 }}" min="1" required>
                            @error('items.'.$index.'.quantity')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Satuan <span style="color: red;">*</span></label>
                            <input type="text" name="items[{{ $index }}][unit]" class="form-control @error('items.'.$index.'.unit') error @enderror" value="{{ $oldItem['unit'] ?? 'Pcs' }}" required>
                            @error('items.'.$index.'.unit')<div class="error-message" style="display: block;">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Est. Harga/Unit (Rp)</label>
                            <input type="number" name="items[{{ $index }}][estimated_price_per_unit]" class="form-control" value="{{ $oldItem['estimated_price_per_unit'] ?? '' }}" min="0">
                        </div>
                    </div>
                    @if(count($oldItems) > 1)
                    <button type="button" class="btn btn-danger" onclick="this.closest('.item-row').remove()">Hapus Item Ini</button>
                    @endif
                </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-secondary" onclick="addItemRow()" style="margin-bottom: 1.5rem;">+ Tambah Item Barang</button>

            <div class="btn-group">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success">Ajukan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let itemIndex = {
        {
            count($oldItems)
        }
    };

    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const assetTypeOptions = `@foreach($assetTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach`;

        const row = document.createElement('div');
        row.className = 'item-row';
        row.style.cssText = 'border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;';
        row.innerHTML = `
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Barang <span style="color:red;">*</span></label>
                    <input type="text" name="items[${itemIndex}][item_name]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jenis Aset <span style="color:red;">*</span></label>
                    <select name="items[${itemIndex}][asset_type_id]" class="form-control" required>
                        <option value="">Pilih Jenis</option>
                        ${assetTypeOptions}
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Spesifikasi</label>
                    <input type="text" name="items[${itemIndex}][specification]" class="form-control">
                </div>
                <div class="form-group">
                    <label>Jumlah <span style="color:red;">*</span></label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label>Satuan <span style="color:red;">*</span></label>
                    <input type="text" name="items[${itemIndex}][unit]" class="form-control" value="Pcs" required>
                </div>
                <div class="form-group">
                    <label>Est. Harga/Unit (Rp)</label>
                    <input type="number" name="items[${itemIndex}][estimated_price_per_unit]" class="form-control" min="0">
                </div>
            </div>
            <button type="button" class="btn btn-danger" onclick="this.closest('.item-row').remove()">Hapus Item Ini</button>
        `;
        container.appendChild(row);
        itemIndex++;
    }

    function toggleRelatedAsset() {
        const alasan = document.getElementById('alasan_pengajuan').value;
        document.getElementById('relatedAssetGroup').style.display = alasan === 'Penggantian' ? 'block' : 'none';
    }
</script>
@endpush