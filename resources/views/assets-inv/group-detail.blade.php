@extends('layouts.app')

@section('title', 'Detail Grup Aset')
@section('page-title', 'Detail Grup: ' . $groupName)

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">{{ $groupName }} ({{ $groupBrand }}) — {{ $units->count() }} Unit</h3>
            <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Aset</th>
                        <th>Gambar</th>
                        <th>Nomor Seri</th>
                        <th>Lokasi</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td><strong>{{ $unit->asset_id }}</strong></td>
                            <td>
                                <img src="{{ $unit->image_url }}" alt="{{ $unit->name }}"
                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                            </td>
                            <td>{{ $unit->serial_number }}</td>
                            <td>
                                @if ($unit->location_id)
                                    {{ $unit->location_ref->name ?? '' }}
                                    @if ($unit->location_detail)
                                        - {{ $unit->location_detail }}
                                    @endif
                                @else
                                    {{ $unit->location }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $conditionClass = match ($unit->condition) {
                                        'Baik' => 'available',
                                        'Rusak Ringan' => 'borrowed',
                                        'Rusak Berat' => 'maintenance',
                                        default => 'pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $conditionClass }}">{{ $unit->condition }}</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($unit->status) {
                                        'Tersedia' => 'available',
                                        'Dipinjam' => 'borrowed',
                                        'Maintenance' => 'maintenance',
                                        'Diganti' => 'rejected',
                                        default => 'pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $unit->status }}</span>
                            </td>
                            <td>
                                <div class="action-buttons" style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                    {{-- Tombol Detail --}}
                                    <a href="{{ route('assets-inv.show', ['asset' => $unit->id, 'group_name' => $groupName, 'group_type_id' => $unit->asset_type_id, 'group_brand' => $groupBrand]) }}"
                                        class="btn btn-secondary btn-sm" title="Lihat Detail">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        Detail
                                    </a>

                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('assets-inv.edit', ['asset' => $unit->id, 'group_name' => $groupName, 'group_type_id' => $unit->asset_type_id, 'group_brand' => $groupBrand]) }}"
                                        class="btn btn-primary btn-sm" title="Edit Aset">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                        Edit
                                    </a>

                                    {{-- Tombol Hapus --}}
                                    @can('delete', $unit)
                                        <form action="{{ route('assets-inv.destroy', $unit) }}" method="POST"
                                            style="display: inline;" onsubmit="return confirm('Hapus unit ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="group_name" value="{{ $groupName }}">
                                            <input type="hidden" name="group_type_id" value="{{ $unit->asset_type_id }}">
                                            <input type="hidden" name="group_brand" value="{{ $groupBrand }}">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Aset">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                    <line x1="10" y1="11" x2="10" y2="17" />
                                                    <line x1="14" y1="11" x2="14" y2="17" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

{{-- Tambahan CSS untuk btn-sm --}}
@push('styles')
    <style>
        .btn-sm {
            padding: 0.3rem 0.7rem;
            font-size: 0.8rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
            cursor: pointer;
            line-height: 1.4;
        }

        .btn-sm svg {
            flex-shrink: 0;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background: #6b7280;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #4b5563;
            color: #fff;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            color: #fff;
        }

        .btn-danger {
            background: #dc2626;
            color: #fff;
        }

        .btn-danger:hover {
            background: #b91c1c;
            color: #fff;
        }

        .action-buttons {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        @media (max-width: 480px) {
            .btn-sm {
                font-size: 0.7rem;
                padding: 0.2rem 0.5rem;
            }

            .btn-sm svg {
                width: 14px;
                height: 14px;
            }
        }
    </style>
@endpush
