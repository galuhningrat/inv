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

    <div class="page-container">
        <div class="bulk-action-bar" id="bulkActionBar">
            <!-- existing content -->
        </div>
    </div>

    <div class="bulk-action-bar" id="bulkActionBar">
        <p><span id="selectedCount">0</span> aset dipilih</p>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn" onclick="cancelBulkMode()">Batal</button>
            <button class="btn btn-danger" onclick="bulkDelete()">Hapus Terpilih</button>
        </div>
    </div>

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
                            <th class="col-checkbox" style="width: 40px;"><input type="checkbox" id="selectAll"
                                    onchange="toggleSelectAll()"></th>
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
                            @forelse($grouped as $group)
                                <tr>
                                    <td class="col-checkbox"></td>
                                    <td><span style="color: var(--text-secondary);">—</span></td>
                                    <td>
                                        <img src="{{ $group->image_url }}" alt="{{ $group->name }}"
                                            class="asset-image-preview"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->assetType->name ?? '-' }}</td>
                                    <td>{{ $group->brand }}</td>
                                    <td>{{ $group->location }}</td>
                                    <td><span class="status-badge available">{{ $group->total_quantity }} unit</span></td>
                                    <td style="font-size: 0.8rem; line-height: 1.6;">
                                        {{ $group->tersedia_count }} Tersedia
                                        @if ($group->dipinjam_count > 0)
                                            <br>{{ $group->dipinjam_count }} Dipinjam
                                        @endif
                                        @if ($group->maintenance_count > 0)
                                            <br>{{ $group->maintenance_count }} Maintenance
                                        @endif
                                        @if ($group->diganti_count > 0)
                                            <br><span style="color: #dc2626;">{{ $group->diganti_count }} Diganti</span>
                                        @endif
                                    </td>
                                    <td><span class="price-display">Rp
                                            {{ number_format($group->price, 0, ',', '.') }}</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('assets-inv.group-detail', ['name' => $group->name, 'type_id' => $group->asset_type_id, 'brand' => $group->brand]) }}"
                                                class="btn btn-secondary">Detail ({{ $group->total_quantity }})</a>
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
                                            onchange="updateSelectedCount()">
                                    </td>
                                    <td><strong>{{ $asset->asset_id }}</strong></td>
                                    <td>
                                        <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}"
                                            class="asset-image-preview"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td>{{ $asset->name }}</td>
                                    <td>{{ $asset->assetType->name ?? '-' }}</td>
                                    <td>{{ $asset->brand }}</td>
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
                                            {{ number_format($asset->price, 0, ',', '.') }}</span></td>
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
                        Total: <strong>{{ $grouped->sum('total_quantity') }}</strong> unit aset ({{ $grouped->count() }}
                        jenis barang)
                    @else
                        Total: <strong>{{ $assets->count() }}</strong> aset
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- easy touch button -->
    <div class="easy-touch-button" id="easyTouchBtn">
        <img src="{{ asset('assets/logo-stti.png') }}" alt="Quick Menu">
    </div>
    <div class="easy-touch-menu" id="easyTouchMenu">
        <div class="menu-item" onclick="toggleBulkMode()" title="Mode Pilih">
            <span class="icon">☑️</span>
        </div>
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
    </style>
@endpush

@push('scripts')
    <script>
        let bulkModeActive = false;
        let selectedAssets = [];

        document.addEventListener('DOMContentLoaded', function() {
            const easyTouchBtn = document.getElementById('easyTouchBtn');
            const easyTouchMenu = document.getElementById('easyTouchMenu');

            if (window.innerWidth <= 1024) {
                easyTouchBtn.style.display = 'flex';
            }

            easyTouchBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                easyTouchMenu.classList.toggle('active');
            });

            const searchInput = document.getElementById('assetSearch');
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 500);
            });

            document.getElementById('filterUnit').addEventListener('change', applyFilters);
            document.getElementById('filterStatus').addEventListener('change', applyFilters);
            document.getElementById('filterType').addEventListener('change', applyFilters);

            document.addEventListener('click', function() {
                easyTouchMenu.classList.remove('active');
            });
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
            if (bulkModeActive) {
                table.classList.add('bulk-mode-active');
                bulkBar.classList.add('active');
            } else {
                table.classList.remove('bulk-mode-active');
                bulkBar.classList.remove('active');
                document.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = false);
                document.getElementById('selectAll').checked = false;
                updateSelectedCount();
            }
            document.getElementById('easyTouchMenu').classList.remove('active');
        }

        function cancelBulkMode() {
            toggleBulkMode();
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            document.querySelectorAll('.asset-checkbox').forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checked = document.querySelectorAll('.asset-checkbox:checked');
            selectedAssets = Array.from(checked).map(cb => cb.value);
            document.getElementById('selectedCount').textContent = selectedAssets.length;
        }

        function bulkDelete() {
            if (selectedAssets.length === 0) {
                showToast('Pilih minimal satu aset', 'error');
                return;
            }
            if (!confirm(`Apakah Anda yakin ingin menghapus ${selectedAssets.length} aset?`)) return;
            showLoading();
            fetch('{{ route('assets.bulk-delete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ids: selectedAssets
                    })
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast('Gagal menghapus aset', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    showToast('Terjadi kesalahan', 'error');
                });
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

        window.addEventListener('resize', function() {
            if (window.innerWidth <= 1024) {
                easyTouchBtn.style.display = 'flex';
            } else {
                easyTouchBtn.style.display = 'none';
                easyTouchMenu.classList.remove('active');
            }
        });
    </script>
@endpush
