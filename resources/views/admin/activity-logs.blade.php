@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas Sistem')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Riwayat Aktivitas Pengguna</h3>
        </div>

        <div style="padding: 1rem;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $log->created_at->format('d M Y, H:i') }} WIB
                                </td>
                                <td>
                                    <strong>{{ $log->user->name ?? 'Sistem' }}</strong><br>
                                    <small style="color: var(--text-secondary);">{{ $log->user->level ?? '-' }}</small>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match ($log->action) {
                                            'CREATE', 'IMPORT' => 'available', // Hijau
                                            'UPDATE' => 'borrowed', // Kuning
                                            'DELETE', 'BULK_DELETE' => 'maintenance', // Merah
                                            'EXPORT' => 'pending', // Orange
                                            default => 'pending',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td style="font-size: 0.85rem;">
                                    {{ $log->description }}
                                </td>
                                <td style="font-family: monospace; font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $log->ip_address }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem;">Belum ada aktivitas tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                {{ $logs->links() }} {{-- Pagination --}}
            </div>
        </div>
    </div>
@endsection
