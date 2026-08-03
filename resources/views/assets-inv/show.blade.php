@extends('layouts.app')

@section('title', 'Detail Aset')
@section('page-title', 'Detail Aset')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Detail Informasi Aset</h3>
            <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <div class="asset-detail">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="detail-image">
                        <img src="{{ $asset->image ? Storage::url($asset->image) : asset('assets/default-asset.png') }}"
                            alt="{{ $asset->name }}" style="width: 100%; border-radius: 12px; box-shadow: var(--shadow);">
                    </div>
                    <div class="detail-info">
                        <h2 style="margin-bottom: 1rem; color: var(--text-primary);">{{ $asset->name }}</h2>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <p><strong>ID Aset:</strong> {{ $asset->asset_id }}</p>
                            <p><strong>Jenis:</strong> {{ $asset->assetType->name ?? '-' }}</p>
                            <p><strong>Merek:</strong> {{ $asset->brand }}</p>
                            <p><strong>Nomor Seri:</strong> {{ $asset->serial_number }}</p>
                            <p><strong>Lokasi:</strong> {{ $asset->location }}</p>
                            <p><strong>Kode QR:</strong> {{ $asset->qr_code }}</p>
                            <p>
                                <strong>Status:</strong>
                                <span class="status-badge {{ $asset->status === 'Tersedia' ? 'available' : ($asset->status === 'Dipinjam' ? 'borrowed' : 'maintenance') }}">
                                    {{ $asset->status }}
                                </span>
                            </p>
                            <p>
                                <strong>Kondisi:</strong>
                                <span class="status-badge {{ $asset->condition === 'Baik' ? 'available' : ($asset->condition === 'Rusak Ringan' ? 'borrowed' : 'maintenance') }}">
                                    {{ $asset->condition }}
                                </span>
                            </p>
                            <p><strong>Harga:</strong> <span class="price-display">Rp {{ number_format($asset->price, 0, ',', '.') }}</span></p>
                            <p><strong>Tanggal Pembelian:</strong> {{ $asset->purchase_date->format('d F Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div style="text-align: center; margin: 2rem 0;">
                    <h4 style="margin-bottom: 1rem;">QR Code Aset</h4>
                    <div id="qrCodeContainer" style="display: inline-block; padding: 15px; background: white; border-radius: 8px; box-shadow: var(--shadow);"></div>
                    <p style="margin-top: 0.5rem; color: var(--text-secondary); font-size: 0.875rem;">{{ $asset->qr_code }}</p>
                </div>

                <div class="btn-group" style="justify-content: center;">
                    <a href="{{ route('assets-inv.edit', $asset) }}" class="btn btn-primary">Edit Aset</a>
                    <button class="btn btn-secondary" onclick="printQrCode()">Cetak QR Code</button>
                    <form action="{{ route('assets-inv.destroy', $asset) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus Aset</button>
                    </form>
                </div>
            </div>

            <!-- Borrowing History -->
            @if($asset->borrowings->count() > 0)
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Riwayat Peminjaman</h4>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Peminjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asset->borrowings as $borrowing)
                                <tr>
                                    <td>{{ $borrowing->borrowing_id }}</td>
                                    <td>{{ $borrowing->borrower_name }}</td>
                                    <td>{{ $borrowing->borrow_date->format('d M Y') }}</td>
                                    <td>{{ $borrowing->actual_return_date ? $borrowing->actual_return_date->format('d M Y') : $borrowing->return_date->format('d M Y') }}</td>
                                    <td>
                                        <span class="status-badge {{ $borrowing->status === 'Selesai' ? 'available' : 'borrowed' }}">
                                            {{ $borrowing->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Maintenance History -->
            @if($asset->maintenances->count() > 0)
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Riwayat Pemeliharaan</h4>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th>Biaya</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asset->maintenances as $maintenance)
                                <tr>
                                    <td>{{ $maintenance->maintenance_id }}</td>
                                    <td>{{ $maintenance->type }}</td>
                                    <td>{{ $maintenance->maintenance_date->format('d M Y') }}</td>
                                    <td>Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="status-badge available">{{ $maintenance->status }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qrContainer = document.getElementById('qrCodeContainer');
            const qrUrl = '{{ route('asset.detail', ['qrcode' => $asset->qr_code]) }}';

            new QRCode(qrContainer, {
                text: qrUrl,
                width: 150,
                height: 150,
                correctLevel: QRCode.CorrectLevel.H
            });
        });

        function printQrCode() {
            const asset = {
                name: '{{ $asset->name }}',
                qrcode: '{{ $asset->qr_code }}'
            };
            const qrUrl = '{{ route('asset.detail', ['qrcode' => $asset->qr_code]) }}';

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak QR Code - ${asset.name}</title>
                    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"><\/script>
                    <style>
                        @page { size: 70mm 45mm; margin: 0; }
                        body { margin: 0; padding: 2mm; font-family: Arial, sans-serif; font-size: 8pt; display: flex; flex-direction: column; justify-content: center; align-items: center; width: 70mm; height: 45mm; }
                        .label-container { text-align: center; }
                        .institution-name { font-weight: bold; font-size: 7pt; margin: 0; }
                        .asset-name { font-weight: bold; font-size: 9pt; margin: 1mm 0; }
                        #qrcode { margin-top: 2mm; }
                        #qrcode img { width: 30mm !important; height: 30mm !important; }
                        .code-number { font-size: 7pt; letter-spacing: 1px; margin-top: 1mm; }
                    </style>
                </head>
                <body>
                    <div class="label-container">
                        <div class="institution-name">Inventaris STT Indonesia Cirebon</div>
                        <div class="asset-name">${asset.name}</div>
                        <div id="qrcode"></div>
                        <div class="code-number">${asset.qrcode}</div>
                    </div>
                    <script>
                        window.onload = function() {
                            new QRCode(document.getElementById("qrcode"), {
                                text: "${qrUrl}",
                                width: 128,
                                height: 128,
                                correctLevel: QRCode.CorrectLevel.H
                            });
                            setTimeout(() => {
                                window.focus();
                                window.print();
                                window.close();
                            }, 500);
                        };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@endpush