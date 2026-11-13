<div class="form-row">
    <div class="form-group">
        <label>Aset yang Ingin Diperbaiki / Dipelihara <span style="color: red;">*</span></label>
        <select name="items[{{ $index }}][service_data][asset_id]"
            class="form-control js-asset-search"
            data-asset-select="{{ $index }}"
            data-search-url="{{ route('requests.assets.search') }}"
            required>
            <option value="">Ketik kode, nama, merek, atau lokasi aset...</option>

            {{-- PENTING: Pertahankan opsi terpilih saat validasi gagal (old input) --}}
            @if(isset($oldItem['service_data']['asset_id']))
            @php
            $selectedAsset = \App\Models\Asset::with('batch')->find($oldItem['service_data']['asset_id']);
            @endphp
            @if($selectedAsset)
            <option value="{{ $selectedAsset->id }}"
                data-code="{{ $selectedAsset->asset_id }}"
                data-location="{{ $selectedAsset->location ?? '-' }}"
                selected>
                {{ $selectedAsset->asset_id }} — {{ $selectedAsset->batch->name ?? '' }}
            </option>
            @endif
            @endif
        </select>
    </div>

    <div class="form-group">
        <label>Jenis Pemeliharaan <span style="color: red;">*</span></label>
        <select name="items[{{ $index }}][service_data][maintenance_type]" class="form-control" required>
            <option value="">-- Pilih Jenis --</option>
            <option value="Preventif" {{ ($oldItem['service_data']['maintenance_type'] ?? '') === 'Preventif' ? 'selected' : '' }}>Preventif (Pencegahan / Rutin)</option>
            <option value="Kuratif (Ringan)" {{ ($oldItem['service_data']['maintenance_type'] ?? '') === 'Kuratif (Ringan)' ? 'selected' : '' }}>Kuratif (Ringan) — Perbaikan</option>
            <option value="Emergency" {{ ($oldItem['service_data']['maintenance_type'] ?? '') === 'Emergency' ? 'selected' : '' }}>Emergency — Darurat</option>
        </select>
    </div>
</div>

{{-- Info Aset (auto-fill, read-only) --}}
<div
    style="background: var(--light-bg); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 0.75rem; font-size: 0.85rem;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
        <div>
            <span style="color: var(--text-secondary);">Kode Aset:</span>
            <strong id="asset_code_{{ $index }}">—</strong>
        </div>
        <div>
            <span style="color: var(--text-secondary);">Lokasi Aset:</span>
            <strong id="asset_location_{{ $index }}">—</strong>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label>Lokasi Spesifik / Detail Penempatan <span style="color: red;">*</span></label>
        <input type="text" name="items[{{ $index }}][service_data][location]" class="form-control"
            placeholder="Contoh: Lab Komputer 2, Rak B3" required oninput="this.dataset.autofilled = 'false'">
        <small style="color: var(--text-secondary); font-size: 0.75rem;">
            💡 Terisi otomatis dari lokasi aset — silakan edit jika lokasi perbaikan berbeda
        </small>
    </div>
</div>

<div class="form-group">
    <label>Deskripsi Rencana Pemeliharaan</label>
    <textarea name="items[{{ $index }}][service_data][damage_description]" class="form-control" rows="2"
        placeholder="Contoh: Layar berkedip, perlu cek kabel display dan grounding"></textarea>
</div>

<div class="form-row">
    <div class="form-group">
        <label>Estimasi Waktu Mulai (Dari) <span style="color: red;">*</span></label>
        <input type="date" name="items[{{ $index }}][service_data][start_date]" class="form-control"
            required>
    </div>
    <div class="form-group">
        <label>Estimasi Waktu Selesai (Sampai) <span style="color: red;">*</span></label>
        <input type="date" name="items[{{ $index }}][service_data][end_date]" class="form-control" required>
    </div>
</div>

<div class="form-group">
    <label>Catatan / Keterangan</label>
    <textarea name="items[{{ $index }}][service_data][notes]" class="form-control" rows="2"
        placeholder="Catatan tambahan untuk Tim Sarpras / vendor (opsional)"></textarea>
</div>

