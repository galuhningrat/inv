@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div id="dashboardContent" class="page-content">
        <!-- Stats Grid -->
        <div class="stats-grid">
            @foreach ($stats as $key => $value)
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">
                            @if ($key == 'total_assets')
                                Total Aset
                            @elseif($key == 'borrowed_assets')
                                Dipinjam
                            @elseif($key == 'available_assets')
                                Tersedia
                            @elseif($key == 'maintenance_assets')
                                Maintenance
                            @endif
                        </div>
                        <div
                            class="stat-icon 
                        @if ($key == 'total_assets') primary
                        @elseif($key == 'borrowed_assets') warning
                        @elseif($key == 'available_assets') success
                        @elseif($key == 'maintenance_assets') danger @endif">
                            @if ($key == 'total_assets')
                                🖥️
                            @elseif($key == 'borrowed_assets')
                                📚
                            @elseif($key == 'available_assets')
                                ✔
                            @elseif($key == 'maintenance_assets')
                                🛠️
                            @endif
                        </div>
                    </div>
                    <div class="stat-value">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        {{-- 🔥 POIN 12: WIDGET PENGINGAT MASA BERLAKU ASET NON-FISIK 🔥 --}}
        @if (isset($expiringIntangibleAssets) && $expiringIntangibleAssets->count() > 0)
            <div class="data-table-container" style="margin-bottom: 2rem; border-left: 4px solid #f59e0b;">
                <div class="table-header">
                    <h3 class="table-title">⏰ Pengingat Masa Berlaku Aset Non-Fisik</h3>
                    <span
                        style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600;">
                        {{ $expiringIntangibleAssets->count() }} Aset Perlu Perhatian
                    </span>
                </div>
                <div class="table-wrapper" style="padding: 0 1rem 1rem;">
                    <table class="data-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Kode Aset</th>
                                <th>Nama Aset</th>
                                <th>Kategori</th>
                                <th>Tanggal Kedaluwarsa</th>
                                <th>Sisa Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expiringIntangibleAssets as $asset)
                                <tr>
                                    <td><strong>{{ $asset->asset_code }}</strong></td>
                                    <td>{{ $asset->name }}</td>
                                    <td>{{ \App\Models\IntangibleAsset::CATEGORIES[$asset->category] ?? $asset->category }}
                                    </td>
                                    <td>{{ $asset->expiry_date->format('d M Y') }}</td>
                                    <td>
                                        @php $days = $asset->days_until_expiry; @endphp
                                        @if ($days < 0)
                                            <span style="color: #ef4444; font-weight: 600;">Kedaluwarsa</span>
                                        @elseif($days <= 7)
                                            <span style="color: #ef4444; font-weight: 600;">{{ $days }} hari
                                                lagi</span>
                                        @else
                                            <span style="color: #f59e0b; font-weight: 600;">{{ $days }} hari
                                                lagi</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('intangible-assets.show', $asset) }}" class="btn btn-secondary"
                                            style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Chart -->
        <div class="chart-container">
            <canvas id="assetChart"></canvas>
        </div>

        <!-- Recent Assets Table -->
        <div class="data-table-container">
            <div class="table-header">
                <h3 class="table-title">Aset Terbaru</h3>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Aset</th>
                            <th>Nama Aset</th>
                            <th>Jenis</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Tanggal Masuk</th>
                        </tr>
                    </thead>
                    <tbody id="recentAssetsTable">
                        @foreach ($recentAssets as $asset)
                            <tr>
                                <td><strong>{{ $asset->asset_id }}</strong></td>
                                <td>{{ $asset->batch->name ?? '-' }}</td>
                                <td>{{ $asset->batch->assetType->name ?? '-' }}</td>
                                <td>{{ $asset->batch->brand ?? '-' }}</td>
                                <td>
                                    <span
                                        class="status-badge {{ $asset->status === 'Tersedia' ? 'available' : ($asset->status === 'Dipinjam' ? 'borrowed' : 'maintenance') }}">
                                        {{ $asset->status }}
                                    </span>
                                </td>
                                <td>{{ $asset->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Chart
            const ctx = document.getElementById('assetChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [{
                        label: 'Jumlah Aset',
                        data: {!! json_encode($chartData['data']) !!},
                        backgroundColor: {!! json_encode($chartData['colors']) !!},
                        borderColor: ['#059669', '#d97706', '#dc2626'],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Status Aset',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 30
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) :
                                        0;
                                    return `${context.label}: ${value} aset (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '50%'
                }
            });
        });
    </script>
@endpush
