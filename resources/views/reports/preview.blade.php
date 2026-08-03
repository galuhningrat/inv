@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)

@section('content')
    <div class="data-table-container" id="reportContainer">
        <div class="table-header no-print">
            <h3 class="table-title">{{ $title }}</h3>
            <div class="btn-group" style="margin: 0;">
                <button onclick="printReport()" class="btn btn-primary">🖨️ Cetak</button>
                <a href="{{ route('reports.export.pdf') }}?type={{ $type }}" class="btn btn-danger">📄 Export PDF</a>
                <a href="{{ route('reports.export.excel') }}?type={{ $type }}" class="btn btn-success">📊 Export Excel</a>
                <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Kembali</a>
            </div>
        </div>
        <div style="padding: 2rem;" id="printArea">
            <!-- Report Header -->
            <div class="report-header-print"
                style="text-align: center; margin-bottom: 2rem; border-bottom: 3px double var(--border-color); padding-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 1.5rem; margin-bottom: 1rem;">
                    <img src="{{ asset('assets/logo-stti.png') }}" alt="Logo"
                        style="width: 80px; height: 80px; object-fit: contain;">
                    <div style="text-align: left;">
                        <h1 style="margin: 0; font-size: 1.25rem; color: var(--text-primary);">SEKOLAH TINGGI TEKNOLOGI
                            INDONESIA</h1>
                        <h2 style="margin: 0; font-size: 1rem; color: var(--text-secondary); font-weight: normal;">CIREBON
                        </h2>
                        <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--text-secondary);">Jl. Raya Cirebon -
                            Kuningan, Desa Kondangsari</p>
                    </div>
                </div>
                <h2 style="margin: 1rem 0 0.5rem; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 2px;">
                    {{ $title }}</h2>
                <p style="color: var(--text-secondary); font-size: 0.875rem;">
                    Dicetak oleh: <strong>{{ $user->name }}</strong> | Tanggal:
                    <strong>{{ now()->format('d F Y H:i') }}</strong>
                </p>
            </div>

            @if($type === 'assets')
                <!-- Assets Report -->
                <div class="table-wrapper">
                    <table class="data-table" style="font-size: 0.8rem;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Aset</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Merek</th>
                                <th>Lokasi</th>
                                <th>Kondisi</th>
                                <th>Status</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $index => $asset)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $asset->asset_id }}</td>
                                    <td>{{ $asset->name }}</td>
                                    <td>{{ $asset->assetType->name }}</td>
                                    <td>{{ $asset->brand }}</td>
                                    <td>{{ $asset->location }}</td>
                                    <td>
                                        <span
                                            class="status-badge {{ $asset->condition === 'Baik' ? 'available' : ($asset->condition === 'Rusak Ringan' ? 'borrowed' : 'maintenance') }}">
                                            {{ $asset->condition }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="status-badge {{ $asset->status === 'Tersedia' ? 'available' : ($asset->status === 'Dipinjam' ? 'borrowed' : 'maintenance') }}">
                                            {{ $asset->status }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">Rp {{ number_format($asset->price, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--light-bg); font-weight: bold;">
                                <td colspan="8" style="text-align: right;">Total Nilai Aset:</td>
                                <td style="text-align: right;">Rp {{ number_format($data->sum('price'), 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="9" style="text-align: center; font-size: 0.75rem; color: var(--text-secondary);">
                                    Total: {{ $data->count() }} aset
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            @elseif($type === 'borrowing')
                <!-- Borrowing Report -->
                <div class="table-wrapper">
                    <table class="data-table" style="font-size: 0.8rem;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Pinjam</th>
                                <th>Aset</th>
                                <th>Peminjam</th>
                                <th>Jabatan</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $index => $borrowing)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $borrowing->borrowing_id }}</td>
                                    <td>{{ $borrowing->asset->name ?? '-' }}</td>
                                    <td>{{ $borrowing->borrower_name }}</td>
                                    <td>{{ $borrowing->borrower_role }}</td>
                                    <td>{{ $borrowing->borrow_date->format('d/m/Y') }}</td>
                                    <td>{{ $borrowing->actual_return_date ? $borrowing->actual_return_date->format('d/m/Y') : $borrowing->return_date->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <span
                                            class="status-badge {{ $borrowing->status === 'Selesai' ? 'available' : ($borrowing->status === 'Terlambat' ? 'maintenance' : 'borrowed') }}">
                                            {{ $borrowing->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8" style="text-align: center; font-size: 0.75rem; color: var(--text-secondary);">
                                    Total: {{ $data->count() }} peminjaman |
                                    Aktif: {{ $data->where('status', 'Aktif')->count() }} |
                                    Selesai: {{ $data->where('status', 'Selesai')->count() }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            @elseif($type === 'maintenance')
                <!-- Maintenance Report -->
                <div class="table-wrapper">
                    <table class="data-table" style="font-size: 0.8rem;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Aset</th>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th>Teknisi</th>
                                <th>Biaya</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $index => $maintenance)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $maintenance->maintenance_id }}</td>
                                    <td>{{ $maintenance->asset->name ?? '-' }}</td>
                                    <td>{{ $maintenance->type }}</td>
                                    <td>{{ $maintenance->maintenance_date->format('d/m/Y') }}</td>
                                    <td>{{ $maintenance->technician }}</td>
                                    <td style="text-align: right;">Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</td>
                                    <td>
                                        <span
                                            class="status-badge {{ $maintenance->status === 'Selesai' ? 'available' : 'maintenance' }}">
                                            {{ $maintenance->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--light-bg); font-weight: bold;">
                                <td colspan="6" style="text-align: right;">Total Biaya:</td>
                                <td colspan="2" style="text-align: left;">Rp
                                    {{ number_format($data->sum('cost'), 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align: center; font-size: 0.75rem; color: var(--text-secondary);">
                                    Total: {{ $data->count() }} record pemeliharaan
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            @elseif($type === 'financial')
                <!-- Financial Report -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center;">
                        <h4 style="margin-bottom: 0.5rem; opacity: 0.9; font-size: 0.875rem;">Total Aset</h4>
                        <p style="font-size: 2.5rem; font-weight: bold; margin: 0;">{{ $data['total_assets'] }}</p>
                        <p style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">unit terdaftar</p>
                    </div>
                    <div
                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center;">
                        <h4 style="margin-bottom: 0.5rem; opacity: 0.9; font-size: 0.875rem;">Total Nilai Aset</h4>
                        <p style="font-size: 1.25rem; font-weight: bold; margin: 0;">Rp
                            {{ number_format($data['total_value'], 0, ',', '.') }}</p>
                        <p style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">nilai inventaris</p>
                    </div>
                    <div
                        style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center;">
                        <h4 style="margin-bottom: 0.5rem; opacity: 0.9; font-size: 0.875rem;">Biaya Pemeliharaan</h4>
                        <p style="font-size: 1.25rem; font-weight: bold; margin: 0;">Rp
                            {{ number_format($data['maintenance_cost'], 0, ',', '.') }}</p>
                        <p style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">total biaya</p>
                    </div>
                    <div
                        style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center;">
                        <h4 style="margin-bottom: 0.5rem; opacity: 0.9; font-size: 0.875rem;">Rata-rata Nilai</h4>
                        <p style="font-size: 1.25rem; font-weight: bold; margin: 0;">Rp
                            {{ number_format($data['average_value'], 0, ',', '.') }}</p>
                        <p style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">per aset</p>
                    </div>
                </div>

                <!-- Summary Table -->
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total Aset Terdaftar</td>
                                <td><strong>{{ $data['total_assets'] }} unit</strong></td>
                            </tr>
                            <tr>
                                <td>Total Nilai Aset</td>
                                <td><strong>Rp {{ number_format($data['total_value'], 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td>Total Biaya Pemeliharaan</td>
                                <td><strong>Rp {{ number_format($data['maintenance_cost'], 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td>Rata-rata Nilai Aset</td>
                                <td><strong>Rp {{ number_format($data['average_value'], 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td>Jumlah Record Pemeliharaan</td>
                                <td><strong>{{ $data['maintenance_records'] }} record</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Signature Section -->
            <div style="margin-top: 3rem; display: flex; justify-content: flex-end;">
                <div style="text-align: center; min-width: 200px;">
                    <p style="margin-bottom: 4rem;">Cirebon, {{ now()->format('d F Y') }}</p>
                    <p style="margin: 0; font-weight: bold;">{{ $user->name }}</p>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">{{ $user->level }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function printReport() {
            window.print();
        }
    </script>
@endpush

@push('styles')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .data-table-container {
                box-shadow: none !important;
                border: none !important;
            }

            #printArea {
                padding: 0 !important;
            }

            .status-badge {
                border: 1px solid currentColor !important;
                background: transparent !important;
            }

            .report-header-print {
                border-bottom: 2px solid #000 !important;
            }
        }
    </style>
@endpush