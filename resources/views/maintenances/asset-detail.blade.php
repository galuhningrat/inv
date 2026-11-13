<div class="maintenance-detail">
    <h4>Informasi Aset</h4>

    <div class="detail-row" style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div class="detail-image" style="flex: 0 0 200px;">
            <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" style="width: 100%; border-radius: 8px; object-fit: cover;">
        </div>
        <div class="detail-info" style="flex: 1;">
            <h4 style="margin-bottom: 1rem;">{{ $asset->name }}</h4>
            <p><strong>ID:</strong> {{ $asset->asset_id }}</p>
            <p><strong>Jenis:</strong> {{ $asset->assetType->name ?? '-' }}</p>
            <p><strong>Merek:</strong> {{ $asset->brand }}</p>
            <p><strong>Nomor Seri:</strong> {{ $asset->serial_number }}</p>
        </div>
    </div>

    <p><strong>Status:</strong>
        <span
            class="status-badge {{ $asset->status === 'Tersedia' ? 'available' : ($asset->status === 'Dipinjam' ? 'borrowed' : 'maintenance') }}">
            {{ $asset->status }}
        </span>
    </p>
    <p><strong>Kondisi:</strong>
        <span
            class="status-badge {{ $asset->condition === 'Baik' ? 'available' : ($asset->condition === 'Rusak Ringan' ? 'borrowed' : 'maintenance') }}">
            {{ $asset->condition }}
        </span>
    </p>
    <p><strong>Lokasi:</strong> {{ $asset->location }}</p>
    <p><strong>Harga:</strong> Rp {{ number_format($asset->price, 0, ',', '.') }}</p>
    <p><strong>Tanggal Pembelian:</strong> {{ \Carbon\Carbon::parse($asset->purchase_date)->format('d F Y') }}</p>

    @if($asset->qrCodes && $asset->qrCodes->count() > 0)
    <div style="text-align: center; margin: 1.5rem 0;">
        <h5>QR Code</h5>
        <div style="display: inline-block; padding: 10px; background: white; border-radius: 8px;">
            <div id="qr-modal-{{ $asset->id }}"></div>
        </div>
        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">
            {{ $asset->qrCodes->first()->code_content }}
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof QRCode !== 'undefined') {
                new QRCode(document.getElementById("qr-modal-{{ $asset->id }}"), {
                    text: "{{ route('asset.detail', ['qrcode' => $asset->qrCodes->first()->code_content]) }}",
                    width: 120,
                    height: 120,
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        });
    </script>
    @endif

    <div class="btn-group" style="margin-top: 1.5rem;">
        <button class="btn btn-primary" onclick="closeModal('assetDetailModal')">Tutup</button>
        @if($asset->qrCodes && $asset->qrCodes->count() > 0)
        <a href="{{ route('qrcodes.print', $asset->qrCodes->first()->id) }}" class="btn btn-secondary"
            target="_blank">Cetak QR Code</a>
        @endif
        <a href="{{ route('assets-inv.show', $asset->id) }}" class="btn btn-success">Lihat Detail Lengkap</a>
    </div>
</div>