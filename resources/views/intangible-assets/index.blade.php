@extends('layouts.app')

@section('title', 'Aset Non-Fisik')
@section('page-title', 'Aset Non-Fisik (Prototipe)')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Aset Non-Fisik <span
                    style="font-size: 0.75rem; color: var(--text-secondary); font-weight: normal;">(Prototipe)</span></h3>
            <a href="{{ route('intangible-assets.create') }}" class="btn btn-primary">+ Tambah Aset Non-Fisik</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Vendor</th>
                        <th>Jenis Lisensi</th>
                        <th>Kedaluwarsa</th>
                        <th>Unit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><strong>{{ $item->asset_code }}</strong></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category }}</td>
                            <td>{{ $item->vendor }}</td>
                            <td>{{ $item->license_type }}</td>
                            <td>{{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '-' }}</td>
                            <td>{{ $item->unit->name ?? '-' }}</td>
                            <td><a href="{{ route('intangible-assets.show', $item) }}" class="btn btn-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:2rem;">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">{{ $items->links() }}</div>
    </div>
@endsection
