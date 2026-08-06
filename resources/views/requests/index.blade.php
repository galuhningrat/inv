@extends('layouts.app')

@section('title', 'Pengajuan Aset')
@section('page-title', 'Pengajuan Aset')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Pengajuan Aset</h3>
            <a href="{{ route('requests.create') }}" class="btn btn-primary">+ Ajukan Aset</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Pengajuan</th>
                        <th>Pengaju</th>
                        <th>Rincian Barang</th>
                        <th>Total Unit</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td><strong>{{ $request->request_id }}</strong></td>
                            <td>{{ $request->requester->name }}</td>
                            <td>
                                @foreach ($request->items as $item)
                                    {{ $item->item_name }} ({{ $item->quantity }} {{ $item->unit }})@if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td>{{ $request->total_quantity }}</td>
                            <td>
                                <span
                                    class="status-badge {{ $request->priority === 'Sangat Mendesak' ? 'maintenance' : ($request->priority === 'Mendesak' ? 'borrowed' : 'available') }}">
                                    {{ $request->priority }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($request->status) {
                                        'Disetujui', 'Dana Cair', 'Dikonfirmasi', 'Diterima' => 'available',
                                        'Diverifikasi' => 'borrowed',
                                        'Ditolak' => 'maintenance',
                                        default => 'pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $request->status }}</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if ($request->status === 'Pending' && in_array(auth()->user()->level, ['PJ Pengadaan', 'Admin']))
                                        <form action="{{ route('requests.verify', $request) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Verifikasi pengajuan ini?')">Verifikasi</button>
                                        </form>
                                    @endif

                                    @if ($request->status === 'Diverifikasi' && auth()->user()->level === 'Rektor')
                                        <form action="{{ route('requests.approve', $request) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Setujui pengajuan ini?')">Setujui</button>
                                        </form>
                                        <button type="button" class="btn btn-danger"
                                            onclick="showRejectModal({{ $request->id }})">Tolak</button>
                                    @endif

                                    @if ($request->status === 'Disetujui' && auth()->user()->level === 'Keuangan')
                                        <form action="{{ route('requests.disburse', $request) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Konfirmasi dana sudah dicairkan?')">Konfirmasi Dana
                                                Cair</button>
                                        </form>
                                    @endif

                                    @if ($request->status === 'Dana Cair' && auth()->user()->level === 'PJ Pengadaan')
                                        <form action="{{ route('requests.confirm', $request) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Konfirmasi barang sudah diterima secara fisik?')">Konfirmasi
                                                Fisik</button>
                                        </form>
                                    @endif

                                    @if ($request->status === 'Dikonfirmasi' && in_array(auth()->user()->level, ['Sarpras', 'Admin']))
                                        <a href="{{ route('requests.receive.form', $request) }}"
                                            class="btn btn-success">Registrasi Aset</a>
                                    @endif

                                    <a href="{{ route('requests.show', $request) }}" class="btn btn-secondary">Detail</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">Tidak ada data pengajuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">
            {{ $requests->links() }}
        </div>
    </div>

    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tolak Pengajuan</h3>
                <button class="btn-close" onclick="closeRejectModal()">×</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approval_notes">Catatan Penolakan</label>
                        <textarea id="approval_notes" name="approval_notes" class="form-control" rows="3"
                            placeholder="Berikan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showRejectModal(requestId) {
            document.getElementById('rejectForm').action = `/requests/${requestId}/reject`;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }
    </script>
@endpush

@push('styles')
    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: var(--card-background);
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: var(--shadow);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }
    </style>
@endpush
