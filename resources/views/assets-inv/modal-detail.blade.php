<div class="asset-detail-modal-content">
    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <img src="{{ $asset->image ? Storage::url($asset->image) : asset('assets/default-asset.png') }}"
                alt="{{ $asset->name }}" style="width: 100%; border-radius: 12px; box-shadow: var(--shadow);">
        </div>
        <div>
            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">{{ $asset->name }}</h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">ID Aset</p>
                    <p style="font-weight: 600;">{{ $asset->asset_id }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Jenis</p>
                    <p style="font-weight: 600;">{{ $asset->assetType->name ?? '-' }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Merek</p>
                    <p style="font-weight: 600;">{{ $asset->brand }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Nomor Seri</p>
                    <p style="font-weight: 600; font-size: 0.875rem;">{{ $asset->serial_number }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Lokasi</p>
                    <p style="font-weight: 600;">{{ $asset->location }}</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Harga</p>
                    <p style="font-weight: 600; color: var(--success-color);">Rp
                        {{ number_format($asset->price, 0, ',', '.') }}</p>
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

    <div style="text-align: center; margin: 2rem 0; padding: 1.5rem; background: var(--light-bg); border-radius: 12px;">
        <h5 style="margin-bottom: 1rem;">QR Code</h5>
        <div id="qr-detail-{{ $asset->id }}" style="display: inline-block;"></div>
        @if($asset->qrCodes->count() > 0)
            <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem; font-family: monospace;">
                {{ $asset->qrCodes->first()->code_content }}
            </p>
        @endif
    </div>

    @if($asset->borrowings->count() > 0)
        <div style="margin-top: 1.5rem;">
            <h5 style="margin-bottom: 0.75rem;">Riwayat Peminjaman (5 Terakhir)</h5>
            <div style="max-height: 150px; overflow-y: auto;">
                <table style="width: 100%; font-size: 0.875rem;">
                    <thead>
                        <tr style="background: var(--light-bg);">
                            <th style="padding: 0.5rem; text-align: left;">Peminjam</th>
                            <th style="padding: 0.5rem; text-align: left;">Tanggal</th>
                            <th style="padding: 0.5rem; text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asset->borrowings->take(5) as $borrowing)
                            <tr>
                                <td style="padding: 0.5rem;">{{ $borrowing->borrower_name }}</td>
                                <td style="padding: 0.5rem;">{{ $borrowing->borrow_date->format('d/m/Y') }}</td>
                                <td style="padding: 0.5rem;">
                                    <span class="status-badge {{ $borrowing->status === 'Selesai' ? 'available' : 'borrowed' }}"
                                        style="font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                        {{ $borrowing->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="btn-group" style="margin-top: 2rem; justify-content: center;">
        <button class="btn btn-secondary" onclick="closeAssetModal()">Tutup</button>
        @if($asset->qrCodes->count() > 0)
            <a href="{{ route('qrcodes.print', $asset->qrCodes->first()->id) }}" class="btn btn-primary" target="_blank">
                📄 Cetak QR
            </a>
        @endif
        <a href="{{ route('assets-inv.edit', $asset->id) }}" class="btn btn-warning">
            ✏️ Edit
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($asset->qrCodes->count() > 0)
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