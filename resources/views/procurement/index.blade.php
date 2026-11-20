@extends('layouts.app')

@section('title', 'Perbandingan Penawaran')

@section('content')
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Perbandingan Penawaran Vendor</h3>
        <div style="display: flex; gap: 0.5rem;">
            @if(in_array(Auth::user()->level, ['PJ Pengadaan', 'Admin']) && in_array($assetRequest->status, ['Disetujui', 'Dana Cair']))
            <a href="{{ route('procurement.create', $assetRequest) }}" class="btn btn-primary">+ Tambah Penawaran</a>
            @endif
            <a href="{{ route('requests.show', $assetRequest) }}" class="btn btn-secondary">← Kembali ke Pengajuan</a>
        </div>
    </div>

    <div style="padding: 2rem;">
        <div style="background: #eff6ff; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <p style="margin: 0;"><strong>Pengajuan ID:</strong> {{ $assetRequest->request_id }}</p>
                    <p style="margin: 0;"><strong>Pengaju:</strong> {{ $assetRequest->requester->name }}</p>
                </div>
                <div>
                    <p style="margin: 0;"><strong>Total Estimasi:</strong> Rp {{ number_format($assetRequest->total_estimated_price, 0, ',', '.') }}</p>
                    <p style="margin: 0;"><strong>Total Vendor:</strong> {{ $offers->count() }} Penawaran Masuk</p>
                </div>
            </div>
        </div>

        @if($offers->isEmpty())
        <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
            <p style="font-size: 2rem;">📭</p>
            <p>Belum ada penawaran vendor untuk pengajuan ini.</p>
        </div>
        @else
        <div class="table-wrapper">
            <table class="data-table" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th style="width: 200px;">Komponen</th>
                        @foreach($offers as $offer)
                        <th style="text-align: center; background: {{ $offer->is_selected ? '#d1fae5' : 'transparent' }};">
                            <strong>{{ $offer->vendor_name }}</strong><br>
                            <small style="font-weight: normal; color: var(--text-secondary);">
                                {{ $offer->offer_number ?: 'Tanpa No. Surat' }}<br>
                                Tgl: {{ $offer->offer_date->format('d/m/Y') }}
                            </small>
                            @if($offer->is_selected)
                            <br><span class="status-badge available" style="margin-top: 0.5rem;">✅ TERPILIH</span>
                            @endif
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- Per Item --}}
                    @foreach($assetRequest->items as $reqItem)
                    <tr>
                        <td>
                            {{ $reqItem->item_name }}<br>
                            <small style="color: var(--text-secondary);">Qty: {{ $reqItem->quantity }} {{ $reqItem->unit }}</small>
                        </td>
                        @foreach($offers as $offer)
                        @php
                        $offerItem = $offer->items->where('asset_request_item_id', $reqItem->id)->first();
                        @endphp
                        <td style="text-align: right;">
                            @if($offerItem)
                            <strong>Rp {{ number_format($offerItem->unit_price, 0, ',', '.') }}</strong> / unit<br>
                            <small style="color: var(--text-secondary);">
                                Subtotal: Rp {{ number_format($offerItem->subtotal, 0, ',', '.') }}
                            </small>
                            @else
                            -
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach

                    {{-- Ringkasan Biaya --}}
                    <tr style="background: #f8fafc;">
                        <td><strong>Pajak Keseluruhan</strong></td>
                        @foreach($offers as $offer)
                        <td style="text-align: right;">Rp {{ number_format($offer->tax_amount, 0, ',', '.') }}</td>
                        @endforeach
                    </tr>
                    <tr style="background: #f8fafc;">
                        <td><strong>Ongkos Kirim</strong></td>
                        @foreach($offers as $offer)
                        <td style="text-align: right;">Rp {{ number_format($offer->shipping_cost, 0, ',', '.') }}</td>
                        @endforeach
                    </tr>
                    <tr style="background: #e2e8f0;">
                        <td><strong>TOTAL PENAWARAN</strong></td>
                        @foreach($offers as $offer)
                        <td style="text-align: right; font-size: 1.1rem; color: var(--primary-color);">
                            <strong>Rp {{ number_format($offer->total_price, 0, ',', '.') }}</strong>
                        </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Dokumen</strong></td>
                        @foreach($offers as $offer)
                        <td style="text-align: center;">
                            @if($offer->quotation_file)
                            <a href="{{ route('procurement.download-quotation', $offer) }}" target="_blank" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">📄 Lihat</a>
                            @else
                            -
                            @endif
                        </td>
                        @endforeach
                    </tr>

                    {{-- Aksi Penetapan --}}
                    @if(in_array(Auth::user()->level, ['Rektor', 'Sarpras', 'Admin']) && !$offers->contains('is_selected', true))
                    <tr>
                        <td><strong>Aksi</strong></td>
                        @foreach($offers as $offer)
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-success" onclick="showSelectModal({{ $offer->id }}, '{{ $offer->vendor_name }}')">
                                Pilih Vendor Ini
                            </button>
                        </td>
                        @endforeach
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Tampilkan Alasan jika sudah dipilih --}}
        @php $selected = $offers->where('is_selected', true)->first(); @endphp
        @if($selected)
        <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 1.5rem; margin-top: 2rem;">
            <h4 style="margin: 0 0 0.5rem; color: #065f46;">✅ Vendor Terpilih: {{ $selected->vendor_name }}</h4>
            <p style="margin: 0; color: #065f46;"><strong>Alasan Pemilihan:</strong> {{ $selected->selection_reason }}</p>
            <p style="margin: 0.5rem 0 0; font-size: 0.85rem; color: #047857;">
                Ditetapkan oleh: {{ $selected->selector->name ?? '-' }} pada {{ $selected->selected_at->format('d F Y H:i') }}
            </p>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Modal Penetapan --}}
<div id="selectModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tetapkan Vendor</h3>
            <button class="close-modal" onclick="closeSelectModal()">×</button>
        </div>
        <form id="selectForm" method="POST">
            @csrf
            <div class="modal-body">
                <p>Anda akan menetapkan <strong id="vendorNameDisplay"></strong> sebagai vendor pemenang.</p>
                <div class="form-group">
                    <label>Alasan Pemilihan <span style="color:red;">*</span></label>
                    <textarea name="selection_reason" class="form-control" rows="3" required placeholder="Jelaskan alasan memilih vendor ini (misal: kualitas terbaik, garansi lebih lama, atau termurah)."></textarea>
                    <small style="color: var(--text-secondary);">Wajib diisi, terutama jika memilih vendor yang bukan termurah.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeSelectModal()">Batal</button>
                <button type="submit" class="btn btn-success">Tetapkan Vendor</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showSelectModal(offerId, vendorName) {
        document.getElementById('selectForm').action = `/procurement/${offerId}/select`;
        document.getElementById('vendorNameDisplay').innerText = vendorName;
        document.getElementById('selectModal').style.display = 'flex';
    }

    function closeSelectModal() {
        document.getElementById('selectModal').style.display = 'none';
    }
</script>
@endpush