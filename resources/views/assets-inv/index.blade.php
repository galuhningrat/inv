@extends('layouts.app')

@section('title', 'Manajemen Aset')
@section('page-title', 'Manajemen Aset')

@section('content')
    <style>
        .view-toggle-wrapper {
            padding: 0 2rem;
            margin-bottom: 0.75rem;
            margin-top: 0.5rem;
        }

        .view-toggle {
            display: inline-flex;
            background: #f3f4f6;
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
            border: 1px solid #e5e7eb;
            flex-wrap: wrap;
        }

        .view-toggle-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            color: #6b7280;
            background: transparent;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .view-toggle-btn .view-toggle-icon {
            font-size: 1.2rem;
            line-height: 1;
        }

        .view-toggle-btn .view-toggle-label {
            font-weight: 500;
        }

        .view-toggle-btn:hover {
            color: #111827;
            background: rgba(0, 0, 0, 0.04);
        }

        .view-toggle-btn.active {
            background: #ffffff;
            color: #2563eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
            font-weight: 600;
        }

        .view-toggle-btn.active .view-toggle-label {
            color: #2563eb;
        }

        body.dark-mode .view-toggle {
            background: #1f2937;
            border-color: #374151;
        }

        body.dark-mode .view-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        body.dark-mode .view-toggle-btn.active {
            background: #111827;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        @media screen and (max-width: 640px) {
            .view-toggle-wrapper {
                padding: 0 1rem;
            }

            .view-toggle {
                width: 100%;
                justify-content: stretch;
            }

            .view-toggle-btn {
                flex: 1;
                justify-content: center;
                padding: 0.5rem 0.8rem;
                font-size: 0.78rem;
            }

            .view-toggle-btn .view-toggle-label {
                display: none;
            }

            .view-toggle-btn .view-toggle-icon {
                font-size: 1.4rem;
            }
        }

        @media screen and (max-width: 480px) {
            .view-toggle-btn {
                padding: 0.4rem 0.6rem;
            }

            .view-toggle-btn .view-toggle-icon {
                font-size: 1.2rem;
            }
        }
    </style>

    @if ($view === 'fisik')
        <div class="bulk-action-bar" id="bulkActionBar">
            <p><span id="selectedCount">0</span> aset terpilih</p>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button class="btn btn-secondary" onclick="exportSelected('pdf')">📄 Export PDF</button>
                <button class="btn btn-success" onclick="exportSelected('excel')">📊 Export Excel</button>
                <button class="btn" onclick="toggleBulkMode()">Batal</button>
                <button class="btn btn-danger" onclick="bulkDelete()">Hapus Terpilih</button>
            </div>
        </div>
    @endif

    <div class="data-table-container">
        <div class="view-toggle-wrapper">
            <div class="view-toggle">
                <a href="{{ route('assets-inv.index') }}" class="view-toggle-btn {{ $view === 'semua' ? 'active' : '' }}"
                    data-view="semua">
                    <span class="view-toggle-icon">📋</span>
                    <span class="view-toggle-label">Semua Aset</span>
                </a>
                <a href="{{ route('assets-inv.index', ['view' => 'fisik']) }}"
                    class="view-toggle-btn {{ $view === 'fisik' ? 'active' : '' }}" data-view="fisik">
                    <span class="view-toggle-icon">📦</span>
                    <span class="view-toggle-label">Aset Fisik</span>
                </a>
                <a href="{{ route('assets-inv.index', ['view' => 'non-fisik']) }}"
                    class="view-toggle-btn {{ $view === 'non-fisik' ? 'active' : '' }}" data-view="non-fisik">
                    <span class="view-toggle-icon">💾</span>
                    <span class="view-toggle-label">Aset Non-Fisik</span>
                </a>
            </div>
        </div>

        <div class="table-header">
            <h3 class="table-title">Manajemen Data Aset</h3>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" id="assetSearch" placeholder="Cari aset..."
                        value="{{ request('search') }}">
                </div>
                <select class="form-control" id="filterUnit" style="width: auto; min-width: 180px;">
                    <option value="">Semua Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->type }}: {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
                <select class="form-control" id="filterStatus" style="width: auto; min-width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="Tersedia" {{ request('status') === 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="Dipinjam" {{ request('status') === 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="Maintenance" {{ request('status') === 'Maintenance' ? 'selected' : '' }}>Maintenance
                    </option>
                    <option value="Diganti" {{ request('status') === 'Diganti' ? 'selected' : '' }}>Diganti</option>
                    <option value="Kadaluarsa" {{ request('status') === 'Kadaluarsa' ? 'selected' : '' }}>Kadaluarsa
                    </option>
                </select>
                <select class="form-control" id="filterType" style="width: auto; min-width: 150px;">
                    <option value="">Semua Jenis</option>
                    @foreach ($assetTypes as $type)
                        <option value="{{ $type->code }}" {{ request('type') === $type->code ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('guides.index') }}" class="btn btn-secondary"
                    style="background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;">
                    📖 Panduan
                </a>

                @can('create', App\Models\Asset::class)
                    <a href="{{ route('assets-inv.create') }}" class="btn btn-primary">+ Tambah Aset</a>
                @endcan
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table" id="assetsTable">
                <thead>
                    @if ($view === 'fisik')
                        <tr>
                            {{-- Hanya tampilkan header checkbox di view fisik --}}
                            <th class="col-checkbox" style="width: 40px;"><input type="checkbox" id="selectAll"
                                    onchange="toggleSelectAll(this)"></th>
                            <th>ID Aset</th>
                            <th>Gambar</th>
                            <th>Nama Aset</th>
                            <th>Jenis</th>
                            <th>Merek</th>
                            <th>Lokasi</th>
                            <th>{{ $isGrouped ? 'Kuantitas' : 'Kondisi' }}</th>
                            <th>{{ $isGrouped ? 'Ringkasan Status' : 'Status' }}</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    @elseif($view === 'non-fisik')
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Vendor</th>
                            <th>Unit</th>
                            <th>Kuantitas</th>
                            <th>Jenis Lisensi</th>
                            <th>Harga/Unit</th>
                            <th>Aksi</th>
                        </tr>
                    @else
                        {{-- view === 'semua' --}}
                        <tr>
                            <th>Tipe</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Kuantitas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @if ($view === 'semua')
                        @forelse($combined as $row)
                            <tr>
                                <td><span
                                        class="status-badge {{ $row->type === 'Fisik' ? 'available' : 'borrowed' }}">{{ $row->type === 'Fisik' ? '📦 Fisik' : '💾 Non-Fisik' }}</span>
                                </td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->category_label }}</td>
                                <td>{{ $row->unit_label }}</td>
                                <td><span class="status-badge available">{{ $row->quantity }} unit</span></td>
                                <td>{{ $row->status_label }}</td>
                                <td><a href="{{ $row->detail_route }}" class="btn btn-secondary">Detail</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; padding:2rem;">Tidak ada data aset.</td>
                            </tr>
                        @endforelse
                    @elseif($view === 'non-fisik')
                        @forelse($groupedNonFisik as $group)
                            <tr>
                                <td>{{ $group->name }}</td>
                                <td>{{ $group->category }}</td>
                                <td>{{ $group->vendor }}</td>
                                <td>{{ $group->unit->name ?? '-' }}</td>
                                <td>
                                    <span class="status-badge available">{{ $group->total_quantity }} unit</span>
                                    @if ($group->kadaluarsa_count > 0)
                                        <br><small style="color:#dc3545;">{{ $group->kadaluarsa_count }} kadaluarsa</small>
                                    @endif
                                </td>
                                <td>{{ $group->license_type }}</td>
                                <td>Rp {{ number_format($group->price, 0, ',', '.') }}</td>
                                <td><a href="{{ route('intangible-assets.show', $group->sample_id) }}"
                                        class="btn btn-secondary">Detail</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:2rem;">Tidak ada data aset non-fisik.
                                </td>
                            </tr>
                        @endforelse
                    @else
                        {{-- view === 'fisik' --}}
                        @if ($isGrouped)
                            @forelse($grouped as $batch)
                                <tr>
                                    <td class="col-checkbox">
                                        <input type="checkbox" class="asset-checkbox" value="{{ $batch->id }}"
                                            data-type="batch" onchange="updateSelectedCount()">
                                    </td>
                                    <td><span style="color: var(--text-secondary);">—</span></td>
                                    <td>
                                        <img src="{{ $batch->assets->first()->image_url ?? asset('assets/logo-stti.png') }}"
                                            alt="{{ $batch->name }}" class="asset-image-preview"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td>
                                        <strong>{{ $batch->name }}</strong><br>
                                        <small style="color: var(--text-secondary);">{{ $batch->model }}</small>
                                    </td>
                                    <td>{{ $batch->assetType->name ?? '-' }}</td>
                                    <td>{{ $batch->brand }}</td>
                                    <td><span style="color: var(--text-secondary);">Multi-Unit</span></td>
                                    <td><span class="status-badge available">{{ $batch->assets_count }} unit</span></td>
                                    <td style="font-size: 0.8rem; line-height: 1.6;">
                                        {{ $batch->tersedia_count }} Tersedia
                                        @if ($batch->dipinjam_count > 0)
                                            <br>{{ $batch->dipinjam_count }} Dipinjam
                                        @endif
                                        @if ($batch->maintenance_count > 0)
                                            <br>{{ $batch->maintenance_count }} Maintenance
                                        @endif
                                        @if ($batch->diganti_count > 0)
                                            <br><span style="color: #dc2626;">{{ $batch->diganti_count }} Diganti</span>
                                        @endif
                                    </td>
                                    <td><span class="price-display">Rp
                                            {{ number_format($batch->price, 0, ',', '.') }}</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('assets-inv.group-detail', ['batch_id' => $batch->id]) }}"
                                                class="btn btn-secondary">Detail ({{ $batch->assets_count }})</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" style="text-align: center; padding: 2rem;">
                                        <div style="color: var(--text-secondary);">
                                            <p style="font-size: 3rem; margin-bottom: 1rem;">📦</p>
                                            <p>Tidak ada data aset.</p>
                                            @can('create', App\Models\Asset::class)
                                                <a href="{{ route('assets-inv.create') }}" class="btn btn-primary"
                                                    style="margin-top: 1rem;">+ Tambah Aset Pertama</a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            @forelse($assets as $asset)
                                <tr>
                                    <td class="col-checkbox">
                                        <input type="checkbox" class="asset-checkbox" value="{{ $asset->id }}"
                                            data-type="asset" onchange="updateSelectedCount()">
                                    </td>
                                    <td><strong>{{ $asset->asset_id }}</strong></td>
                                    <td>
                                        <img src="{{ $asset->image_url }}" alt="{{ $asset->batch->name ?? 'Aset' }}"
                                            class="asset-image-preview"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td>{{ $asset->batch->name ?? '-' }}</td>
                                    <td>{{ $asset->batch->assetType->name ?? '-' }}</td>
                                    <td>{{ $asset->batch->brand ?? '-' }}</td>
                                    <td>
                                        @if ($asset->location_id)
                                            {{ $asset->location_ref->name ?? '' }}
                                            @if ($asset->location_detail)
                                                - {{ $asset->location_detail }}
                                            @endif
                                        @else
                                            {{ $asset->location }}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $conditionClass = match ($asset->condition) {
                                                'Baik' => 'available',
                                                'Rusak Ringan' => 'borrowed',
                                                'Rusak Berat' => 'maintenance',
                                                default => 'pending',
                                            };
                                        @endphp
                                        <span class="status-badge {{ $conditionClass }}">
                                            {{ $asset->condition }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match ($asset->status) {
                                                'Tersedia' => 'available',
                                                'Dipinjam' => 'borrowed',
                                                'Maintenance' => 'maintenance',
                                                'Diganti' => 'rejected',
                                                default => 'pending',
                                            };
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ $asset->status }}
                                        </span>
                                    </td>
                                    <td><span class="price-display">Rp
                                            {{ number_format($asset->batch->price ?? 0, 0, ',', '.') }}</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="showAssetDetail({{ $asset->id }})"
                                                class="btn btn-secondary">Detail</button>
                                            @can('update', $asset)
                                                <a href="{{ route('assets-inv.edit', $asset) }}"
                                                    class="btn btn-primary">Edit</a>
                                            @endcan
                                            @can('delete', $asset)
                                                <form action="{{ route('assets-inv.destroy', $asset) }}" method="POST"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" style="text-align: center; padding: 2rem;">
                                        <div style="color: var(--text-secondary);">
                                            <p style="font-size: 3rem; margin-bottom: 1rem;">📦</p>
                                            <p>Tidak ada data aset.</p>
                                            @can('create', App\Models\Asset::class)
                                                <a href="{{ route('assets-inv.create') }}" class="btn btn-primary"
                                                    style="margin-top: 1rem;">+ Tambah Aset Pertama</a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    @endif
                </tbody>
            </table>
        </div>

        <div style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="color: var(--text-secondary); font-size: 0.875rem;">
                @if ($view === 'semua')
                    Total: <strong>{{ $combined->count() }}</strong> grup aset (fisik + non-fisik)
                @elseif($view === 'non-fisik')
                    Total: <strong>{{ $groupedNonFisik->count() }}</strong> grup aset non-fisik
                @else
                    {{-- view fisik --}}
                    @if ($isGrouped)
                        Total: <strong>{{ $grouped->sum('assets_count') }}</strong> unit aset ({{ $grouped->count() }}
                        jenis barang)
                    @else
                        Total: <strong>{{ $assets->count() }}</strong> aset
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- easy touch button -->
    <div class="easy-touch-button snap-right" id="easyTouchBtn" onclick="toggleEasyTouchMenu(event)">
        <img src="{{ asset('assets/logo-stti.png') }}" alt="Quick Menu">
    </div>

    <div class="easy-touch-menu" id="easyTouchMenu">
        {{-- HANYA TAMPILKAN TOMBOL BULK DI VIEW FISIK --}}
        @if ($view === 'fisik')
            <div class="menu-item" onclick="toggleBulkMode()" title="Mode Pilih">
                <span class="icon">☑️</span>
            </div>
        @endif
        @can('create', App\Models\Asset::class)
            <div class="menu-item" onclick="window.location.href='{{ route('assets-inv.create') }}'" title="Tambah Aset">
                <span class="icon">➕</span>
            </div>
        @endcan
        <div class="menu-item" onclick="exportAssets()" title="Export">
            <span class="icon">📥</span>
        </div>
        <div class="menu-item" onclick="scrollToTop()" title="Scroll ke Atas">
            <span class="icon">⬆️</span>
        </div>
    </div>

    <div id="assetDetailModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">Detail Aset</h3>
                <button class="close-modal" onclick="closeAssetModal()">×</button>
            </div>
            <div class="modal-body" id="assetDetailContent">
                <p style="text-align: center; padding: 2rem;">Loading...</p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* CSS Bulk Action */
        .bulk-action-bar {
            display: none;
            padding: 1rem 2rem;
            background-color: var(--primary-color);
            color: white;
            align-items: center;
            justify-content: space-between;
            animation: fadeIn 0.3s;
            margin: 0 2rem 1rem 2rem;
            border-radius: 8px;
        }

        .bulk-action-bar.active {
            display: flex;
        }

        .bulk-action-bar p {
            margin: 0;
            font-weight: 600;
        }

        .bulk-action-bar .btn {
            background-color: white;
            color: var(--primary-color);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .bulk-action-bar .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .bulk-action-bar .btn-success {
            background-color: #10b981;
            color: white;
        }

        .bulk-action-bar .btn-secondary {
            background-color: #64748b;
            color: white;
        }

        .bulk-action-bar .btn:hover {
            opacity: 0.8;
        }

        /* Sembunyikan checkbox secara default */
        .data-table .col-checkbox {
            width: 40px;
            display: none;
        }

        .data-table.bulk-mode-active .col-checkbox {
            display: table-cell;
        }

        .page-container {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .table-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        @media screen and (max-width: 1024px) {
            .table-actions {
                flex-direction: column;
                width: 100%;
            }

            .table-actions .search-box,
            .table-actions .filter-select,
            .table-actions .btn {
                width: 100%;
            }
        }

        @media screen and (max-width: 768px) {
            .table-header {
                flex-direction: column;
                gap: 1rem;
            }

            .table-title {
                text-align: center;
            }
        }

        /* 🔥 FIX TAMPILAN HP (TABEL SCROLL & SPASI BAWAH) 🔥 */
        .table-wrapper {
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            min-width: 900px !important;
        }

        .content-area,
        .data-table-container {
            padding-bottom: 100px !important;
        }

        /* 🔥 CSS UNTUK DRAG & DROP EASY TOUCH 🔥 */
        .easy-touch-button {
            position: fixed;
            width: 60px;
            height: 60px;
            background-color: var(--primary-color);
            border-radius: 50%;
            box-shadow: var(--shadow-lg);
            display: flex !important;
            align-items: center;
            justify-content: center;
            cursor: grab;
            z-index: 99999;
            transition: opacity 0.3s ease;
            user-select: none;
            -webkit-user-drag: none;
            /* Default posisi */
            right: 30px;
            bottom: 90px;
        }

        .easy-touch-button.faded {
            opacity: 0.5 !important;
        }

        .easy-touch-button:hover {
            opacity: 1 !important;
        }

        .easy-touch-button img {
            width: 50%;
            height: 50%;
            object-fit: contain;
            pointer-events: none;
        }

        .easy-touch-button.snap-left {
            left: 20px;
            right: auto;
        }

        .easy-touch-button.snap-right {
            right: 20px;
            left: auto;
        }

        .easy-touch-menu {
            z-index: 99998;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let bulkModeActive = false;
        let selectedAssets = [];
        let isDragging = false;
        let hasDragged = false;
        let startX, startY, initialLeft, initialTop;
        let fadeTimeout;

        document.addEventListener('DOMContentLoaded', function() {
            const easyTouchBtn = document.getElementById('easyTouchBtn');

            // --- LOGIKA DRAG & DROP EASY TOUCH ---
            if (easyTouchBtn) {
                easyTouchBtn.addEventListener('pointerdown', (e) => {
                    isDragging = true;
                    hasDragged = false;
                    easyTouchBtn.style.cursor = 'grabbing';
                    easyTouchBtn.classList.remove('faded');
                    clearTimeout(fadeTimeout);

                    const rect = easyTouchBtn.getBoundingClientRect();
                    startX = e.clientX;
                    startY = e.clientY;
                    initialLeft = rect.left;
                    initialTop = rect.top;

                    // Hapus kelas snap agar bebas digeser
                    easyTouchBtn.classList.remove('snap-left', 'snap-right');
                    easyTouchBtn.style.right = 'auto';
                    easyTouchBtn.style.bottom = 'auto';
                    easyTouchBtn.style.left = `${initialLeft}px`;
                    easyTouchBtn.style.top = `${initialTop}px`;

                    easyTouchBtn.setPointerCapture(e.pointerId);
                });

                easyTouchBtn.addEventListener('pointermove', (e) => {
                    if (!isDragging) return;

                    const dx = e.clientX - startX;
                    const dy = e.clientY - startY;

                    // Toleransi 5px untuk mendeteksi apakah ini drag atau klik biasa
                    if (Math.abs(dx) > 5 || Math.abs(dy) > 5) hasDragged = true;
                    if (!hasDragged) return;

                    let newLeft = initialLeft + dx;
                    let newTop = initialTop + dy;

                    // Batasi agar tidak keluar layar
                    const maxLeft = window.innerWidth - easyTouchBtn.offsetWidth;
                    const maxTop = window.innerHeight - easyTouchBtn.offsetHeight;
                    newLeft = Math.max(0, Math.min(newLeft, maxLeft));
                    newTop = Math.max(0, Math.min(newTop, maxTop));

                    easyTouchBtn.style.left = `${newLeft}px`;
                    easyTouchBtn.style.top = `${newTop}px`;
                });

                easyTouchBtn.addEventListener('pointerup', (e) => {
                    if (!isDragging) return;
                    isDragging = false;
                    easyTouchBtn.style.cursor = 'grab';
                    easyTouchBtn.releasePointerCapture(e.pointerId);

                    if (!hasDragged) return;

                    // --- LOGIKA SNAPPING KE KIRI / KANAN ---
                    const btnCenter = easyTouchBtn.getBoundingClientRect().left + (easyTouchBtn
                        .offsetWidth / 2);
                    const screenCenter = window.innerWidth / 2;

                    if (btnCenter < screenCenter) {
                        easyTouchBtn.style.left = '20px';
                        easyTouchBtn.style.right = 'auto';
                        easyTouchBtn.classList.add('snap-left');
                    } else {
                        easyTouchBtn.style.left = 'auto';
                        easyTouchBtn.style.right = '20px';
                        easyTouchBtn.classList.add('snap-right');
                    }

                    // --- LOGIKA AUTO-FADE 3 DETIK ---
                    fadeTimeout = setTimeout(() => {
                        easyTouchBtn.classList.add('faded');
                    }, 3000);
                });
            }

            // --- SISA FILTER (Search & Dropdown) ---
            const searchInput = document.getElementById('assetSearch');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        applyFilters();
                    }, 500);
                });
            }
            if (document.getElementById('filterUnit')) document.getElementById('filterUnit').addEventListener(
                'change', applyFilters);
            if (document.getElementById('filterStatus')) document.getElementById('filterStatus').addEventListener(
                'change', applyFilters);
            if (document.getElementById('filterType')) document.getElementById('filterType').addEventListener(
                'change', applyFilters);
        });

        // 🔥 FUNGSI BUKA/TUTUP MENU EASY TOUCH 🔥
        function toggleEasyTouchMenu(event) {
            if (hasDragged) {
                hasDragged = false;
                return;
            }
            if (event) event.stopPropagation();
            const menu = document.getElementById('easyTouchMenu');
            if (menu) menu.classList.toggle('active');
        }

        // 🔥 TUTUP MENU JIKA KLIK DI LUAR AREA 🔥
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('easyTouchMenu');
            const btn = document.getElementById('easyTouchBtn');
            if (menu && menu.classList.contains('active')) {
                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                    menu.classList.remove('active');
                } else if (e.target.closest('.menu-item')) {
                    menu.classList.remove('active');
                }
            }
        });

        function applyFilters() {
            const search = document.getElementById('assetSearch').value;
            const status = document.getElementById('filterStatus').value;
            const type = document.getElementById('filterType').value;
            const unitId = document.getElementById('filterUnit').value;
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (status) params.append('status', status);
            if (type) params.append('type', type);
            if (unitId) params.append('unit_id', unitId);
            const view = '{{ $view }}';
            if (view) params.append('view', view);
            window.location.href = '{{ route('assets-inv.index') }}?' + params.toString();
        }

        function toggleBulkMode() {
            bulkModeActive = !bulkModeActive;
            const table = document.getElementById('assetsTable');
            const bulkBar = document.getElementById('bulkActionBar');
            if (!table || !bulkBar) return;

            if (bulkModeActive) {
                table.classList.add('bulk-mode-active');
                bulkBar.classList.add('active');
            } else {
                table.classList.remove('bulk-mode-active');
                bulkBar.classList.remove('active');
                document.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = false);
                const selectAll = document.getElementById('selectAll');
                if (selectAll) selectAll.checked = false;
                updateSelectedCount();
            }
        }

        function toggleSelectAll(selectAllCheckbox) {
            document.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.asset-checkbox:checked');
            selectedAssets = Array.from(checkboxes).map(cb => cb.value);
            const countElement = document.getElementById('selectedCount');
            if (countElement) countElement.textContent = selectedAssets.length;
        }

        function bulkDelete() {
            const checkboxes = document.querySelectorAll('.asset-checkbox:checked');
            const ids = [];
            const types = [];
            checkboxes.forEach(cb => {
                ids.push(cb.value);
                types.push(cb.dataset.type || 'asset');
            });

            if (ids.length === 0) {
                showToast('Pilih minimal satu aset terlebih dahulu', 'error');
                return;
            }
            if (!confirm(`Apakah Anda yakin ingin menghapus ${ids.length} aset/grup terpilih?`)) return;

            showLoading();
            fetch('{{ route('assets.bulk-delete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ids: ids,
                        types: types
                    })
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Gagal menghapus aset', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    showToast('Terjadi kesalahan pada server', 'error');
                    console.error(error);
                });
        }

        // 🔥 LOGIKA EXPORT TERPILIH (FIX: Sertakan types) 🔥
        function exportSelected(format) {
            const checkboxes = document.querySelectorAll('.asset-checkbox:checked');
            const ids = [];
            const types = [];

            checkboxes.forEach(cb => {
                ids.push(cb.value);
                types.push(cb.dataset.type || 'asset');
            });

            if (ids.length === 0) {
                showToast('Pilih minimal satu aset terlebih dahulu', 'error');
                return;
            }

            const idsParam = ids.join(',');
            const typesParam = types.join(',');
            let baseUrl = format === 'pdf' ? '{{ route('reports.export.pdf') }}' : '{{ route('reports.export.excel') }}';

            window.location.href = `${baseUrl}?type=assets&ids=${idsParam}&types=${typesParam}`;
            showToast(`Menyiapkan file ${format.toUpperCase()}...`, 'info');
        }

        function exportAssets() {
            window.location.href = '{{ route('reports.export.excel') }}?type=assets';
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
@endpush
