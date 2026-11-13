@extends('layouts.app')

@section('title', 'Pengajuan')
@section('page-title', 'Pengajuan')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Riwayat Pengajuan</h3>
            @can('create', App\Models\AssetRequest::class)
                <button type="button" class="btn btn-primary" onclick="openNewRequestModal()">
                    + Tambah Pengajuan Baru
                </button>
            @endcan
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Pengajuan</th>
                        <th>Pengaju</th>
                        <th>Tipe</th>
                        <th>Rincian</th>
                        <th>Total Unit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        @php
                            $isService = ($request->request_type ?? 'aset') === 'jasa';
                        @endphp
                        <tr>
                            <td><strong>{{ $request->request_id }}</strong></td>
                            <td>{{ $request->requester->name }}</td>
                            <td>
                                @if ($isService)
                                    <span class="status-badge borrowed">🛠️ Jasa</span>
                                    @if ($request->service_category)
                                        <br><small
                                            style="color: var(--text-secondary);">{{ \App\Models\ServiceRequestItem::CATEGORIES[$request->service_category] ?? $request->service_category }}</small>
                                    @endif
                                @else
                                    <span class="status-badge available">📦 Aset</span>
                                @endif
                            </td>
                            <td>
                                @if ($isService)
                                    @foreach ($request->serviceItems as $item)
                                        {{ $item->item_name }} ({{ $item->quantity }} {{ $item->unit }})@if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                @else
                                    @foreach ($request->items as $item)
                                        {{ $item->item_name }} ({{ $item->quantity }} {{ $item->unit }})@if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                {{ $isService ? $request->serviceItems->sum('quantity') : $request->total_quantity }}
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($request->status) {
                                        'Disetujui', 'Dana Cair', 'Dikonfirmasi', 'Diterima', 'Selesai' => 'available',
                                        'Diverifikasi' => 'borrowed',
                                        'Ditolak' => 'maintenance',
                                        default => 'pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $request->status_label }}</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @can('verify', $request)
                                        <form action="{{ route('requests.verify', $request) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Verifikasi pengajuan ini?')">Verifikasi</button>
                                        </form>
                                    @endcan

                                    @can('approve', $request)
                                        <form action="{{ route('requests.approve', $request) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Setujui pengajuan ini?')">Setujui</button>
                                        </form>
                                    @endcan

                                    @can('reject', $request)
                                        <button type="button" class="btn btn-danger"
                                            onclick="showRejectModal({{ $request->id }})">Tolak</button>
                                    @endcan

                                    @can('disburse', $request)
                                        <form action="{{ route('requests.disburse', $request) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Konfirmasi dana sudah dicairkan?')">Konfirmasi Dana
                                                Cair</button>
                                        </form>
                                    @endcan

                                    @can('confirmPhysical', $request)
                                        <form action="{{ route('requests.confirm', $request) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Konfirmasi barang sudah diterima secara fisik?')">Konfirmasi
                                                Fisik</button>
                                        </form>
                                    @endcan

                                    @can('receive', $request)
                                        <a href="{{ route('requests.receive.form', $request) }}"
                                            class="btn btn-success">Registrasi Aset</a>
                                    @endcan

                                    @if (in_array(Auth::user()->level, ['Sarpras', 'Admin']) &&
                                            ($request->request_type ?? 'aset') === 'jasa' &&
                                            $request->status === 'Dana Cair')
                                        <a href="{{ route('requests.complete-service.form', $request) }}"
                                            class="btn btn-success">✅ Selesaikan Jasa</a>
                                    @endif

                                    @can('approve', $request)
                                        <a href="{{ route('requests.approval', $request) }}"
                                            class="btn btn-primary">Approval</a>
                                    @endcan

                                    @can('view', $request)
                                        <a href="{{ route('requests.show', $request) }}" class="btn btn-secondary">Detail</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">Tidak ada data pengajuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">
            {{ $requests->links() }}
        </div>
    </div>

    {{-- ===================== MODAL REJECT (existing) ===================== --}}
    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tolak Pengajuan</h3>
                <button class="btn-close" onclick="closeRejectModal()">×</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approval_notes">Catatan Penolakan</label>
                        <textarea id="approval_notes" name="approval_notes" class="form-control" rows="3"
                            placeholder="Berikan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL KATALOG LAYANAN (NEW) ===================== --}}
    <div id="catalogModal" class="catalog-modal" style="display: none;">
        <div class="catalog-modal-content">
            <div class="catalog-modal-header">
                <div>
                    <h3 id="catalogTitle" style="margin: 0;">Pilih Kategori Pengajuan</h3>
                    <p id="catalogSubtitle" style="margin: 0.25rem 0 0; font-size: 0.85rem; color: var(--text-secondary);">
                        Tentukan jenis layanan yang ingin Anda ajukan
                    </p>
                </div>
                <button class="btn-close" onclick="closeCatalogModal()">×</button>
            </div>

            {{-- ===== STAGE 1: Pilih Aset / Jasa ===== --}}
            <div id="catalogStage1" class="catalog-stage">
                <div class="catalog-grid">
                    <a href="{{ route('requests.create') }}" class="catalog-card">
                        <span class="catalog-icon">📦</span>
                        <span class="catalog-card-text">
                            <strong>Pengajuan Aset</strong>
                            <small>Barang fisik / digital baru, penggantian, atau pengisian kembali</small>
                        </span>
                    </a>

                    <button type="button" class="catalog-card" onclick="showServiceStage()">
                        <span class="catalog-icon">🛠️</span>
                        <span class="catalog-card-text">
                            <strong>Pengajuan Jasa</strong>
                            <small>Perbaikan, pemasangan, pelatihan, sewa, atau konsultasi</small>
                        </span>
                    </button>
                </div>
            </div>

            {{-- ===== STAGE 2: Sub-kategori Jasa ===== --}}
            <div id="catalogStage2" class="catalog-stage" style="display: none;">
                <button type="button" class="catalog-back" onclick="showTypeStage()">← Kembali ke pilihan
                    kategori</button>

                <div class="catalog-grid catalog-grid-sm">
                    <a href="{{ route('requests.create-service', ['category' => 'pemeliharaan']) }}"
                        class="catalog-card catalog-card-sm">
                        <span class="catalog-icon-sm">🔧</span>
                        <span class="catalog-card-text">
                            <strong>Jasa Pemeliharaan & Perbaikan</strong>
                            <small>Servis, perbaikan aset yang rusak</small>
                        </span>
                    </a>

                    <a href="{{ route('requests.create-service', ['category' => 'instalasi']) }}"
                        class="catalog-card catalog-card-sm">
                        <span class="catalog-icon-sm">⚙️</span>
                        <span class="catalog-card-text">
                            <strong>Jasa Instalasi & Pemasangan</strong>
                            <small>Pemasangan perangkat / sistem baru</small>
                        </span>
                    </a>

                    <a href="{{ route('requests.create-service', ['category' => 'pelatihan']) }}"
                        class="catalog-card catalog-card-sm">
                        <span class="catalog-icon-sm">🎓</span>
                        <span class="catalog-card-text">
                            <strong>Jasa Pelatihan & Sertifikasi</strong>
                            <small>Training, workshop, uji kompetensi</small>
                        </span>
                    </a>

                    <a href="{{ route('requests.create-service', ['category' => 'sewa']) }}"
                        class="catalog-card catalog-card-sm">
                        <span class="catalog-icon-sm">📅</span>
                        <span class="catalog-card-text">
                            <strong>Jasa Sewa</strong>
                            <small>Rental peralatan / ruangan / kendaraan</small>
                        </span>
                    </a>

                    <a href="{{ route('requests.create-service', ['category' => 'konsultasi']) }}"
                        class="catalog-card catalog-card-sm">
                        <span class="catalog-icon-sm">💼</span>
                        <span class="catalog-card-text">
                            <strong>Jasa Konsultasi</strong>
                            <small>Pendampingan & studi kelayakan</small>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ================= REJECT MODAL (existing) =================
        function showRejectModal(requestId) {
            document.getElementById('rejectForm').action = `/requests/${requestId}/reject`;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        // ================= CATALOG MODAL (new) =================
        function openNewRequestModal() {
            showTypeStage();
            document.getElementById('catalogModal').style.display = 'flex';
        }

        function closeCatalogModal() {
            document.getElementById('catalogModal').style.display = 'none';
        }

        function showServiceStage() {
            document.getElementById('catalogStage1').style.display = 'none';
            document.getElementById('catalogStage2').style.display = 'block';
            document.getElementById('catalogTitle').textContent = 'Pilih Jenis Layanan Jasa';
            document.getElementById('catalogSubtitle').textContent =
                'Setiap jenis jasa memiliki formulir yang berbeda';
        }

        function showTypeStage() {
            document.getElementById('catalogStage1').style.display = 'block';
            document.getElementById('catalogStage2').style.display = 'none';
            document.getElementById('catalogTitle').textContent = 'Pilih Kategori Pengajuan';
            document.getElementById('catalogSubtitle').textContent =
                'Tentukan jenis layanan yang ingin Anda ajukan';
        }

        // Klik backdrop = tutup. Klik konten di dalamnya = jangan tutup.
        document.getElementById('catalogModal').addEventListener('click', function(e) {
            if (e.target === this) closeCatalogModal();
        });

        // ESC = tutup modal katalog
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('catalogModal').style.display === 'flex') {
                closeCatalogModal();
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        /* ============ Modal dasar (dipakai juga oleh reject modal) ============ */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: var(--card-background);
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: var(--shadow);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            line-height: 1;
        }

        /* ============ Modal katalog layanan ============ */
        .catalog-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            justify-content: center;
            align-items: center;
            z-index: 1100;
            padding: 1rem;
            backdrop-filter: blur(2px);
        }

        .catalog-modal-content {
            background: var(--card-background, #fff);
            border-radius: 16px;
            width: 100%;
            max-width: 720px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        }

        .catalog-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1.5rem 1.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .catalog-modal-header h3 {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .catalog-stage {
            padding: 1.5rem 1.75rem 1.75rem;
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .catalog-grid-sm {
            grid-template-columns: 1fr 1fr;
        }

        /* Kartu utama (Aset / Jasa) */
        .catalog-card {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem;
            background: var(--card-background, #fff);
            border: 2px solid var(--border-color, #e5e7eb);
            border-radius: 14px;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
            font-family: inherit;
            font-size: inherit;
        }

        .catalog-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-color, #2563eb);
        }

        .catalog-icon {
            font-size: 2.25rem;
            flex-shrink: 0;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg, #f3f4f6);
            border-radius: 12px;
        }

        .catalog-card-text {
            flex: 1;
            min-width: 0;
        }

        .catalog-card-text strong {
            display: block;
            font-size: 0.98rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            margin-bottom: 0.2rem;
        }

        .catalog-card-text small {
            display: block;
            font-size: 0.78rem;
            color: var(--text-secondary, #6b7280);
            line-height: 1.4;
        }

        /* Kartu kecil (sub-jasa) */
        .catalog-card-sm {
            padding: 0.9rem 1rem;
            gap: 0.75rem;
            border-radius: 12px;
        }

        .catalog-card-sm .catalog-card-text strong {
            font-size: 0.88rem;
        }

        .catalog-icon-sm {
            font-size: 1.6rem;
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg, #f3f4f6);
            border-radius: 10px;
        }

        .catalog-back {
            background: none;
            border: none;
            color: var(--primary-color, #2563eb);
            cursor: pointer;
            font-size: 0.85rem;
            padding: 0;
            margin-bottom: 1rem;
            font-family: inherit;
        }

        .catalog-back:hover {
            text-decoration: underline;
        }

        /* Dark mode */
        body.dark-mode .catalog-modal-content {
            background: var(--dark-card, #1f2937);
        }

        body.dark-mode .catalog-card {
            background: var(--dark-card, #1f2937);
            border-color: var(--dark-border, #374151);
        }

        body.dark-mode .catalog-card:hover {
            border-color: var(--primary-color, #3b82f6);
        }

        body.dark-mode .catalog-icon,
        body.dark-mode .catalog-icon-sm {
            background: var(--dark-bg-secondary, #111827);
        }

        body.dark-mode .catalog-card-text strong {
            color: #f9fafb;
        }

        body.dark-mode .catalog-card-text small {
            color: #9ca3af;
        }

        /* Responsive */
        @media (max-width: 640px) {

            .catalog-grid,
            .catalog-grid-sm {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
