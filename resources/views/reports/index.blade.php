@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Generate Laporan</h3>
        </div>
        <div style="padding: 2rem;">

            {{-- 🔥 FILTER TANGGAL GLOBAL 🔥 --}}
            <div
                style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border: 1px dashed #cbd5e1; margin-bottom: 2rem;">
                <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 1rem; color: #1e293b;">📅 Filter Berdasarkan
                    Tanggal Perolehan (Opsional)</h4>
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="margin: 0;">
                        <label>Dari Tanggal</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Sampai Tanggal</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <button onclick="resetDateFilter()" class="btn btn-secondary"
                        style="padding: 0.6rem 1rem;">Reset</button>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-secondary);">* Jika dikosongkan, laporan
                    akan mencakup seluruh data.</p>
            </div>

            <div class="report-options"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">

                @if ($allowedTypes->contains('assets'))
                    <div class="report-card"
                        style="background: var(--card-background); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">🖥️</div>
                        <h4 style="margin-bottom: 0.5rem;">Laporan Inventaris Aset</h4>
                        <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">Daftar lengkap
                            semua aset yang terdaftar</p>
                        <div class="btn-group">
                            <button onclick="exportReport('assets', 'pdf')" class="btn btn-danger">📄 PDF</button>
                            <button onclick="exportReport('assets', 'excel')" class="btn btn-success">📊 Excel</button>
                        </div>
                    </div>
                @endif

                @if ($allowedTypes->contains('borrowing'))
                    <div class="report-card"
                        style="background: var(--card-background); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">📚</div>
                        <h4 style="margin-bottom: 0.5rem;">Laporan Peminjaman</h4>
                        <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">Riwayat
                            peminjaman aset</p>
                        <div class="btn-group">
                            <button onclick="exportReport('borrowing', 'pdf')" class="btn btn-danger">📄 PDF</button>
                            <button onclick="exportReport('borrowing', 'excel')" class="btn btn-success">📊 Excel</button>
                        </div>
                    </div>
                @endif

                @if ($allowedTypes->contains('maintenance'))
                    <div class="report-card"
                        style="background: var(--card-background); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">🛠️</div>
                        <h4 style="margin-bottom: 0.5rem;">Laporan Pemeliharaan</h4>
                        <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">Catatan
                            pemeliharaan aset</p>
                        <div class="btn-group">
                            <button onclick="exportReport('maintenance', 'pdf')" class="btn btn-danger">📄 PDF</button>
                            <button onclick="exportReport('maintenance', 'excel')" class="btn btn-success">📊 Excel</button>
                        </div>
                    </div>
                @endif

                @if ($allowedTypes->contains('financial'))
                    <div class="report-card"
                        style="background: var(--card-background); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">💰</div>
                        <h4 style="margin-bottom: 0.5rem;">Laporan Keuangan</h4>
                        <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">Ringkasan nilai
                            aset & biaya</p>
                        <div class="btn-group">
                            <button onclick="exportReport('financial', 'pdf')" class="btn btn-danger">📄 PDF</button>
                            <button onclick="exportReport('financial', 'excel')" class="btn btn-success">📊 Excel</button>
                        </div>
                    </div>
                @endif

                @if ($allowedTypes->isEmpty())
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <p style="font-size: 3rem; margin-bottom: 1rem;">🔒</p>
                        <p>Anda tidak memiliki akses ke laporan apapun.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function resetDateFilter() {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
        }

        function exportReport(type, format) {
            const start = document.getElementById('start_date').value;
            const end = document.getElementById('end_date').value;

            // Tentukan Base URL berdasarkan format
            let baseUrl = format === 'pdf' ? '{{ route('reports.export.pdf') }}' : '{{ route('reports.export.excel') }}';

            // Buat parameter URL
            let params = new URLSearchParams();
            params.append('type', type);
            if (start) params.append('start_date', start);
            if (end) params.append('end_date', end);

            // Arahkan ke URL Export
            window.location.href = `${baseUrl}?${params.toString()}`;
        }
    </script>
@endpush
