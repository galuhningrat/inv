@extends('layouts.app')

@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Detail: {{ $assetRequest->request_id }}</h3>
            <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <div style="padding: 2rem;">
            <div class="detail-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Informasi Pengajuan</h4>
                    <p><strong>ID:</strong> {{ $assetRequest->request_id }}</p>
                    <p><strong>Pengaju:</strong> {{ optional($assetRequest->requester)->name ?? '-' }}</p>
                    <p><strong>Level:</strong> {{ optional($assetRequest->requester)->level ?? '-' }}</p>
                    <p><strong>Tanggal:</strong> {{ $assetRequest->created_at->format('d F Y H:i') }}</p>
                    <p><strong>Status:</strong>
                        @php
                            $statusClass = match ($assetRequest->status) {
                                'Disetujui', 'Dana Cair', 'Dikonfirmasi', 'Diterima' => 'approved',
                                'Diverifikasi' => 'borrowed',
                                'Ditolak' => 'rejected',
                                default => 'pending',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $assetRequest->status }}</span>
                    </p>
                </div>

                <div>
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Klasifikasi</h4>
                    <p><strong>Jenis Barang:</strong> {{ $assetRequest->jenis_barang }}</p>
                    <p><strong>Kategori Barang:</strong> {{ $assetRequest->kategori_barang }}</p>
                    <p><strong>Alasan Pengajuan:</strong> {{ $assetRequest->alasan_pengajuan }}</p>
                    @if ($assetRequest->relatedAsset)
                        <p><strong>Aset Terkait:</strong> {{ $assetRequest->relatedAsset->asset_id }} —
                            {{ $assetRequest->relatedAsset->name }}</p>
                    @endif
                    <p><strong>Prioritas:</strong>
                        <span
                            class="status-badge {{ $assetRequest->priority === 'Sangat Mendesak' ? 'maintenance' : ($assetRequest->priority === 'Mendesak' ? 'borrowed' : 'available') }}">
                            {{ $assetRequest->priority }}
                        </span>
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                    Rincian Barang yang Diajukan</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Spesifikasi</th>
                            <th>Jenis Aset</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Est. Harga/Unit</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assetRequest->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->specification ?? '-' }}</td>
                                <td>{{ $item->assetType->name ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>{{ $item->estimated_price_per_unit ? 'Rp ' . number_format($item->estimated_price_per_unit, 0, ',', '.') : '-' }}
                                </td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align: right;"><strong>Total Estimasi</strong></td>
                            <td><strong>Rp {{ number_format($assetRequest->total_estimated_price, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                    Alasan / Latar Belakang</h4>
                <div style="background: var(--light-bg); padding: 1.5rem; border-radius: 8px; line-height: 1.6;">
                    {{ $assetRequest->reason }}</div>
            </div>

            @if ($assetRequest->verified_by)
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Verifikasi PJ Pengadaan</h4>
                    <p><strong>Diverifikasi oleh:</strong> {{ optional($assetRequest->verifier)->name ?? '-' }}</p>
                    <p><strong>Tanggal:</strong>
                        {{ $assetRequest->verified_at ? $assetRequest->verified_at->format('d F Y H:i') : '-' }}</p>
                    @if ($assetRequest->verification_notes)
                        <div style="margin-top: 0.5rem; background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                            {{ $assetRequest->verification_notes }}</div>
                    @endif
                </div>
            @endif

            @if ($assetRequest->approved_by)
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Persetujuan Ketua STTI</h4>
                    <p><strong>{{ $assetRequest->status === 'Ditolak' ? 'Ditolak oleh' : 'Disetujui oleh' }}:</strong>
                        {{ optional($assetRequest->approver)->name ?? '-' }}</p>
                    <p><strong>Tanggal:</strong>
                        {{ $assetRequest->approved_at ? $assetRequest->approved_at->format('d F Y H:i') : '-' }}</p>
                    @if ($assetRequest->approval_notes)
                        <div style="margin-top: 0.5rem; background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                            {{ $assetRequest->approval_notes }}</div>
                    @endif
                </div>
            @endif

            @if ($assetRequest->disbursed_by)
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Pencairan Dana (Bagian Keuangan)</h4>
                    <p><strong>Dicairkan oleh:</strong> {{ optional($assetRequest->disburser)->name ?? '-' }}</p>
                    <p><strong>Tanggal:</strong>
                        {{ $assetRequest->disbursed_at ? $assetRequest->disbursed_at->format('d F Y H:i') : '-' }}</p>
                    @if ($assetRequest->disbursement_notes)
                        <div style="margin-top: 0.5rem; background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                            {{ $assetRequest->disbursement_notes }}</div>
                    @endif
                </div>
            @endif

            @if ($assetRequest->confirmed_by)
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Konfirmasi Penerimaan Fisik (PJ Pengadaan)</h4>
                    <p><strong>Dikonfirmasi oleh:</strong> {{ optional($assetRequest->confirmer)->name ?? '-' }}</p>
                    <p><strong>Tanggal:</strong>
                        {{ $assetRequest->confirmed_at ? $assetRequest->confirmed_at->format('d F Y H:i') : '-' }}</p>
                    @if ($assetRequest->confirmation_notes)
                        <div style="margin-top: 0.5rem; background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                            {{ $assetRequest->confirmation_notes }}</div>
                    @endif
                </div>
            @endif

            <div class="btn-group" style="justify-content: center; margin-top: 2rem;">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Kembali</a>

                @can('verify', $assetRequest)
                    <form action="{{ route('requests.verify', $assetRequest) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Verifikasi pengajuan ini?')">✓
                            Verifikasi</button>
                    </form>
                @endcan

                @can('approve', $assetRequest)
                    <form action="{{ route('requests.approve', $assetRequest) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Setujui pengajuan?')">✓
                            Setujui</button>
                    </form>
                @endcan

                @can('reject', $assetRequest)
                    <button type="button" class="btn btn-danger" onclick="showRejectModal()">✗ Tolak</button>
                @endcan

                @can('disburse', $assetRequest)
                    <form action="{{ route('requests.disburse', $assetRequest) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success"
                            onclick="return confirm('Konfirmasi dana sudah dicairkan?')">✓ Konfirmasi Dana Cair</button>
                    </form>
                @endcan

                @can('confirmPhysical', $assetRequest)
                    <form action="{{ route('requests.confirm', $assetRequest) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success"
                            onclick="return confirm('Konfirmasi barang sudah diterima secara fisik?')">✓ Konfirmasi
                            Fisik</button>
                    </form>
                @endcan

                @can('receive', $assetRequest)
                    <a href="{{ route('requests.receive.form', $assetRequest) }}" class="btn btn-success">Registrasi Aset</a>
                @endcan
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
