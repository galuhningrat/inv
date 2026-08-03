@extends('layouts.app')

@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Detail: {{ $assetRequest->request_id ?? 'N/A' }}</h3>
            <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <div style="padding: 2rem;">
            <div class="detail-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Informasi Pengajuan
                    </h4>
                    <p><strong>ID:</strong> {{ $assetRequest->request_id ?? '-' }}</p>
                    <p><strong>Pengaju:</strong> {{ optional($assetRequest->requester)->name ?? '-' }}</p>
                    <p><strong>Level:</strong> {{ optional($assetRequest->requester)->level ?? '-' }}</p>
                    <p><strong>Tanggal:</strong> {{ $assetRequest->created_at->format('d F Y H:i') }}</p>
                    <p><strong>Status:</strong>
                        <span
                            class="status-badge {{ $assetRequest->status === 'Disetujui' ? 'approved' : ($assetRequest->status === 'Ditolak' ? 'rejected' : 'pending') }}">
                            {{ $assetRequest->status }}
                        </span>
                    </p>
                </div>

                <div>
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Informasi Aset
                    </h4>
                    <p><strong>Nama:</strong> {{ $assetRequest->asset_name }}</p>
                    <p><strong>Jenis:</strong>
                        @if($assetRequest->assetType)
                            {{ $assetRequest->assetType->name }}
                        @else
                            <span style="color: var(--text-secondary);">Tidak ada jenis</span>
                        @endif
                    </p>
                    <p><strong>Jumlah:</strong>
                        <span class="status-badge available">{{ $assetRequest->quantity }} unit</span>
                    </p>

                    @if($assetRequest->estimated_price)
                        <p><strong>Est. Harga/unit:</strong>
                            <span class="price-display">Rp
                                {{ number_format($assetRequest->estimated_price, 0, ',', '.') }}</span>
                        </p>
                        <p><strong>Total Est.:</strong>
                            <span class="price-display">Rp
                                {{ number_format($assetRequest->estimated_price * $assetRequest->quantity, 0, ',', '.') }}</span>
                        </p>
                    @endif

                    <p><strong>Prioritas:</strong>
                        <span
                            class="status-badge {{ $assetRequest->priority === 'Urgent' ? 'maintenance' : ($assetRequest->priority === 'Tinggi' ? 'borrowed' : 'available') }}">
                            {{ $assetRequest->priority }}
                        </span>
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                    Alasan Pengajuan
                </h4>
                <div style="background: var(--light-bg); padding: 1.5rem; border-radius: 8px; line-height: 1.6;">
                    {{ $assetRequest->reason }}
                </div>
            </div>

            @if($assetRequest->status !== 'Pending')
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Informasi Persetujuan
                    </h4>
                    <p><strong>Diproses oleh:</strong> {{ optional($assetRequest->approver)->name ?? '-' }}</p>
                    <p><strong>Tanggal:</strong>
                        {{ $assetRequest->approved_at ? $assetRequest->approved_at->format('d F Y H:i') : '-' }}</p>

                    @if($assetRequest->approval_notes)
                        <div style="margin-top: 1rem;">
                            <p><strong>Catatan:</strong></p>
                            <div style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                                {{ $assetRequest->approval_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="btn-group" style="justify-content: center; margin-top: 2rem;">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Kembali</a>

                @if($assetRequest->status === 'Pending' && in_array(auth()->user()->level, ['Admin', 'Kaprodi', 'Keuangan']))
                    <form action="{{ route('requests.approve', $assetRequest) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Setujui pengajuan?')">
                            ✓ Setujui
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                        ✗ Tolak
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tolak Pengajuan</h3>
                <button class="close-modal" onclick="closeRejectModal()">×</button>
            </div>
            <form action="{{ route('requests.reject', $assetRequest) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approval_notes">Catatan Penolakan</label>
                        <textarea id="approval_notes" name="approval_notes" class="form-control" rows="4"
                            placeholder="Alasan penolakan (opsional)"></textarea>
                    </div>
                </div>
                <div class="btn-group"
                    style="justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('active');
        }
    </script>
@endpush

@push('styles')
    <style>
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endpush