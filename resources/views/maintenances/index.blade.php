@extends('layouts.app')

@section('title', 'Pemeliharaan')
@section('page-title', 'Pemeliharaan Aset')

@section('content')
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Pemeliharaan Aset</h3>
        @can('create', App\Models\Maintenance::class)
        <a href="{{ route('maintenances.create') }}" class="btn btn-primary">+ Catat Pemeliharaan</a>
        @endcan
    </div>

    {{-- Kartu Statistik Ringkas --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; padding: 0 2rem; margin-bottom: 1.5rem;">
        <div style="background: #fef3c7; border-radius: 8px; padding: 1rem 1.25rem;">
            <p style="margin: 0; font-size: 0.8rem; color: #92400e;">⏳ Menunggu Verifikasi</p>
            <p style="margin: 0.25rem 0 0; font-size: 1.5rem; font-weight: 700; color: #78350f;">{{ $stats['menunggu_verifikasi'] }}</p>
        </div>
        <div style="background: #dbeafe; border-radius: 8px; padding: 1rem 1.25rem;">
            <p style="margin: 0; font-size: 0.8rem; color: #1e40af;">🔧 Dalam Proses</p>
            <p style="margin: 0.25rem 0 0; font-size: 1.5rem; font-weight: 700; color: #1e3a8a;">{{ $stats['dalam_proses'] }}</p>
        </div>
        <div style="background: #d1fae5; border-radius: 8px; padding: 1rem 1.25rem;">
            <p style="margin: 0; font-size: 0.8rem; color: #065f46;">✅ Selesai</p>
            <p style="margin: 0.25rem 0 0; font-size: 1.5rem; font-weight: 700; color: #047857;">{{ $stats['selesai'] }}</p>
        </div>
    </div>

    {{-- Filter Status --}}
    <div style="padding: 0 2rem 1rem 2rem;">
        <form method="GET" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <label style="font-size: 0.9rem; font-weight: 500;">Filter Status:</label>
            <select name="status" class="form-control" style="width: auto; min-width: 200px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Menunggu Verifikasi" {{ request('status') === 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="Diterima" {{ request('status') === 'Diterima' ? 'selected' : '' }}>Diterima</option>
                <option value="Dalam Proses" {{ request('status') === 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                <option value="Menunggu Komponen" {{ request('status') === 'Menunggu Komponen' ? 'selected' : '' }}>Menunggu Komponen</option>
                <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Aset</th>
                    <th>Jenis</th>
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
                    <td>
                        {{ $maintenance->asset->batch->name ?? '-' }}
                        <br><small style="color: var(--text-secondary); font-family: monospace;">{{ $maintenance->asset->asset_id }}</small>
                    </td>
                    <td>{{ $maintenance->type }}</td>
                    <td>{{ $maintenance->maintenance_date->format('d M Y') }}</td>
                    <td><span class="price-display">Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</span></td>
                    <td>
                        <span class="status-badge {{ $maintenance->status_badge_class }}">
                            {{ $maintenance->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            @if($maintenance->isAwaitingVerification())
                            @can('verify', $maintenance)
                            <a href="{{ route('maintenances.show', $maintenance->id) }}"
                                class="btn btn-warning" style="background: #f59e0b; color: white;">
                                🔍 Verifikasi
                            </a>
                            @else
                            <a href="{{ route('maintenances.show', $maintenance->id) }}"
                                class="btn btn-secondary">Lihat Detail</a>
                            @endcan
                            @else
                            @can('update', $maintenance)
                            <a href="{{ route('maintenances.show', $maintenance->id) }}"
                                class="btn btn-secondary">Kelola Status</a>
                            @else
                            <a href="{{ route('maintenances.show', $maintenance->id) }}"
                                class="btn btn-secondary">Lihat Detail</a>
                            @endcan
                            @endif
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
        {{ $maintenances->withQueryString()->links() }}
    </div>
</div>

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
                document.getElementById('assetDetailContent').innerHTML =
                    '<p style="color: red;">Gagal memuat data aset.</p>';
            });
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }
</script>
@endpush