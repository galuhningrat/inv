@extends('layouts.app')

@section('title', 'Pemeliharaan')
@section('page-title', 'Pemeliharaan Aset')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Pemeliharaan Aset</h3>
            <a href="{{ route('maintenances.create') }}" class="btn btn-primary">+ Catat Pemeliharaan</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Maintenance</th>
                        <th>Aset</th>
                        <th>Jenis Pemeliharaan</th>
                        <th>Tanggal</th>
                        <th>Biaya</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenances as $maintenance)
                        <tr>
                            <td><strong>{{ $maintenance->maintenance_id }}</strong></td>
                            <td>{{ $maintenance->asset->name }}</td>
                            <td>{{ $maintenance->type }}</td>
                            <td>{{ $maintenance->maintenance_date->format('d M Y') }}</td>
                            <td><span class="price-display">Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</span></td>
                            <td>
                                <span
                                    class="status-badge {{ $maintenance->status === 'Selesai' ? 'available' : 'maintenance' }}">
                                    {{ $maintenance->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary"
                                        onclick="viewAssetDetail({{ $maintenance->asset_id }})">Detail Aset</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">Tidak ada data pemeliharaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">
            {{ $maintenances->links() }}
        </div>
    </div>

    <!-- Asset Detail Modal - LETAKKAN DI SINI -->
    <div id="assetDetailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Aset</h3>
                <button class="close-modal" onclick="closeModal('assetDetailModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div id="assetDetailContent">Loading...</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function viewAssetDetail(assetId) {
            document.getElementById('assetDetailModal').classList.add('active');
            document.getElementById('assetDetailContent').innerHTML = '<p>Memuat data...</p>';

            fetch(`/maintenances/asset/${assetId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('assetDetailContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('assetDetailContent').innerHTML = '<p style="color: red;">Gagal memuat data aset.</p>';
                });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
    </script>
@endpush