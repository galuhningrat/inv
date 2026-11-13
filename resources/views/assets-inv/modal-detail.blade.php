<div class="asset-detail-modal-content">
    @php
        $schema = \App\Models\AssetType::getSchema($asset->batch->assetType->code ?? '');
        $batchData = $asset->batch->category_data ?? [];
        $unitData = $asset->unit_data ?? [];
    @endphp

    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <img src="{{ $asset->image ? Storage::url($asset->image) : asset('assets/logo-stti.png') }}"
                alt="{{ $asset->batch->name ?? 'Aset' }}"
                style="width: 100%; border-radius: 12px; box-shadow: var(--shadow);">
        </div>
        <div>
            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">{{ $asset->batch->name ?? '-' }}</h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">ID Aset</p>
                    <p style="font-weight: 600;">{{ $asset->asset_id }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Jenis</p>
                    <p style="font-weight: 600;">{{ $asset->batch->assetType->name ?? '-' }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Merek</p>
                    <p style="font-weight: 600;">{{ $asset->batch->brand ?? '-' }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Nomor Seri
                        Pabrik</p>
                    <p style="font-weight: 600; font-size: 0.875rem;">{{ $asset->serial_number ?? '-' }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Lokasi</p>
                    <p style="font-weight: 600;">{{ $asset->location ?? '-' }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Harga</p>
                    <p style="font-weight: 600; color: var(--success-color);">Rp
                        {{ number_format($asset->batch->price ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Status</p>
                    <span
                        class="status-badge {{ $asset->status === 'Tersedia' ? 'available' : ($asset->status === 'Dipinjam' ? 'borrowed' : 'maintenance') }}">
                        {{ $asset->status }}
                    </span>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Kondisi</p>
                    <span
                        class="status-badge {{ $asset->condition === 'Baik' ? 'available' : ($asset->condition === 'Rusak Ringan' ? 'borrowed' : 'maintenance') }}">
                        {{ $asset->condition }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 🔥 TAMPILKAN SPESIFIKASI DINAMIS 🔥 --}}
    @if (count($batchData) > 0 || count($unitData) > 0)
        <div
            style="background: #f0f9ff; border: 1px solid #bae6fd; border-left: 4px solid #0ea5e9; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 0.95rem; color: #0369a1;">⚙️ Spesifikasi Khusus
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">

                {{-- Data Batch --}}
                @foreach ($batchData as $key => $value)
                    @if (isset($schema[$key]) && !empty($value))
                        <div>
                            <span style="color: #0369a1;">{{ $schema[$key]['label'] ?? $key }}:</span><br>
                            <strong>{{ is_array($value) ? implode(', ', $value) : $value }}</strong>
                        </div>
                    @endif
                @endforeach

                {{-- Data Unit --}}
                @foreach ($unitData as $key => $value)
                    @if (isset($schema[$key]) && !empty($value))
                        <div>
                            <span style="color: #0369a1;">{{ $schema[$key]['label'] ?? $key }}:</span><br>
                            <strong>{{ is_array($value) ? implode(', ', $value) : $value }}</strong>
                        </div>
                    @endif
                @endforeach

            </div>
        </div>
    @endif

    <div style="text-align: center; margin: 2rem 0; padding: 1.5rem; background: var(--light-bg); border-radius: 12px;">
        <h5 style="margin-bottom: 1rem;">QR Code</h5>
        <div id="qr-detail-{{ $asset->id }}" style="display: inline-block;"></div>
        @if ($asset->qrCodes->count() > 0)
            <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem; font-family: monospace;">
                {{ $asset->qrCodes->first()->code_content }}
            </p>
        @endif
    </div>

    <div class="btn-group" style="margin-top: 2rem; justify-content: center;">
        <button class="btn btn-secondary" onclick="closeAssetModal()">Tutup</button>
        @if ($asset->qrCodes->count() > 0)
            <a href="{{ route('qrcodes.print', $asset->qrCodes->first()->id) }}" class="btn btn-primary"
                target="_blank">
                📄 Cetak QR
            </a>
        @endif
        <a href="{{ route('assets-inv.edit', $asset->id) }}" class="btn btn-warning">
            ✏️ Edit
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if ($asset->qrCodes->count() > 0)
            if (typeof QRCode !== 'undefined') {
                new QRCode(document.getElementById("qr-detail-{{ $asset->id }}"), {
                    text: "{{ route('asset.detail', ['qrcode' => $asset->qrCodes->first()->code_content]) }}",
                    width: 150,
                    height: 150,
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        @endif
    });
</script>
