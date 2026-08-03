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
                        <span class="status-badge {{ $borrowing->status === 'Aktif' ? 'borrowed' : 'available' }}">
                            {{ $borrowing->status }}
                        </span>
                    </p>
                </div>

                <div>
                    <h4 style="margin-bottom: 1rem;">Informasi Aset</h4>
                    <p><strong>Nama Aset:</strong> {{ $borrowing->asset->name }}</p>
                    <p><strong>ID Aset:</strong> {{ $borrowing->asset->asset_id }}</p>
                    <p><strong>Jenis:</strong> {{ $borrowing->asset->assetType->name }}</p>
                    <p><strong>Lokasi:</strong> {{ $borrowing->asset->location }}</p>
                </div>
            </div>

            <div class="purpose-section">
                <h4 style="margin-bottom: 1rem;">Tujuan Peminjaman</h4>
                <div style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                    {{ $borrowing->purpose }}
                </div>
            </div>

            @if($borrowing->actual_return_date)
                <div style="margin-top: 1.5rem;">
                    <p><strong>Tanggal Pengembalian Aktual:</strong> {{ $borrowing->actual_return_date->format('d F Y') }}</p>
                </div>
            @endif

            <div class="btn-group" style="margin-top: 2rem; justify-content: center;">
                @if($borrowing->status === 'Aktif')
                    <form action="{{ route('borrowings.return', $borrowing) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Konfirmasi pengembalian aset?')">
                            ✔ Kembalikan Aset
                        </button>
                    </form>
                @endif
                <a href="{{ route('borrowings.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
            </div>
        </div>
    </div>

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