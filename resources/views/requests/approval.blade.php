@extends('layouts.app')

@section('title', 'Approval Pengajuan')
@section('page-title', 'Approval Pengajuan: ' . $assetRequest->request_id)

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Approval Pengajuan: {{ $assetRequest->request_id }}</h3>
            <div>
                <span class="status-badge borrowed">Menunggu Persetujuan</span>
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
            </div>
        </div>

        <div style="padding: 2rem;">
            {{-- Informasi Pengajuan --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <p><strong>Pengaju:</strong> {{ $assetRequest->requester->name }}</p>
                    <p><strong>Unit:</strong> {{ $assetRequest->unit->name ?? '-' }}</p>
                    <p><strong>Periode:</strong>
                        {{ $assetRequest->period_month ?? now()->month }}/{{ $assetRequest->period_year ?? now()->year }}
                    </p>
                </div>
                <div>
                    <p><strong>Total Estimasi:</strong> <span class="price-display">Rp
                            {{ number_format($assetRequest->total_estimated_price, 0, ',', '.') }}</span></p>
                    <p><strong>Total Disetujui:</strong> <span class="price-display" style="color: var(--success-color);">Rp
                            {{ number_format($assetRequest->approved_total, 0, ',', '.') }}</span></p>
                </div>
            </div>

            {{-- Ringkasan Status --}}
            <div style="display: flex; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
                @php $summary = $assetRequest->approval_summary; @endphp
                <span class="status-badge pending">⏳ Menunggu: {{ $summary['pending'] }}</span>
                <span class="status-badge available">✅ Disetujui: {{ $summary['approved'] }}</span>
                <span class="status-badge maintenance">❌ Ditolak: {{ $summary['rejected'] }}</span>
                <span class="status-badge borrowed">⏳ Ditangguhkan: {{ $summary['deferred'] }}</span>
            </div>

            {{-- ===== Tabel Item — DI-GROUP PER UNIT PENGAJU ===== --}}
            @php
                // Fase 2: grouping by unit, sorted by unit_id supaya urutan konsisten
                // antar refresh (bukan mengikuti urutan kemunculan item).
                $itemsByUnit = $assetRequest->items
                    ->groupBy(function ($item) use ($assetRequest) {
                        return $item->unit_id ?? ($assetRequest->unit_id ?? 0);
                    })
                    ->sortKeys();
            @endphp

            @forelse ($itemsByUnit as $unitId => $items)
                @php
                    $unitName = $units[$unitId] ?? ($assetRequest->unit->name ?? 'Unit Tidak Diketahui');
                    $unitTotal = $items->sum(fn($i) => $i->subtotal);
                    $unitApproved = $items->where('approval_status', 'approved')->sum(fn($i) => $i->subtotal);
                @endphp

                <div style="margin-bottom: 2rem;">
                    {{-- Header Unit --}}
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; background: var(--light-bg); padding: 0.75rem 1.25rem; border-radius: 8px 8px 0 0; border: 1px solid var(--border-color); border-bottom: none;">
                        <h4 style="margin: 0; font-size: 1rem;">
                            🏢 {{ $unitName }}
                            <span style="font-weight: normal; color: var(--text-secondary); font-size: 0.85rem;">
                                ({{ $items->count() }} item — Est. Rp {{ number_format($unitTotal, 0, ',', '.') }})
                            </span>
                        </h4>
                        @if ($unitApproved > 0)
                            <span class="status-badge available" style="font-size: 0.75rem;">
                                Disetujui: Rp {{ number_format($unitApproved, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <div class="table-wrapper" style="border-radius: 0 0 8px 8px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Barang</th>
                                    <th>Tipe</th>
                                    <th>Prioritas</th>
                                    <th>Jumlah</th>
                                    <th>Est. Harga</th>
                                    <th>Subtotal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $item->item_name }}
                                            @if ($item->specification)
                                                <br><small
                                                    style="color: var(--text-secondary);">{{ $item->specification }}</small>
                                            @endif
                                            @if ($item->reason)
                                                <br><small style="color: var(--text-secondary); font-style: italic;">
                                                    Alasan: {{ $item->reason }}</small>
                                            @endif
                                            @if ($item->alasan_pengajuan && $item->alasan_pengajuan !== 'Pengadaan Baru')
                                                <br><small style="color: #f59e0b;">{{ $item->alasan_pengajuan }}</small>
                                            @endif
                                            @if ($item->rolled_from_item_id)
                                                <br><small style="color: #f59e0b;">
                                                    🔄 Rollover dari item sebelumnya
                                                    @if ($item->rolledFrom && $item->rolledFrom->approval_notes)
                                                        — Alasan ditangguhkan: {{ $item->rolledFrom->approval_notes }}
                                                    @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="status-badge {{ $item->item_type === 'Fisik' ? 'available' : 'borrowed' }}">{{ $item->item_type }}</span>
                                            @if ($item->item_type === 'Non-Fisik' && $item->sifat_barang !== 'Jasa' && $item->category)
                                                <br><small style="color: var(--text-secondary);">
                                                    {{ \App\Models\IntangibleAsset::CATEGORIES[$item->category] ?? $item->category }}
                                                </small>
                                            @elseif ($item->item_type === 'Fisik' && $item->sifat_barang === 'Habis Pakai' && $item->category)
                                                <br><small style="color: var(--text-secondary);">
                                                    {{ \App\Models\AssetRequestItem::HABIS_PAKAI_CATEGORIES[$item->category] ?? $item->category }}
                                                </small>
                                            @endif
                                            @if ($item->sifat_barang && $item->sifat_barang !== 'Tidak Habis Pakai')
                                                <br><small style="color: #f59e0b;">{{ $item->sifat_barang }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $prioClass = match ($item->priority ?? 'Normal') {
                                                    'Sangat Mendesak' => 'maintenance',
                                                    'Mendesak' => 'borrowed',
                                                    default => 'available',
                                                };
                                            @endphp
                                            <span class="status-badge {{ $prioClass }}" style="font-size: 0.75rem;">
                                                {{ $item->priority ?? 'Normal' }}
                                            </span>
                                        </td>
                                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                        <td>Rp {{ number_format($item->estimated_price_per_unit ?? 0, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="status-badge {{ $item->approval_badge_class }}">
                                                {{ $item->approval_status_label }}
                                            </span>
                                            @if ($item->approval_notes)
                                                <br><small
                                                    style="color: var(--text-secondary);">{{ $item->approval_notes }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->approval_status === 'pending')
                                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                                    <form
                                                        action="{{ route('requests.approve-item', [$assetRequest, $item]) }}"
                                                        method="POST" style="display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="action" value="approved">
                                                        <button type="submit" class="btn btn-success btn-sm"
                                                            title="Setujui">✅</button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="showActionModal({{ $item->id }}, 'rejected')"
                                                        title="Tolak">❌</button>
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                        onclick="showActionModal({{ $item->id }}, 'deferred')"
                                                        title="Tangguhkan">⏳</button>
                                                </div>
                                            @else
                                                <span style="color: var(--text-secondary); font-size: 0.8rem;">Sudah
                                                    diproses</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    Tidak ada item pada pengajuan ini.
                </p>
            @endforelse

            {{-- ============ FASE 3.5: APPROVAL JASA ============ --}}
            @if ($assetRequest->serviceItems->isNotEmpty())
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem; border-bottom: 2px solid #7c3aed; padding-bottom: 0.5rem;">
                        🛠️ Item Jasa</h4>

                    @php
                        $serviceItemsByUnit = $assetRequest->serviceItems
                            ->groupBy(function ($item) use ($assetRequest) {
                                return $item->unit_id ?? ($assetRequest->unit_id ?? 0);
                            })
                            ->sortKeys();
                    @endphp

                    @foreach ($serviceItemsByUnit as $unitId => $items)
                        @php
                            $unitName = $units[$unitId] ?? ($assetRequest->unit->name ?? 'Unit Tidak Diketahui');
                            $unitTotal = $items->sum(fn($i) => $i->subtotal);
                            $unitApproved = $items->where('approval_status', 'approved')->sum(fn($i) => $i->subtotal);
                        @endphp

                        <div style="margin-bottom: 2rem;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; background: #f5f3ff; padding: 0.75rem 1.25rem; border-radius: 8px 8px 0 0; border: 1px solid #ddd6fe; border-bottom: none;">
                                <h4 style="margin: 0; font-size: 1rem;">
                                    🏢 {{ $unitName }}
                                    <span style="font-weight: normal; color: var(--text-secondary); font-size: 0.85rem;">
                                        ({{ $items->count() }} item jasa — Est. Rp
                                        {{ number_format($unitTotal, 0, ',', '.') }})
                                    </span>
                                </h4>
                                @if ($unitApproved > 0)
                                    <span class="status-badge available" style="font-size: 0.75rem;">
                                        Disetujui: Rp {{ number_format($unitApproved, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            <div class="table-wrapper" style="border-radius: 0 0 8px 8px;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Jasa</th>
                                            <th>Kategori</th>
                                            <th>Detail</th>
                                            <th>Prioritas</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ $item->item_name }}
                                                    @if ($item->specification)
                                                        <br><small
                                                            style="color: var(--text-secondary);">{{ $item->specification }}</small>
                                                    @endif
                                                    @if ($item->reason)
                                                        <br><small
                                                            style="color: var(--text-secondary); font-style: italic;">
                                                            Alasan: {{ $item->reason }}</small>
                                                    @endif
                                                    @if ($item->rolled_from_item_id)
                                                        <br><small style="color: #f59e0b;">
                                                            🔄 Rollover dari item sebelumnya
                                                            @if ($item->rolledFrom && $item->rolledFrom->approval_notes)
                                                                — Alasan ditangguhkan:
                                                                {{ $item->rolledFrom->approval_notes }}
                                                            @endif
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="status-badge borrowed" style="font-size: 0.7rem;">
                                                        {{ \App\Models\ServiceRequestItem::CATEGORIES[$item->service_category] ?? $item->service_category }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php $sd = $item->service_data ?? []; @endphp
                                                    @if (!empty($sd['asset_id']))
                                                        @php $asset = \App\Models\Asset::find($sd['asset_id']); @endphp
                                                        <small><strong>Aset:</strong> {{ $asset->asset_id ?? '-' }} —
                                                            {{ $asset->name ?? '-' }}</small><br>
                                                    @endif
                                                    @if (!empty($sd['maintenance_type']))
                                                        <small><strong>Jenis:</strong>
                                                            {{ $sd['maintenance_type'] }}</small><br>
                                                    @endif
                                                    @if (!empty($sd['location']))
                                                        <small><strong>Lokasi:</strong> {{ $sd['location'] }}</small><br>
                                                    @endif
                                                    @if (!empty($sd['start_date']) && !empty($sd['end_date']))
                                                        <small><strong>Waktu:</strong> {{ $sd['start_date'] }} s/d
                                                            {{ $sd['end_date'] }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $prioClass = match ($item->priority ?? 'Normal') {
                                                            'Sangat Mendesak' => 'maintenance',
                                                            'Mendesak' => 'borrowed',
                                                            default => 'available',
                                                        };
                                                    @endphp
                                                    <span class="status-badge {{ $prioClass }}"
                                                        style="font-size: 0.75rem;">
                                                        {{ $item->priority ?? 'Normal' }}
                                                    </span>
                                                </td>
                                                <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="status-badge {{ $item->approval_badge_class }}">
                                                        {{ $item->approval_status_label }}
                                                    </span>
                                                    @if ($item->approval_notes)
                                                        <br><small
                                                            style="color: var(--text-secondary);">{{ $item->approval_notes }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($item->approval_status === 'pending')
                                                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                                            <form
                                                                action="{{ route('requests.approve-service-item', [$assetRequest, $item]) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                <input type="hidden" name="action" value="approved">
                                                                <button type="submit" class="btn btn-success btn-sm"
                                                                    title="Setujui">✅</button>
                                                            </form>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                onclick="showActionModal({{ $item->id }}, 'rejected', 'service')"
                                                                title="Tolak">❌</button>
                                                            <button type="button" class="btn btn-warning btn-sm"
                                                                onclick="showActionModal({{ $item->id }}, 'deferred', 'service')"
                                                                title="Tangguhkan">⏳</button>
                                                        </div>
                                                    @else
                                                        <span style="color: var(--text-secondary); font-size: 0.8rem;">Sudah
                                                            diproses</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Footer: total keseluruhan lintas unit --}}
            <div
                style="display: flex; justify-content: flex-end; gap: 2rem; margin-top: 1rem; padding: 1rem 1.5rem; background: var(--light-bg); border-radius: 8px;">
                <div style="text-align: right;">
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Total Estimasi Keseluruhan</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">
                        Rp {{ number_format($assetRequest->total_estimated_price, 0, ',', '.') }}
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Total Disetujui</div>
                    <div style="font-weight: 700; font-size: 1.1rem; color: var(--success-color);">
                        Rp {{ number_format($assetRequest->approved_total, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
            </div>
        </div>
    </div>

    {{-- Modal untuk Action dengan Alasan --}}
    <div id="actionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="actionModalTitle">Konfirmasi Tindakan</h3>
                <button class="close-modal" onclick="closeActionModal()">×</button>
            </div>
            <form id="actionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" id="actionType">
                    <div class="form-group">
                        <label for="approval_notes_modal">Alasan <span style="color: red;">*</span></label>
                        <textarea id="approval_notes_modal" name="approval_notes" class="form-control" rows="3"
                            placeholder="Masukkan alasan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeActionModal()">Batal</button>
                    <button type="submit" class="btn btn-danger" id="actionSubmitBtn">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            min-width: 36px;
        }

        .btn-warning {
            background: #f59e0b;
            color: #fff;
        }

        .btn-warning:hover {
            background: #d97706;
        }

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

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }
    </style>
@endsection

@push('scripts')
    <script>
        let currentItemId = null;
        let currentAction = null;
        let currentItemType = 'asset';

        function showActionModal(itemId, action, itemType = 'asset') {
            currentItemId = itemId;
            currentAction = action;
            currentItemType = itemType;

            const modal = document.getElementById('actionModal');
            const title = document.getElementById('actionModalTitle');
            const submitBtn = document.getElementById('actionSubmitBtn');
            const form = document.getElementById('actionForm');
            const actionInput = document.getElementById('actionType');

            const labels = {
                'rejected': {
                    title: itemType === 'service' ? 'Tolak Item Jasa' : 'Tolak Item',
                    btn: 'Tolak',
                    color: 'btn-danger'
                },
                'deferred': {
                    title: itemType === 'service' ? 'Tangguhkan Item Jasa' : 'Tangguhkan Item',
                    btn: 'Tangguhkan',
                    color: 'btn-warning'
                }
            };

            const label = labels[action] || labels['rejected'];
            title.textContent = label.title;
            submitBtn.textContent = label.btn;
            submitBtn.className = 'btn ' + label.color;
            actionInput.value = action;

            // Route berbeda untuk aset vs jasa
            const assetRequestId = {{ $assetRequest->id }};
            if (itemType === 'service') {
                form.action = `/requests/${assetRequestId}/service-items/${itemId}/approve`;
            } else {
                form.action = `/requests/${assetRequestId}/items/${itemId}/approve`;
            }

            modal.style.display = 'flex';
            document.getElementById('approval_notes_modal').value = '';
        }

        function closeActionModal() {
            document.getElementById('actionModal').style.display = 'none';
        }
    </script>
@endpush
