@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div id="dashboardContent" class="page-content">
        <!-- Stats Grid -->
        {{-- Di section content, perbaiki grid layout --}}
        <div class="stats-grid">
            @foreach($stats as $key => $value)
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">
                            @if($key == 'total_assets') Total Aset
                            @elseif($key == 'borrowed_assets') Dipinjam
                            @elseif($key == 'available_assets') Tersedia
                            @elseif($key == 'maintenance_assets') Maintenance
                            @endif
                        </div>
                        <div class="stat-icon 
                        @if($key == 'total_assets') primary
                        @elseif($key == 'borrowed_assets') warning
                        @elseif($key == 'available_assets') success
                        @elseif($key == 'maintenance_assets') danger
                        @endif">
                            @if($key == 'total_assets') 🖥️
                            @elseif($key == 'borrowed_assets') 📚
                            @elseif($key == 'available_assets') ✔
                            @elseif($key == 'maintenance_assets') 🛠️
                            @endif
                        </div>
                    </div>
                    <div class="stat-value">{{ $value }}</div>
                </div>
            @endforeach
        </div>

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
                        @foreach($recentAssets as $asset)
                            <tr>
                                <td><strong>{{ $asset->asset_id }}</strong></td>
                                <td>{{ $asset->name }}</td>
                                <td>{{ $asset->assetType->name }}</td>
                                <td>{{ $asset->location }}</td>
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
        document.addEventListener('DOMContentLoaded', function () {
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
                            labels: { padding: 20, usePointStyle: true }
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Status Aset',
                            font: { size: 16, weight: 'bold' },
                            padding: { top: 10, bottom: 30 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
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