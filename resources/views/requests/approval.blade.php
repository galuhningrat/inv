@extends('layouts.app')

@section('title', 'Approval Pengajuan')
@section('page-title', 'Approval Pengajuan: ' . $assetRequest->request_id)

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Approval Pengajuan: {{ $assetRequest->request_id }}</h3>
            <div>
                <span class="status-badge borrowed">Menunggu Persetujuan</span>
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
            </div>
        </div>

        <div style="padding: 2rem;">
            {{-- Informasi Pengajuan --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <p><strong>Pengaju:</strong> {{ $assetRequest->requester->name }}</p>
                    <p><strong>Unit:</strong> {{ $assetRequest->unit->name ?? '-' }}</p>
                    <p><strong>Periode:</strong>
                        {{ $assetRequest->period_month ?? now()->month }}/{{ $assetRequest->period_year ?? now()->year }}
                    </p>
                </div>
                <div>
                    <p><strong>Total Estimasi:</strong> <span class="price-display">Rp
                            {{ number_format($assetRequest->total_estimated_price, 0, ',', '.') }}</span></p>
                    <p><strong>Total Disetujui:</strong> <span class="price-display" style="color: var(--success-color);">Rp
                            {{ number_format($assetRequest->approved_total, 0, ',', '.') }}</span></p>
                </div>
            </div>

            {{-- Ringkasan Status --}}
            <div style="display: flex; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
                @php $summary = $assetRequest->approval_summary; @endphp
                <span class="status-badge pending">⏳ Menunggu: {{ $summary['pending'] }}</span>
                <span class="status-badge available">✅ Disetujui: {{ $summary['approved'] }}</span>
                <span class="status-badge maintenance">❌ Ditolak: {{ $summary['rejected'] }}</span>
                <span class="status-badge borrowed">⏳ Ditangguhkan: {{ $summary['deferred'] }}</span>
            </div>

            {{-- Tabel Item --}}
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Est. Harga</th>
                            <th>Subtotal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assetRequest->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $item->item_name }}
                                    @if ($item->specification)
                                        <br><small style="color: var(--text-secondary);">{{ $item->specification }}</small>
                                    @endif
                                    @if ($item->rolled_from_item_id)
                                        <br><small style="color: #f59e0b;">🔄 Rollover dari item sebelumnya</small>
                                    @endif
                                </td>
                                <td><span
                                        class="status-badge {{ $item->item_type === 'Fisik' ? 'available' : 'borrowed' }}">{{ $item->item_type }}</span>
                                </td>
                                <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                <td>Rp {{ number_format($item->estimated_price_per_unit ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status-badge {{ $item->approval_badge_class }}">
                                        {{ $item->approval_status_label }}
                                    </span>
                                    @if ($item->approval_notes)
                                        <br><small
                                            style="color: var(--text-secondary);">{{ $item->approval_notes }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->approval_status === 'pending')
                                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                            <form action="{{ route('requests.approve-item', [$assetRequest, $item]) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="action" value="approved">
                                                <button type="submit" class="btn btn-success btn-sm"
                                                    title="Setujui">✅</button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="showActionModal({{ $item->id }}, 'rejected')"
                                                title="Tolak">❌</button>
                                            <button type="button" class="btn btn-warning btn-sm"
                                                onclick="showActionModal({{ $item->id }}, 'deferred')"
                                                title="Tangguhkan">⏳</button>
                                        </div>
                                    @else
                                        <span style="color: var(--text-secondary); font-size: 0.8rem;">Sudah diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align: right;"><strong>Total Estimasi</strong></td>
                            <td><strong>Rp {{ number_format($assetRequest->total_estimated_price, 0, ',', '.') }}</strong>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align: right; color: var(--success-color);"><strong>Total
                                    Disetujui</strong></td>
                            <td style="color: var(--success-color);"><strong>Rp
                                    {{ number_format($assetRequest->approved_total, 0, ',', '.') }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
            </div>
        </div>
    </div>

    {{-- Modal untuk Action dengan Alasan --}}
    <div id="actionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="actionModalTitle">Konfirmasi Tindakan</h3>
                <button class="close-modal" onclick="closeActionModal()">×</button>
            </div>
            <form id="actionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" id="actionType">
                    <div class="form-group">
                        <label for="approval_notes_modal">Alasan <span style="color: red;">*</span></label>
                        <textarea id="approval_notes_modal" name="approval_notes" class="form-control" rows="3"
                            placeholder="Masukkan alasan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeActionModal()">Batal</button>
                    <button type="submit" class="btn btn-danger" id="actionSubmitBtn">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            min-width: 36px;
        }

        .btn-warning {
            background: #f59e0b;
            color: #fff;
        }

        .btn-warning:hover {
            background: #d97706;
        }

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

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }
    </style>
@endsection

@push('scripts')
    <script>
        let currentItemId = null;
        let currentAction = null;

        function showActionModal(itemId, action) {
            currentItemId = itemId;
            currentAction = action;

            const modal = document.getElementById('actionModal');
            const title = document.getElementById('actionModalTitle');
            const submitBtn = document.getElementById('actionSubmitBtn');
            const form = document.getElementById('actionForm');
            const actionInput = document.getElementById('actionType');

            const labels = {
                'rejected': {
                    title: 'Tolak Item',
                    btn: 'Tolak',
                    color: 'btn-danger'
                },
                'deferred': {
                    title: 'Tangguhkan Item',
                    btn: 'Tangguhkan',
                    color: 'btn-warning'
                }
            };

            const label = labels[action] || labels['rejected'];
            title.textContent = label.title;
            submitBtn.textContent = label.btn;
            submitBtn.className = 'btn ' + label.color;
            actionInput.value = action;

            // Set form action
            form.action = `/requests/${ {{ $assetRequest->id }} }/items/${itemId}/approve`;

            modal.style.display = 'flex';
            document.getElementById('approval_notes_modal').value = '';
        }

        function closeActionModal() {
            document.getElementById('actionModal').style.display = 'none';
        }
    </script>
@endpush
