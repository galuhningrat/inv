@extends('layouts.app')

@section('title', 'Detail Peminjaman')
@section('page-title', 'Detail Peminjaman')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Detail Peminjaman: {{ $borrowing->borrowing_id }}</h3>
            <a href="{{ route('borrowings.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <div style="padding: 2rem;">
            <div class="detail-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h4 style="margin-bottom: 1rem;">Informasi Peminjaman</h4>
                    <p><strong>ID Peminjaman:</strong> {{ $borrowing->borrowing_id }}</p>
                    <p><strong>Peminjam:</strong> {{ $borrowing->borrower_name }}</p>
                    <p><strong>Jabatan:</strong> {{ $borrowing->borrower_role }}</p>
                    <p><strong>Tanggal Pinjam:</strong> {{ $borrowing->borrow_date->format('d F Y') }}</p>
                    <p><strong>Tanggal Kembali:</strong> {{ $borrowing->return_date->format('d F Y') }}</p>
                    <p><strong>Status:</strong>
                        @php
                            $statusClass = match ($borrowing->status) {
                                'Aktif' => 'borrowed',
                                'Selesai' => 'available',
                                'Menunggu Persetujuan Kalab' => 'pending',
                                'Ditolak' => 'maintenance',
                                default => 'maintenance',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $borrowing->status }}</span>
                    </p>
                </div>

                <div>
                    <h4 style="margin-bottom: 1rem;">Informasi Aset</h4>
                    <p><strong>Nama Aset:</strong> {{ $borrowing->asset->name }}</p>
                    <p><strong>ID Aset:</strong> {{ $borrowing->asset->asset_id }}</p>
                    <p><strong>Jenis:</strong> {{ $borrowing->asset->assetType->name }}</p>
                    <p><strong>Lokasi:</strong> {{ $borrowing->asset->location }}</p>
                    <p><strong>Unit Pemilik:</strong> {{ $borrowing->asset->unit->name ?? '-' }}</p>
                </div>
            </div>

            <div class="purpose-section">
                <h4 style="margin-bottom: 1rem;">Tujuan Peminjaman</h4>
                <div style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                    {{ $borrowing->purpose }}
                </div>
            </div>

            @if ($borrowing->kalab_approved_by)
                <div style="margin-top: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">
                        {{ $borrowing->status === 'Ditolak' ? 'Penolakan Kalab (Lintas-Unit)' : 'Persetujuan Kalab (Lintas-Unit)' }}
                    </h4>
                    <div style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                        <p><strong>{{ $borrowing->status === 'Ditolak' ? 'Ditolak oleh' : 'Disetujui oleh' }}:</strong>
                            {{ optional($borrowing->kalabApprover)->name ?? '-' }}</p>
                        <p><strong>Tanggal:</strong>
                            {{ $borrowing->kalab_approved_at ? $borrowing->kalab_approved_at->format('d F Y H:i') : '-' }}
                        </p>
                        @if ($borrowing->kalab_rejection_notes)
                            <p style="margin-top: 0.5rem;"><strong>Alasan Penolakan:</strong>
                                {{ $borrowing->kalab_rejection_notes }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($borrowing->actual_return_date)
                <div style="margin-top: 1.5rem;">
                    <p><strong>Tanggal Pengembalian Aktual:</strong> {{ $borrowing->actual_return_date->format('d F Y') }}
                    </p>
                </div>
            @endif

            <div class="btn-group" style="margin-top: 2rem; justify-content: center;">
                @can('approveCrossUnit', $borrowing)
                    <form action="{{ route('borrowings.approve-cross-unit', $borrowing) }}" method="POST"
                        style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success"
                            onclick="return confirm('Setujui peminjaman lintas-unit ini?')">
                            ✔ Setujui Peminjaman
                        </button>
                    </form>
                @endcan

                @can('rejectCrossUnit', $borrowing)
                    <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                        ✗ Tolak Peminjaman
                    </button>
                @endcan

                @if ($borrowing->status === 'Aktif')
                    @can('update', $borrowing)
                        <form action="{{ route('borrowings.return', $borrowing) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success"
                                onclick="return confirm('Konfirmasi pengembalian aset?')">
                                ✔ Kembalikan Aset
                            </button>
                        </form>
                    @endcan
                @endif

                <a href="{{ route('borrowings.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
            </div>
        </div>
    </div>

    @can('rejectCrossUnit', $borrowing)
        <div id="rejectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Tolak Peminjaman Lintas-Unit</h3>
                    <button class="close-modal" onclick="closeRejectModal()">×</button>
                </div>
                <form action="{{ route('borrowings.reject-cross-unit', $borrowing) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="kalab_rejection_notes">Alasan Penolakan</label>
                            <textarea id="kalab_rejection_notes" name="kalab_rejection_notes" class="form-control" rows="4"
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
    @endcan

    <style>
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
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
