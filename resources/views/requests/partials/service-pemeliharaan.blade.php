<div class="form-row">
    <div class="form-group">
        <label>Aset yang Ingin Diperbaiki / Dipelihara <span style="color: red;">*</span></label>
        <select name="items[{{ $index }}][service_data][asset_id]" class="form-control"
            data-asset-select="{{ $index }}" onchange="fillAssetInfo({{ $index }}, this)" required>
            <option value="">-- Pilih Aset --</option>
            @foreach ($assets as $asset)
                <option value="{{ $asset->id }}" data-code="{{ $asset->asset_id }}" data-name="{{ $asset->name }}"
                    data-location="{{ $asset->location ?? '-' }}" data-brand="{{ $asset->brand ?? '-' }}">
                    {{ $asset->asset_id }} — {{ $asset->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Jenis Pemeliharaan <span style="color: red;">*</span></label>
        <select name="items[{{ $index }}][service_data][maintenance_type]" class="form-control" required>
            <option value="">-- Pilih Jenis --</option>
            <option value="Preventif">Preventif (Pencegahan / Rutin)</option>
            <option value="Kuratif (Ringan)">Kuratif (Ringan) — Perbaikan</option>
            <option value="Emergency">Emergency — Darurat</option>
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

{{-- Script auto-fill --}}
@push('scripts')
    <script>
        function fillAssetInfo(index, selectEl) {
            const opt = selectEl.options[selectEl.selectedIndex];
            const codeEl = document.getElementById(`asset_code_${index}`);
            const locEl = document.getElementById(`asset_location_${index}`);
            if (!codeEl || !locEl) return;
            codeEl.textContent = opt?.dataset?.code || '—';
            locEl.textContent = opt?.dataset?.location || '—';

            // Auto-fill input "Lokasi Spesifik" dari lokasi aset.
            // Hanya isi kalau input masih kosong ATAU nilainya dari auto-fill sebelumnya.
            // Kalau user sudah edit manual (autofilled = 'false'), jangan ditimpa.
            const locInput = selectEl.closest('.item-row')?.querySelector('input[name*="[service_data][location]"]');
            if (locInput) {
                const currentVal = locInput.value.trim();
                const isAutoFilled = locInput.dataset.autofilled === 'true';
                if (currentVal === '' || isAutoFilled) {
                    locInput.value = opt?.dataset?.location || '';
                    locInput.dataset.autofilled = 'true';
                }
            }
        }

        // Saat halaman dimuat (mis. setelah validasi gagal), isi info untuk yang sudah terpilih
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-asset-select]').forEach(selectEl => {
                const idx = selectEl.getAttribute('data-asset-select');
                if (selectEl.value) fillAssetInfo(idx, selectEl);
            });
        });
    </script>
@endpush
