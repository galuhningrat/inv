@extends('layouts.app')

@section('title', 'Input Penawaran Vendor')

@section('content')
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Input Penawaran Vendor</h3>
        <a href="{{ route('procurement.index', $assetRequest) }}" class="btn btn-secondary">← Kembali</a>
    </div>

    <div style="padding: 2rem;">
        <div style="background: #eff6ff; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <p style="margin: 0;"><strong>Pengajuan:</strong> {{ $assetRequest->request_id }} — {{ $assetRequest->requester->name }}</p>
        </div>

        <form action="{{ route('procurement.store', $assetRequest) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h4 style="margin-bottom: 1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">Informasi Vendor</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Vendor / Supplier <span style="color:red;">*</span></label>
                    <input type="text" name="vendor_name" class="form-control" required value="{{ old('vendor_name') }}">
                </div>
                <div class="form-group">
                    <label>Nama Kontak (PIC)</label>
                    <input type="text" name="vendor_contact" class="form-control" value="{{ old('vendor_contact') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email Vendor</label>
                    <input type="email" name="vendor_email" class="form-control" value="{{ old('vendor_email') }}">
                </div>
                <div class="form-group">
                    <label>Telepon Vendor</label>
                    <input type="text" name="vendor_phone" class="form-control" value="{{ old('vendor_phone') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nomor Surat Penawaran</label>
                    <input type="text" name="offer_number" class="form-control" value="{{ old('offer_number') }}">
                </div>
                <div class="form-group">
                    <label>Tanggal Penawaran <span style="color:red;">*</span></label>
                    <input type="date" name="offer_date" class="form-control" required value="{{ old('offer_date', date('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label>Berlaku Sampai</label>
                    <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until') }}">
                </div>
            </div>

            <h4 style="margin: 2rem 0 1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">Rincian Harga Barang</h4>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Harga Satuan (Rp) <span style="color:red;">*</span></th>
                            <th>Diskon (Rp)</th>
                            <th>Pajak (Rp)</th>
                            <th>Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assetRequest->items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                            <td>
                                <input type="number" name="items[{{ $item->id }}][unit_price]" class="form-control calc-price" data-qty="{{ $item->quantity }}" data-id="{{ $item->id }}" required min="0">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $item->id }}][discount]" class="form-control calc-price" data-qty="{{ $item->quantity }}" data-id="{{ $item->id }}" min="0" value="0">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $item->id }}][tax]" class="form-control calc-price" data-qty="{{ $item->quantity }}" data-id="{{ $item->id }}" min="0" value="0">
                            </td>
                            <td>
                                <strong id="subtotal-{{ $item->id }}">Rp 0</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="form-row" style="margin-top: 1.5rem; justify-content: flex-end;">
                <div class="form-group" style="max-width: 300px;">
                    <label>Pajak Keseluruhan (Rp)</label>
                    <input type="number" name="tax_amount" class="form-control" value="{{ old('tax_amount', 0) }}" min="0">
                </div>
                <div class="form-group" style="max-width: 300px;">
                    <label>Ongkos Kirim (Rp)</label>
                    <input type="number" name="shipping_cost" class="form-control" value="{{ old('shipping_cost', 0) }}" min="0">
                </div>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label>Upload Dokumen Penawaran (PDF/Gambar)</label>
                <input type="file" name="quotation_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="form-group">
                <label>Catatan Tambahan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="btn-group" style="margin-top: 1.5rem;">
                <a href="{{ route('procurement.index', $assetRequest) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success">Simpan Penawaran</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.calc-price').forEach(input => {
        input.addEventListener('input', function() {
            const id = this.dataset.id;
            const qty = parseInt(this.dataset.qty);

            const unitPrice = parseFloat(document.querySelector(`input[name="items[${id}][unit_price]"]`).value) || 0;
            const discount = parseFloat(document.querySelector(`input[name="items[${id}][discount]"]`).value) || 0;
            const tax = parseFloat(document.querySelector(`input[name="items[${id}][tax]"]`).value) || 0;

            const subtotal = (unitPrice * qty) - discount + tax;
            document.getElementById(`subtotal-${id}`).innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        });
    });
</script>
@endpush