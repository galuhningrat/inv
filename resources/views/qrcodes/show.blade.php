@extends('layouts.app')

@section('title', 'Detail QR Code')
@section('page-title', 'Detail QR Code')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Detail QR Code: {{ $qrCode->qr_code_id }}</h3>
            <a href="{{ route('qrcodes.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- QR Code Display -->
                <div style="text-align: center;">
                    <h4 style="margin-bottom: 1.5rem; color: var(--text-primary);">QR Code</h4>
                    <div id="qrCodeContainer"
                        style="display: inline-block; padding: 20px; background: white; border-radius: 12px; box-shadow: var(--shadow-lg);">
                    </div>
                    <p
                        style="margin-top: 1rem; font-family: monospace; font-size: 0.875rem; color: var(--text-secondary); word-break: break-all;">
                        {{ $qrCode->code_content }}
                    </p>
                    <div style="margin-top: 1.5rem;">
                        <span class="status-badge {{ $qrCode->status === 'Aktif' ? 'available' : 'maintenance' }}"
                            style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                            {{ $qrCode->status }}
                        </span>
                    </div>
                </div>

                <!-- QR Code Info -->
                <div>
                    <h4
                        style="margin-bottom: 1rem; color: var(--text-primary); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Informasi QR Code
                    </h4>
                    <div style="display: grid; gap: 1rem;">
                        <div class="info-item" style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                            <div
                                style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                ID QR Code</div>
                            <div style="font-weight: 600; margin-top: 0.25rem;">{{ $qrCode->qr_code_id }}</div>
                        </div>
                        <div class="info-item" style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                            <div
                                style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                Kode Konten</div>
                            <div
                                style="font-weight: 600; margin-top: 0.25rem; font-family: monospace; word-break: break-all;">
                                {{ $qrCode->code_content }}</div>
                        </div>
                        <div class="info-item" style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                            <div
                                style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                Status</div>
                            <div style="margin-top: 0.25rem;">
                                <span class="status-badge {{ $qrCode->status === 'Aktif' ? 'available' : 'maintenance' }}">
                                    {{ $qrCode->status }}
                                </span>
                            </div>
                        </div>
                        <div class="info-item" style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                            <div
                                style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                Dibuat Pada</div>
                            <div style="font-weight: 600; margin-top: 0.25rem;">
                                {{ $qrCode->created_at->format('d F Y H:i') }}</div>
                        </div>
                    </div>

                    @if($qrCode->asset)
                        <h4
                            style="margin: 2rem 0 1rem; color: var(--text-primary); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                            Informasi Aset Terkait
                        </h4>
                        <div style="display: grid; gap: 1rem;">
                            <div style="text-align: center; margin-bottom: 1rem;">
                                <img src="{{ $qrCode->asset->image ? Storage::url($qrCode->asset->image) : asset('assets/default-asset.png') }}"
                                    alt="{{ $qrCode->asset->name }}"
                                    style="width: 120px; height: 120px; object-fit: cover; border-radius: 12px; box-shadow: var(--shadow);">
                            </div>
                            <div class="info-item" style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                                <div
                                    style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                    Nama Aset</div>
                                <div style="font-weight: 600; margin-top: 0.25rem;">{{ $qrCode->asset->name }}</div>
                            </div>
                            <div class="info-item" style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                                <div
                                    style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                    ID Aset</div>
                                <div style="font-weight: 600; margin-top: 0.25rem;">{{ $qrCode->asset->asset_id }}</div>
                            </div>
                            <div class="info-item" style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                                <div
                                    style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                    Lokasi</div>
                                <div style="font-weight: 600; margin-top: 0.25rem;">{{ $qrCode->asset->location }}</div>
                            </div>
                            <div class="info-item" style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                                <div
                                    style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                    Status Aset</div>
                                <div style="margin-top: 0.25rem;">
                                    <span
                                        class="status-badge {{ $qrCode->asset->status === 'Tersedia' ? 'available' : ($qrCode->asset->status === 'Dipinjam' ? 'borrowed' : 'maintenance') }}">
                                        {{ $qrCode->asset->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="btn-group" style="justify-content: center; margin-top: 2rem;">
                <a href="{{ route('qrcodes.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
                <form action="{{ route('qrcodes.toggle-status', $qrCode) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn {{ $qrCode->status === 'Aktif' ? 'btn-warning' : 'btn-success' }}">
                        {{ $qrCode->status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <a href="{{ route('qrcodes.print', $qrCode) }}" class="btn btn-primary" target="_blank">Cetak QR Code</a>
                <form action="{{ route('qrcodes.destroy', $qrCode) }}" method="POST" style="display: inline;"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus QR Code ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qrContainer = document.getElementById('qrCodeContainer');
            const qrUrl = '{{ route('asset.detail', ['qrcode' => $qrCode->code_content]) }}';

            new QRCode(qrContainer, {
                text: qrUrl,
                width: 200,
                height: 200,
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
@endpush