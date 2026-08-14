@extends('layouts.app')

@section('title', 'Peminjaman')
@section('page-title', 'Manajemen Peminjaman')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Manajemen Peminjaman</h3>
            <a href="{{ route('borrowings.create') }}" class="btn btn-primary">+ Pinjam Aset</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Peminjaman</th>
                        <th>Peminjam</th>
                        <th>Aset</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $borrowing)
                        <tr>
                            <td><strong>{{ $borrowing->borrowing_id }}</strong></td>
                            <td>{{ $borrowing->borrower_name }} <small>({{ $borrowing->borrower_role }})</small></td>
                            <td>{{ $borrowing->asset->name }}</td>
                            <td>{{ $borrowing->borrow_date->format('d M Y') }}</td>
                            <td>{{ $borrowing->actual_return_date ? $borrowing->actual_return_date->format('d M Y') : $borrowing->return_date->format('d M Y') }}
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($borrowing->status) {
                                        'Aktif' => 'borrowed',
                                        'Selesai' => 'available',
                                        'Menunggu Persetujuan Kalab' => 'pending',
                                        'Ditolak' => 'maintenance',
                                        default => 'maintenance',
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $borrowing->status }}
                                </span>
                            </td>
                            <td>
                                @can('approveCrossUnit', $borrowing)
                                    <form action="{{ route('borrowings.approve-cross-unit', $borrowing) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('Setujui peminjaman lintas-unit ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Setujui</button>
                                    </form>
                                @endcan

                                @can('rejectCrossUnit', $borrowing)
                                    <button type="button" class="btn btn-danger"
                                        onclick="showRejectModal({{ $borrowing->id }})">Tolak</button>
                                @endcan

                                @if ($borrowing->status === 'Aktif')
                                    @can('update', $borrowing)
                                        <form action="{{ route('borrowings.return', $borrowing) }}" method="POST"
                                            style="display: inline;" onsubmit="return confirm('Konfirmasi pengembalian aset?')">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Kembalikan</button>
                                        </form>
                                    @endcan
                                @else
                                    @if (!in_array($borrowing->status, ['Menunggu Persetujuan Kalab', 'Ditolak']))
                                        <span
                                            style="color: var(--text-secondary); font-size: 0.75rem;">{{ $borrowing->status }}</span>
                                    @endif
                                @endif

                                <a href="{{ route('borrowings.show', $borrowing) }}" class="btn btn-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">Tidak ada data peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">
            {{ $borrowings->links() }}
        </div>
    </div>

    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tolak Peminjaman Lintas-Unit</h3>
                <button class="btn-close" onclick="document.getElementById('rejectModal').style.display='none'">×</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kalab_rejection_notes">Alasan Penolakan</label>
                        <textarea id="kalab_rejection_notes" name="kalab_rejection_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('rejectModal').style.display='none'">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showRejectModal(borrowingId) {
            document.getElementById('rejectForm').action = `/borrowings/${borrowingId}/reject-cross-unit`;
            document.getElementById('rejectModal').style.display = 'flex';
        }
    </script>
@endpush
