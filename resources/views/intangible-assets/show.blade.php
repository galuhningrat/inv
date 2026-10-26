@extends('layouts.app')

@section('title', 'Detail Aset Non-Fisik')
@section('page-title', 'Detail Aset Non-Fisik')

@section('content')
    <div class="data-table-container" style="margin-top: 2rem;">
        <div class="table-header">
            <h3 class="table-title">Detail: {{ $intangibleAsset->name }}</h3>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <a href="{{ route('intangible-assets.index') }}" class="btn btn-secondary">← Kembali</a>
                <a href="{{ route('intangible-assets.edit', $intangibleAsset) }}" class="btn btn-primary">✏️ Edit</a>

                <form action="{{ route('intangible-assets.destroy', $intangibleAsset) }}" method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini? Data yang dihapus tidak dapat dikembalikan.');"
                    style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                        style="background-color: #ef4444; color: white; border: none;">🗑️ Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <div style="padding: 2rem;">
        {{-- ========================================== --}}
        {{-- STATUS BADGE & KODE ASET --}}
        {{-- ========================================== --}}
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: var(--light-bg); padding: 1rem; border-radius: 8px;">
            <div>
                <span style="font-size: 0.9rem; color: var(--text-secondary);">Kode Aset:</span>
                <strong style="font-size: 1.2rem; margin-left: 0.5rem;">{{ $intangibleAsset->asset_code }}</strong>
            </div>
            <div>
                {{-- 🔥 POIN 13: UPDATE WARNA BADGE STATUS 🔥 --}}
                @php
                    $statusColor = match ($intangibleAsset->status) {
                        'Aktif' => '#10b981', // Hijau
                        'Kadaluarsa' => '#ef4444', // Merah
                        'Ditangguhkan' => '#f59e0b', // Kuning/Oranye
                        'Dihentikan' => '#6b7280', // Abu-abu
                        'Belum Aktif' => '#3b82f6', // Biru
                        default => '#6b7280',
                    };
                @endphp
                <span
                    style="background: {{ $statusColor }}; color: white; padding: 0.4rem 1rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600;">
                    {{ $intangibleAsset->status }}
                </span>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- INFORMASI UTAMA --}}
        {{-- ========================================== --}}
        <h4 style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">Informasi
            Utama</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Kategori</span>
                <span
                    class="detail-value">{{ \App\Models\IntangibleAsset::CATEGORIES[$intangibleAsset->category] ?? $intangibleAsset->category }}</span>
            </div>

            @php
                $schema = \App\Models\IntangibleAsset::getSchema($intangibleAsset->category);
                $hideVendor = $schema['hide_vendor'] ?? false;
            @endphp

            @if (!$hideVendor && $intangibleAsset->vendor)
                <div class="detail-item">
                    <span class="detail-label">Vendor / Penyedia</span>
                    <span class="detail-value">{{ $intangibleAsset->vendor }}</span>
                </div>
            @endif

            <div class="detail-item">
                <span class="detail-label">Dibuat Oleh</span>
                <span class="detail-value">{{ $intangibleAsset->creator->name ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Input</span>
                <span class="detail-value">{{ $intangibleAsset->created_at->format('d M Y, H:i') }}</span>
            </div>

            {{-- DESKRIPSI ASET --}}
            @if ($intangibleAsset->description)
                <div class="detail-item" style="grid-column: 1 / -1; margin-top: 0.5rem;">
                    <span class="detail-label">Deskripsi Aset</span>
                    <span class="detail-value"
                        style="font-weight: normal; white-space: pre-wrap; line-height: 1.6;">{{ $intangibleAsset->description }}</span>
                </div>
            @endif
        </div>

        {{-- ========================================== --}}
        {{-- SPESIFIKASI KHUSUS (DINAMIS JSON) — POIN 17.2 --}}
        {{-- ========================================== --}}
        @if ($intangibleAsset->category_data && count($intangibleAsset->category_data) > 0)
            <h4
                style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1rem;">
                Spesifikasi Khusus {{ $intangibleAsset->category }}</h4>
            <div class="detail-grid">
                @foreach ($schema as $key => $field)
                    @if (is_array($field) && isset($field['in_batch']) && $field['in_batch'])
                        @php
                            $value = $intangibleAsset->category_data[$key] ?? null;
                        @endphp

                        {{-- Field kosong tidak ditampilkan --}}
                        @if ($value !== null && $value !== '')
                            <div class="detail-item">
                                <span class="detail-label">{{ $field['label'] }}</span>
                                <span class="detail-value">
                                    @if ($key === 'product_key')
                                        {{-- Tampilkan product key secara tersamar --}}
                                        <span title="Data Sensitif"
                                            style="font-family: monospace; background: #fef3c7; padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid #fcd34d;">
                                            ****-****-{{ substr($value, -4) }}
                                        </span>
                                    @elseif(isset($field['options']) && isset($field['options'][$value]))
                                        {{ $field['options'][$value] }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    @endif
                @endforeach
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- ASPEK FINANSIAL & LEGALITAS --}}
        {{-- ========================================== --}}
        <h4
            style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1rem;">
            Aspek Finansial & Legalitas</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Harga Pembelian / Nilai Perolehan</span>
                <span class="detail-value">Rp {{ number_format($intangibleAsset->price, 0, ',', '.') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Metode Perolehan</span>
                <span class="detail-value">{{ $intangibleAsset->acquisition_method ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Perolehan / Aktivasi</span>
                <span class="detail-value">{{ $intangibleAsset->activation_date->format('d M Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Sumber Pendanaan</span>
                <span class="detail-value">{{ $intangibleAsset->funding_source ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nomor Kontrak / Sertifikat / SK</span>
                <span class="detail-value">{{ $intangibleAsset->contract_number ?? '-' }}</span>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- MASA BERLAKU & HAK PENGGUNAAN — POIN 7 --}}
        {{-- ========================================== --}}
        <h4
            style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1rem;">
            Masa Berlaku & Hak Penggunaan</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Jenis Hak / Lisensi</span>
                <span class="detail-value">{{ $intangibleAsset->license_type }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Model Pembayaran</span>
                <span class="detail-value">{{ $intangibleAsset->billing_model ?? '-' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Tanggal Kedaluwarsa</span>
                <span class="detail-value">
                    @if ($intangibleAsset->expiry_date)
                        <strong style="color: {{ $intangibleAsset->expiry_date->isPast() ? '#ef4444' : 'inherit' }};">
                            {{ $intangibleAsset->expiry_date->format('d M Y') }}
                        </strong>

                        {{-- 🔥 POIN 12: Indikator Sisa Waktu 🔥 --}}
                        @if ($intangibleAsset->expiry_status_badge)
                            <div style="margin-top: 0.25rem;">
                                <span
                                    style="display: inline-block; padding: 0.15rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 4px; background: {{ $intangibleAsset->expiry_status_badge['color'] }}20; color: {{ $intangibleAsset->expiry_status_badge['color'] }}; border: 1px solid {{ $intangibleAsset->expiry_status_badge['color'] }}40;">
                                    {{ $intangibleAsset->expiry_status_badge['label'] }}
                                </span>
                            </div>
                        @endif
                    @elseif ($intangibleAsset->license_type === 'Perpetual')
                        <span style="color: #10b981; font-weight: 600;">Selamanya (Perpetual)</span>
                        <small style="display:block; font-weight: normal; color: #64748b; margin-top: 0.25rem;">
                            Hak penggunaan tidak terbatas waktu.
                        </small>
                    @else
                        -
                    @endif
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Pengingat Sebelum Habis</span>
                <span
                    class="detail-value">{{ $intangibleAsset->reminder_days ? $intangibleAsset->reminder_days . ' hari' : '-' }}</span>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- PENEMPATAN & PENGELOLAAN — POIN 10 --}}
        {{-- ========================================== --}}
        <h4
            style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1rem;">
            Penempatan & Pengelolaan</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Unit Pengelola</span>
                <span class="detail-value">{{ $intangibleAsset->unit->name ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">PIC / Penanggung Jawab</span>
                <span class="detail-value">{{ $intangibleAsset->pic->name ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Kapasitas / Kuota</span>
                <span class="detail-value">{{ $intangibleAsset->quota ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">URL Akses / Dasbor</span>
                <span class="detail-value">
                    @if ($intangibleAsset->access_url)
                        <a href="{{ $intangibleAsset->access_url }}" target="_blank"
                            style="color: var(--primary-color); text-decoration: underline;">
                            {{ $intangibleAsset->access_url }}
                        </a>
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- 🔥 POIN 16: INFO PERMINTAAN PENGADAAN 🔥 --}}
        {{-- ========================================== --}}
        @if ($intangibleAsset->request)
            <h4
                style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1rem;">
                🔗 Permintaan Pengadaan Terkait
            </h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nomor Pengajuan</span>
                    <span class="detail-value">
                        <a href="{{ route('requests.show', $intangibleAsset->request) }}"
                            style="color: var(--primary-color); text-decoration: underline;">
                            {{ $intangibleAsset->request->request_id ?? 'N/A' }}
                        </a>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status Pengajuan</span>
                    <span class="detail-value">
                        {{ $intangibleAsset->request->status_label ?? ($intangibleAsset->request->status ?? '-') }}
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Pengajuan</span>
                    <span class="detail-value">
                        {{ optional($intangibleAsset->request->created_at)->format('d M Y') ?? '-' }}
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Pengaju</span>
                    <span class="detail-value">{{ $intangibleAsset->request->requester->name ?? '-' }}</span>
                </div>
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- DOKUMEN LAMPIRAN — POIN 11 --}}
        {{-- ========================================== --}}
        @if ($intangibleAsset->certificate_file)
            <h4
                style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1rem;">
                Dokumen Lampiran</h4>
            <div class="detail-grid">
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">File Sertifikat / Bukti Kepemilikan</span>
                    <div style="margin-top: 0.5rem;">
                        <a href="{{ route('intangible-assets.download-certificate', $intangibleAsset) }}"
                            class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            ⬇️ Unduh Dokumen
                        </a>
                        <small style="display: block; margin-top: 0.5rem; color: var(--text-secondary);">
                            File disimpan secara privat. Hanya pengguna berwenang yang dapat mengunduh.
                        </small>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- 🔥 POIN 17.4: RIWAYAT PERUBAHAN (AUDIT TRAIL) 🔥 --}}
        {{-- ========================================== --}}
        @if ($intangibleAsset->logs && $intangibleAsset->logs->count() > 0)
            <h4
                style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1rem;">
                📋 Riwayat Perubahan
                <small style="font-weight: normal; color: var(--text-secondary); font-size: 0.85rem; margin-left: 0.5rem;">
                    ({{ $intangibleAsset->logs->count() }} entri)
                </small>
            </h4>
            <div style="background: #f8fafc; border-radius: 8px; padding: 1rem; border: 1px solid var(--border-color);">
                @foreach ($intangibleAsset->logs as $log)
                    <div
                        style="padding: 0.75rem 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border-color);' : '' }}">
                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <span
                                style="font-size: 1.1rem; flex-shrink: 0; line-height: 1.4;">{{ $log->action_icon }}</span>
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                    <span
                                        style="background: {{ $log->action_color }}; color: white; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        {{ $log->action }}
                                    </span>
                                    <span
                                        style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">{{ $log->description }}</span>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                    oleh <strong
                                        style="color: var(--text-primary);">{{ $log->user->name ?? 'Sistem' }}</strong>
                                    • {{ $log->created_at->format('d M Y, H:i') }}
                                </div>

                                {{-- Detail perubahan per field --}}
                                @if ($log->changes && count($log->changes) > 0)
                                    <div
                                        style="margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: white; border-radius: 4px; border-left: 3px solid {{ $log->action_color }};">
                                        @foreach ($log->changes as $field => $change)
                                            <div
                                                style="font-size: 0.8rem; padding: 0.2rem 0; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: baseline;">
                                                <span
                                                    style="font-weight: 600; color: var(--text-secondary); min-width: 130px;">{{ $change['label'] ?? $field }}:</span>
                                                <span
                                                    style="color: #ef4444; text-decoration: line-through;">{{ $change['old'] ?? '-' }}</span>
                                                <span style="color: var(--text-secondary);">→</span>
                                                <span
                                                    style="color: #10b981; font-weight: 600;">{{ $change['new'] ?? '-' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
@endsection

@push('styles')
    <style>
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .detail-value {
            font-size: 1rem;
            color: var(--text-primary);
            font-weight: 600;
            word-break: break-word;
        }
    </style>
@endpush
