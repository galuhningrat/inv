@extends('layouts.app')

@section('title', 'Panduan Pengisian Aset')
@section('page-title', 'Panduan Pengisian Aset')

@section('content')
    <style>
        .accordion {
            background: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .accordion-header {
            background: var(--light-bg);
            padding: 1rem 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--text-primary);
        }

        .accordion-header:hover {
            background: #e2e8f0;
        }

        .accordion-body {
            padding: 1.5rem;
            display: none;
            border-top: 1px solid var(--border-color);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .accordion-body.active {
            display: block;
        }

        .field-list {
            margin: 0;
            padding-left: 1.5rem;
        }

        .field-list li {
            margin-bottom: 0.5rem;
        }

        .field-wajib {
            color: #dc2626;
            font-weight: bold;
        }

        .badge-kategori {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Style untuk Tab */
        .tab-container {
            display: flex;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 1rem;
        }

        .tab-btn {
            padding: 1rem 2rem;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: var(--primary-color);
        }

        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>

    <div class="data-table-container" style="max-width: 900px; margin: 0 auto;">
        <div class="table-header">
            <h3 class="table-title">Panduan Pengisian Formulir Aset</h3>
            <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">← Kembali ke Manajemen Aset</a>
        </div>

        {{-- Tab Navigation --}}
        <div class="tab-container">
            <div class="tab-btn active" onclick="switchTab('fisik', this)">
                📦 Panduan Aset Fisik
            </div>
            <div class="tab-btn" onclick="switchTab('non-fisik', this)">
                💾 Panduan Aset Non-Fisik
            </div>
        </div>

        {{-- Tab Content: Aset Fisik --}}
        <div id="tab-fisik" class="tab-content active">
            @include('guides._assets')
        </div>

        {{-- Tab Content: Aset Non-Fisik --}}
        <div id="tab-non-fisik" class="tab-content">
            @include('guides._intangible')
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        // Fungsi untuk Tab
        function switchTab(tabName, btn) {
            // Sembunyikan semua content
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            // Hapus active dari semua btn
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

            // Tampilkan yang dipilih
            document.getElementById('tab-' + tabName).classList.add('active');
            btn.classList.add('active');
        }

        // Fungsi untuk Accordion (Sama seperti sebelumnya)
        function toggleAccordion(header) {
            const body = header.nextElementSibling;
            const icon = header.querySelector('span:last-child');

            body.classList.toggle('active');

            if (body.classList.contains('active')) {
                icon.textContent = '▲';
                header.style.background = '#e2e8f0';
            } else {
                icon.textContent = '▼';
                header.style.background = 'var(--light-bg)';
            }
        }
    </script>
@endpush
