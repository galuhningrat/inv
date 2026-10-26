@extends('layouts.app')

@section('title', 'Aset Non-Fisik')
@section('page-title', 'Aset Non-Fisik')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Daftar Aset Non-Fisik</h3>
            <a href="{{ route('intangible-assets.create') }}" class="btn btn-primary">+ Tambah Aset Non-Fisik</a>
        </div>

        {{-- 🔥 POIN 15: FORM PENCARIAN & FILTER 🔥 --}}
        <div style="padding: 1rem 2rem; background: #f8fafc; border-bottom: 1px solid var(--border-color);">
            <form action="{{ route('intangible-assets.index') }}" method="GET">
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; align-items: end;">

                    <div>
                        <label
                            style="font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.25rem;">Pencarian
                            Umum</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Nama, Kode, Vendor, No. Kontrak..." value="{{ request('search') }}">
                    </div>

                    <div>
                        <label
                            style="font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.25rem;">Kategori</label>
                        <select name="category" class="form-control">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $val => $label)
                                <option value="{{ $val }}" {{ request('category') === $val ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            style="font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.25rem;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="Aktif" {{ request('status') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Belum Aktif" {{ request('status') === 'Belum Aktif' ? 'selected' : '' }}>Belum
                                Aktif</option>
                            <option value="Ditangguhkan" {{ request('status') === 'Ditangguhkan' ? 'selected' : '' }}>
                                Ditangguhkan</option>
                            <option value="Dihentikan" {{ request('status') === 'Dihentikan' ? 'selected' : '' }}>Dihentikan
                            </option>
                            <option value="Kadaluarsa" {{ request('status') === 'Kadaluarsa' ? 'selected' : '' }}>
                                Kadaluarsa</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.25rem;">Masa
                            Berlaku</label>
                        <select name="expiry_filter" class="form-control">
                            <option value="">Semua</option>
                            <option value="soon" {{ request('expiry_filter') === 'soon' ? 'selected' : '' }}>Segera
                                Kedaluwarsa (≤30 hari)</option>
                            <option value="expired" {{ request('expiry_filter') === 'expired' ? 'selected' : '' }}>Sudah
                                Kedaluwarsa</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">🔍 Cari</button>
                        <a href="{{ route('intangible-assets.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>

                {{-- Pencarian Lanjutan di dalam JSON --}}
                <details style="margin-top: 0.75rem;">
                    <summary style="cursor: pointer; font-size: 0.85rem; color: #3b82f6; font-weight: 500;">▼ Pencarian
                        Spesifik Atribut Kategori (JSON)</summary>
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <div>
                            <label style="font-size: 0.8rem; color: #64748b; display: block;">Nama Field</label>
                            <select name="json_key" class="form-control">
                                <option value="">-- Pilih Field --</option>
                                <option value="domain_name" {{ request('json_key') === 'domain_name' ? 'selected' : '' }}>
                                    domain_name (Domain)</option>
                                <option value="registrar" {{ request('json_key') === 'registrar' ? 'selected' : '' }}>
                                    registrar (Registrar)</option>
                                <option value="registration_number"
                                    {{ request('json_key') === 'registration_number' ? 'selected' : '' }}>
                                    registration_number (No. Registrasi HAKI)</option>
                                <option value="journal_name"
                                    {{ request('json_key') === 'journal_name' ? 'selected' : '' }}>journal_name (Nama
                                    Jurnal)</option>
                                <option value="product_name"
                                    {{ request('json_key') === 'product_name' ? 'selected' : '' }}>product_name (Nama
                                    Produk Software)</option>
                                <option value="issn" {{ request('json_key') === 'issn' ? 'selected' : '' }}>issn (ISSN)
                                </option>
                                <option value="subject_domain"
                                    {{ request('json_key') === 'subject_domain' ? 'selected' : '' }}>subject_domain (Subjek
                                    Sertifikat)</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #64748b; display: block;">Nilai yang Dicari</label>
                            <input type="text" name="json_value" class="form-control" placeholder="mis. stti.ac.id"
                                value="{{ request('json_value') }}">
                        </div>
                    </div>
                </details>
            </form>
        </div>

        {{-- INFO HASIL PENCARIAN --}}
        @if (request()->filled('search') ||
                request()->filled('category') ||
                request()->filled('status') ||
                request()->filled('json_value'))
            <div style="padding: 0.5rem 2rem; background: #fef3c7; font-size: 0.85rem; border-bottom: 1px solid #fcd34d;">
                Menampilkan <strong>{{ $items->total() }}</strong> hasil
                @if (request('search'))
                    dengan kata kunci "<strong>{{ request('search') }}</strong>"
                @endif
                @if (request('category'))
                    di kategori
                    "<strong>{{ \App\Models\IntangibleAsset::CATEGORIES[request('category')] ?? request('category') }}</strong>"
                @endif
            </div>
        @endif

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Aset</th>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th>Vendor / Penyedia</th>
                        <th>Hak / Lisensi</th>
                        <th>Masa Berlaku</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><strong>{{ $item->asset_code }}</strong></td>
                            <td>{{ $item->name }}</td>
                            <td>
                                <span
                                    style="font-size: 0.85rem; background: var(--light-bg); padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid var(--border-color);">
                                    {{ \App\Models\IntangibleAsset::CATEGORIES[$item->category] ?? $item->category }}
                                </span>
                            </td>
                            <td>{{ $item->vendor ?? '-' }}</td>
                            <td>{{ $item->license_type ?? '-' }}</td>
                            <td>
                                @if ($item->expiry_date)
                                    <div>{{ $item->expiry_date->format('d M Y') }}</div>
                                    @if ($item->expiry_status_badge)
                                        <span
                                            style="display: inline-block; margin-top: 0.25rem; padding: 0.15rem 0.5rem; font-size: 0.7rem; font-weight: 600; border-radius: 4px; background: {{ $item->expiry_status_badge['color'] }}20; color: {{ $item->expiry_status_badge['color'] }}; border: 1px solid {{ $item->expiry_status_badge['color'] }}40;">
                                            {{ $item->expiry_status_badge['label'] }}
                                        </span>
                                    @endif
                                @else
                                    <span style="color: var(--text-secondary); font-style: italic;">Selamanya</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColor = match ($item->status) {
                                        'Aktif' => '#10b981',
                                        'Kadaluarsa' => '#ef4444',
                                        'Ditangguhkan' => '#f59e0b',
                                        'Dihentikan' => '#6b7280',
                                        'Belum Aktif' => '#3b82f6',
                                        default => '#6b7280',
                                    };
                                @endphp
                                <span
                                    style="background: {{ $statusColor }}; color: white; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <a href="{{ route('intangible-assets.show', $item) }}" class="btn btn-secondary"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Detail</a>

                                    <form action="{{ route('intangible-assets.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Hapus aset {{ $item->name }}?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                            style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background-color: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:3rem;">
                                <div style="font-size: 2rem; margin-bottom: 1rem;">📭</div>
                                <h4 style="margin: 0; color: var(--text-secondary);">Tidak ada data yang sesuai.</h4>
                                <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                                    @if (request()->hasAny(['search', 'category', 'status', 'json_value']))
                                        Coba ubah kata kunci atau reset filter.
                                    @else
                                        Silakan tambahkan aset baru melalui tombol di atas.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">{{ $items->links() }}</div>
    </div>
@endsection
