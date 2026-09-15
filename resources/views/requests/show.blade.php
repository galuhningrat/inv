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
                        <span class="status-badge {{ $statusClass }}">{{ $assetRequest->status_label }}</span>
                    </p>
                </div>

                <div>
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        Klasifikasi</h4>
                    {{-- Jenis Barang (header) tidak lagi diminta untuk pengajuan baru — klasifikasi
                         sekarang per item lewat "Sifat Barang" (lihat kolom Jenis/Kategori di tabel
                         rincian di bawah). Baris ini tetap tampil untuk pengajuan historis yang masih
                         punya nilainya, dan otomatis hilang untuk pengajuan baru. --}}
                    @if ($assetRequest->jenis_barang)
                        <p><strong>Jenis Barang:</strong> {{ $assetRequest->jenis_barang }}</p>
                    @endif
                    {{-- Kategori Barang tidak lagi diminta untuk pengajuan baru (lihat create.blade.php)
                         — kolom & data lama tetap tersimpan, jadi baris ini tetap tampil untuk pengajuan
                         historis yang masih punya nilainya, dan otomatis hilang untuk pengajuan baru. --}}
                    @if ($assetRequest->kategori_barang)
                        <p><strong>Kategori Barang:</strong> {{ $assetRequest->kategori_barang }}</p>
                    @endif
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

            @php
                $rolledOverItems = $assetRequest->items->whereNotNull('rolled_from_item_id');
            @endphp
            @if ($rolledOverItems->isNotEmpty())
                <div
                    style="margin-bottom: 2rem; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1.5rem;">
                    <h4 style="margin: 0 0 1rem; color: #92400e;">📋 Riwayat Penangguhan oleh Ketua</h4>
                    @foreach ($rolledOverItems as $item)
                        @php $original = $item->rolledFrom; @endphp
                        @if ($original)
                            <div
                                style="{{ !$loop->last ? 'margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #f59e0b;' : '' }}">
                                <p style="margin: 0; color: #92400e;"><strong>Barang:</strong> {{ $item->item_name }}</p>
                                <p style="margin: 0; color: #92400e;"><strong>Ditangguhkan oleh:</strong>
                                    {{ optional($original->approver)->name ?? '-' }}</p>
                                <p style="margin: 0; color: #92400e;"><strong>Tanggal:</strong>
                                    {{ $original->approved_at ? $original->approved_at->format('d F Y H:i') : '-' }}
                                </p>
                                <p style="margin: 0.5rem 0 0; color: #92400e;"><strong>Alasan:</strong>
                                    {{ $original->approval_notes ?? '-' }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                    Rincian Barang yang Diajukan</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Spesifikasi</th>
                            <th>Jenis / Kategori</th>
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
                                <td>
                                    {{ $item->item_name }}
                                    @if ($item->rolled_from_item_id)
                                        <br><small style="color: #f59e0b;">🔄 Rollover dari item sebelumnya</small>
                                    @endif
                                </td>
                                <td>{{ $item->specification ?? '-' }}</td>
                                <td>
                                    {{-- Fisik + Tidak Habis Pakai pakai Jenis Aset (assetType). Fisik + Habis
                                         Pakai dan Non-Fisik pakai Kategori yang dipilih pemohon saat mengajukan.
                                         Sebelumnya kolom ini selalu "-" untuk Non-Fisik karena assetType memang
                                         tidak pernah diisi untuk item jenis itu. --}}
                                    @if ($item->item_type === 'Fisik' && $item->sifat_barang === 'Habis Pakai')
                                        {{ \App\Models\AssetRequestItem::HABIS_PAKAI_CATEGORIES[$item->category] ?? ($item->category ?? '-') }}
                                    @elseif ($item->item_type === 'Fisik')
                                        {{ $item->assetType->name ?? '-' }}
                                    @else
                                        {{ \App\Models\IntangibleAsset::CATEGORIES[$item->category] ?? ($item->category ?? '-') }}
                                    @endif
                                    {{-- Sifat Barang cuma ditampilkan kalau bukan default "Tidak Habis Pakai" —
                                         supaya tidak menambah noise untuk mayoritas item yang lewat alur biasa. --}}
                                    @if ($item->sifat_barang && $item->sifat_barang !== 'Tidak Habis Pakai')
                                        <br><small style="color: var(--text-secondary);">({{ $item->sifat_barang }})</small>
                                    @endif
                                    @if (!is_null($item->received_quantity))
                                        <br><small style="color: var(--success-color);">
                                            ✅ Diterima: {{ $item->received_quantity }} {{ $item->unit }}
                                            @if ($item->receipt_notes)
                                                — {{ $item->receipt_notes }}
                                            @endif
                                        </small>
                                    @endif
                                </td>
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
