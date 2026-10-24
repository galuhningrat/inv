@extends('layouts.app')

@section('title', 'Detail Aset')
@section('page-title', 'Detail Aset')

@section('content')
    @php
        $backUrl = route('assets-inv.group-detail', ['batch_id' => $asset->asset_batch_id]);
        $schema = \App\Models\AssetType::getSchema($asset->batch->assetType->code ?? '');
        $hideGeneral = $schema['hide_general'] ?? false;
        $batchData = $asset->batch->category_data ?? [];
        $unitData = $asset->unit_data ?? [];
    @endphp

    <div class="data-table-container" style="max-width: 1100px; margin: 0 auto;">
        <div class="table-header">
            <h3 class="table-title">Detail Informasi Aset</h3>
            <a href="{{ $backUrl }}" class="btn btn-secondary">← Kembali ke Grup</a>
        </div>

        <div style="padding: 2rem; display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap;">

            {{-- 🔥 KOLOM KIRI: FOTO & QR CODE 🔥 --}}
            <div style="width: 280px; flex-shrink: 0; display: flex; flex-direction: column; gap: 1.5rem;">

                {{-- Foto Aset --}}
                <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center;">
                    <img src="{{ $asset->image_url }}" alt="{{ $asset->batch->name ?? 'Aset' }}"
                        style="width: 100%; height: 220px; object-fit: contain; border-radius: 8px;">
                </div>

                {{-- QR Code --}}
                <div
                    style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <h5 style="margin-top: 0; margin-bottom: 1rem; font-size: 0.9rem; color: #64748b;">QR CODE ASET</h5>
                    <div id="qrCodeContainer" style="display: flex; justify-content: center;"></div>
                    <p style="margin-top: 1rem; font-family: monospace; font-size: 0.8rem; color: #94a3b8;">
                        {{ $asset->qr_code }}</p>
                </div>

            </div>

            {{-- 🔥 KOLOM KANAN: INFORMASI 🔥 --}}
            <div style="flex: 1; min-width: 300px;">

                {{-- Header & Tombol Aksi --}}
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h2 style="margin: 0 0 0.5rem; font-size: 1.75rem; color: var(--text-primary);">
                            {{ $asset->batch->name ?? '-' }}</h2>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <span
                                class="status-badge {{ $asset->status === 'Tersedia' ? 'available' : 'borrowed' }}">{{ $asset->status }}</span>
                            <span
                                class="status-badge {{ $asset->condition === 'Baik' ? 'available' : 'maintenance' }}">Kondisi:
                                {{ $asset->condition }}</span>
                        </div>
                    </div>

                    <div class="btn-group" style="margin: 0; flex-wrap: nowrap;">
                        @can('update', $asset)
                            <a href="{{ route('assets-inv.edit', $asset->id) }}" class="btn btn-primary">✏️ Edit Unit</a>
                        @endcan
                        <button class="btn btn-secondary" onclick="printQrCode()">🖨️ Cetak QR</button>
                        @can('delete', $asset)
                            <form action="{{ route('assets-inv.destroy', $asset) }}" method="POST" style="display: inline;"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">🗑️ Hapus</button>
                            </form>
                        @endcan
                    </div>
                </div>

                {{-- Blok Informasi Umum --}}
                <div
                    style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <h4
                        style="margin-top: 0; margin-bottom: 1.25rem; font-size: 1rem; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem;">
                        📋 Informasi Umum</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.9rem;">
                        <p><span style="color: #64748b;">Label Aset:</span><br><strong>{{ $asset->asset_id }}</strong></p>
                        <p><span style="color: #64748b;">Jenis
                                Kategori:</span><br><strong>{{ $asset->batch->assetType->name ?? '-' }}</strong></p>

                        {{-- Sembunyikan Merek & S/N jika kosong (seperti Buku) --}}
                        @if (!$hideGeneral)
                            <p><span
                                    style="color: #64748b;">Merek:</span><br><strong>{{ $asset->batch->brand ?? '-' }}</strong>
                            </p>
                            <p><span style="color: #64748b;">Nomor Seri
                                    (S/N):</span><br><strong>{{ $asset->serial_number ?? '-' }}</strong></p>
                        @endif

                        <p><span style="color: #64748b;">Lokasi:</span><br>
                            <strong>
                                @if ($asset->location_id)
                                    {{ $asset->location_ref->name ?? '' }}
                                    {{ $asset->location_detail ? '- ' . $asset->location_detail : '' }}
                                @else
                                    {{ $asset->location ?? '-' }}
                                @endif
                            </strong>
                        </p>
                        <p><span style="color: #64748b;">Tanggal
                                Pembelian:</span><br><strong>{{ $asset->batch->purchase_date ? $asset->batch->purchase_date->format('d F Y') : '-' }}</strong>
                        </p>
                        <p><span style="color: #64748b;">Harga Satuan:</span><br><strong
                                style="color: var(--primary-color);">Rp
                                {{ number_format($asset->batch->price ?? 0, 0, ',', '.') }}</strong></p>
                    </div>
                </div>

                {{-- 🔥 Blok Spesifikasi Dinamis (JSON) 🔥 --}}
                @if (count($batchData) > 0 || count($unitData) > 0)
                    <div
                        style="background: #f0f9ff; border: 1px solid #bae6fd; border-left: 4px solid #0ea5e9; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="margin-top: 0; margin-bottom: 1.25rem; font-size: 1rem; color: #0369a1;">⚙️ Spesifikasi
                            Khusus Kategori ({{ $asset->batch->assetType->name }})</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.9rem;">

                            {{-- Tampilkan Data dari Induk (Batch) --}}
                            @foreach ($batchData as $key => $value)
                                @if (isset($schema[$key]) && !empty($value))
                                    <p><span style="color: #0369a1;">{{ $schema[$key]['label'] ?? $key }}:</span><br>
                                        <strong>{{ $value }}</strong>
                                    </p>
                                @endif
                            @endforeach

                            {{-- Tampilkan Data dari Unit --}}
                            @foreach ($unitData as $key => $value)
                                @if (isset($schema[$key]) && !empty($value))
                                    <p><span style="color: #0369a1;">{{ $schema[$key]['label'] ?? $key }}:</span><br>
                                        <strong>{{ $value }}</strong>
                                    </p>
                                @endif
                            @endforeach

                        </div>
                    </div>
                @endif

                {{-- Blok Akuntansi --}}
                @if ($asset->batch->funding_source || $asset->batch->economic_life_years || $asset->batch->residual_value)
                    <div
                        style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <h4
                            style="margin-top: 0; margin-bottom: 1.25rem; font-size: 1rem; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem;">
                            💰 Data Akuntansi</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; font-size: 0.9rem;">
                            @if ($asset->batch->funding_source)
                                <p><span style="color: #64748b;">Sumber
                                        Dana:</span><br><strong>{{ $asset->batch->funding_source }}</strong></p>
                            @endif
                            @if ($asset->batch->economic_life_years)
                                <p><span style="color: #64748b;">Umur
                                        Ekonomis:</span><br><strong>{{ $asset->batch->economic_life_years }} tahun</strong>
                                </p>
                            @endif
                            @if (!is_null($asset->batch->residual_value))
                                <p><span style="color: #64748b;">Nilai Residu:</span><br><strong>Rp
                                        {{ number_format($asset->batch->residual_value, 0, ',', '.') }}</strong></p>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                name: '{{ $asset->batch->name ?? 'Aset' }}',
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
                        new QRCode(document.getElementById("qrcode"), { text: "${qrUrl}", width: 128, height: 128, correctLevel: QRCode.CorrectLevel.H });
                        setTimeout(() => { window.focus(); window.print(); window.close(); }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
            printWindow.document.close();
        }
    </script>
@endpush
