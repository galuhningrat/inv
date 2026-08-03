@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Generate Laporan</h3>
        </div>
        <div style="padding: 2rem;">
            <div class="report-options"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <!-- Laporan Aset -->
                <div class="report-card"
                    style="background: var(--card-background); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">🖥️</div>
                    <h4 style="margin-bottom: 0.5rem;">Laporan Inventaris Aset</h4>
                    <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">Daftar lengkap semua
                        aset yang terdaftar</p>
                    <div class="btn-group">
                        <form action="{{ route('reports.generate') }}" method="POST" style="display: inline;">
                            @csrf
                            <input type="hidden" name="type" value="assets">
                            <button type="submit" class="btn btn-primary">Preview</button>
                        </form>
                        <a href="{{ route('reports.export.pdf') }}?type=assets" class="btn btn-secondary">PDF</a>
                        <a href="{{ route('reports.export.excel') }}?type=assets" class="btn btn-secondary">Excel</a>
                    </div>
                </div>

                <!-- Laporan Peminjaman -->
                <div class="report-card"
                    style="background: var(--card-background); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">📚</div>
                    <h4 style="margin-bottom: 0.5rem;">Laporan Peminjaman</h4>
                    <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">Riwayat peminjaman
                        aset</p>
                    <div class="btn-group">
                        <form action="{{ route('reports.generate') }}" method="POST" style="display: inline;">
                            @csrf
                            <input type="hidden" name="type" value="borrowing">
                            <button type="submit" class="btn btn-primary">Preview</button>
                        </form>
                        <a href="{{ route('reports.export.pdf') }}?type=borrowing" class="btn btn-secondary">PDF</a>
                        <a href="{{ route('reports.export.excel') }}?type=borrowing" class="btn btn-secondary">Excel</a>
                    </div>
                </div>

                <!-- Laporan Pemeliharaan -->
                <div class="report-card"
                    style="background: var(--card-background); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">🛠️</div>
                    <h4 style="margin-bottom: 0.5rem;">Laporan Pemeliharaan</h4>
                    <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">Catatan pemeliharaan
                        aset</p>
                    <div class="btn-group">
                        <form action="{{ route('reports.generate') }}" method="POST" style="display: inline;">
                            @csrf
                            <input type="hidden" name="type" value="maintenance">
                            <button type="submit" class="btn btn-primary">Preview</button>
                        </form>
                        <a href="{{ route('reports.export.pdf') }}?type=maintenance" class="btn btn-secondary">PDF</a>
                        <a href="{{ route('reports.export.excel') }}?type=maintenance" class="btn btn-secondary">Excel</a>
                    </div>
                </div>

                <!-- Laporan Keuangan -->
                <div class="report-card"
                    style="background: var(--card-background); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">💰</div>
                    <h4 style="margin-bottom: 0.5rem;">Laporan Keuangan</h4>
                    <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">Ringkasan nilai aset
                        & biaya</p>
                    <div class="btn-group">
                        <form action="{{ route('reports.generate') }}" method="POST" style="display: inline;">
                            @csrf
                            <input type="hidden" name="type" value="financial">
                            <button type="submit" class="btn btn-primary">Preview</button>
                        </form>
                        <a href="{{ route('reports.export.pdf') }}?type=financial" class="btn btn-secondary">PDF</a>
                        <a href="{{ route('reports.export.excel') }}?type=financial" class="btn btn-secondary">Excel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection