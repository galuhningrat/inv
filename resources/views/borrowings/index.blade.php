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
                                <span class="status-badge {{ $borrowing->status === 'Aktif' ? 'borrowed' : 'available' }}">
                                    {{ $borrowing->status }}
                                </span>
                            </td>
                            <td>
                                @if($borrowing->status === 'Aktif')
                                    <form action="{{ route('borrowings.return', $borrowing) }}" method="POST"
                                        style="display: inline;" onsubmit="return confirm('Konfirmasi pengembalian aset?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Kembalikan</button>
                                    </form>
                                @else
                                    <span style="color: var(--text-secondary); font-size: 0.75rem;">{{ $borrowing->status }}</span>
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
@endsection