@extends('layouts.app')

@section('title', 'QR Code')
@section('page-title', 'Manajemen QR Code')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Manajemen QR Code</h3>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" id="qrSearch" placeholder="Cari QR Code..."
                        value="{{ request('search') }}">
                </div>
                <a href="{{ route('qrcodes.export-pdf') }}" class="btn btn-primary">
                    📄 Export Semua PDF
                </a>
            </div>
        </div>

        <!-- QR Code Grid View -->
        <div style="padding: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                @forelse($qrCodes as $qr)
                    <div class="qr-card"
                        style="background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;">
                        <div style="padding: 1.5rem; text-align: center; background: var(--light-bg);">
                            <div id="qrcode-{{ $qr->id }}"
                                style="display: inline-block; padding: 10px; background: white; border-radius: 8px;"></div>
                        </div>
                        <div style="padding: 1.5rem;">
                            <h4 style="margin-bottom: 0.5rem; font-size: 1rem; color: var(--text-primary);">
                                {{ $qr->asset->name ?? 'N/A' }}</h4>
                            <p style="color: var(--text-secondary); font-size: 0.75rem; margin-bottom: 0.5rem;">
                                {{ $qr->qr_code_id }}</p>
                            <p
                                style="font-family: monospace; font-size: 0.7rem; color: var(--text-secondary); word-break: break-all; margin-bottom: 1rem;">
                                {{ $qr->code_content }}</p>

                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span class="status-badge {{ $qr->status === 'Aktif' ? 'available' : 'maintenance' }}">
                                    {{ $qr->status }}
                                </span>
                                <span style="font-size: 0.75rem; color: var(--text-secondary);">
                                    {{ $qr->created_at->format('d M Y') }}
                                </span>
                            </div>

                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="{{ route('qrcodes.show', $qr) }}" class="btn btn-secondary"
                                    style="flex: 1; font-size: 0.75rem; padding: 0.5rem;">Detail</a>
                                <a href="{{ route('qrcodes.print', $qr) }}" class="btn btn-primary"
                                    style="flex: 1; font-size: 0.75rem; padding: 0.5rem;" target="_blank">Cetak</a>
                                <form action="{{ route('qrcodes.toggle-status', $qr) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <button type="submit"
                                        class="btn {{ $qr->status === 'Aktif' ? 'btn-warning' : 'btn-success' }}"
                                        style="width: 100%; font-size: 0.75rem; padding: 0.5rem;">
                                        {{ $qr->status === 'Aktif' ? 'Off' : 'On' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <p style="font-size: 4rem; margin-bottom: 1rem;">📱</p>
                        <p style="color: var(--text-secondary);">Tidak ada QR Code yang tersedia.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Table View (Alternative) -->
        <div class="table-wrapper" style="display: none;" id="tableView">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID QR</th>
                        <th>Aset</th>
                        <th>Kode</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qrCodes as $qr)
                        <tr>
                            <td><strong>{{ $qr->qr_code_id }}</strong></td>
                            <td>{{ $qr->asset->name ?? 'N/A' }}</td>
                            <td>
                                <code
                                    style="background: var(--light-bg); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                                        {{ Str::limit($qr->code_content, 20) }}
                                    </code>
                            </td>
                            <td>
                                <span class="status-badge {{ $qr->status === 'Aktif' ? 'available' : 'maintenance' }}">
                                    {{ $qr->status }}
                                </span>
                            </td>
                            <td>{{ $qr->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <form action="{{ route('qrcodes.toggle-status', $qr) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        <button type="submit"
                                            class="btn {{ $qr->status === 'Aktif' ? 'btn-warning' : 'btn-success' }}">
                                            {{ $qr->status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('qrcodes.print', $qr) }}" class="btn btn-secondary"
                                        target="_blank">Cetak</a>
                                    <a href="{{ route('qrcodes.show', $qr) }}" class="btn btn-primary">Detail</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">Tidak ada data QR Code.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 1rem 2rem;">
            {{ $qrCodes->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Generate QR Codes for each card
            @foreach($qrCodes as $qr)
                new QRCode(document.getElementById("qrcode-{{ $qr->id }}"), {
                    text: "{{ route('asset.detail', ['qrcode' => $qr->code_content]) }}",
                    width: 100,
                    height: 100,
                    correctLevel: QRCode.CorrectLevel.M
                });
            @endforeach

        // Search functionality
        const searchInput = document.getElementById('qrSearch');
            let searchTimeout;

            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const params = new URLSearchParams();
                    if (this.value) params.append('search', this.value);
                    window.location.href = '{{ route('qrcodes.index') }}?' + params.toString();
                }, 500);
            });

            // Add hover effect to cards
            document.querySelectorAll('.qr-card').forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = 'var(--shadow-lg)';
                });
                card.addEventListener('mouseleave', function () {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'var(--shadow)';
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .qr-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
    </style>
@endpush