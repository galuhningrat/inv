@extends('layouts.app')

@section('title', 'Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
    <div class="data-table-container">
        {{-- ===== HEADER + TOMBOL TAMBAH + FILTER ===== --}}
        <div class="table-header">
            <h3 class="table-title">Manajemen Pengguna</h3>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                {{-- FILTER UNIT --}}
                <select
                    onchange="window.location.href='{{ route('users.index') }}?unit_id='+this.value+'&per_page={{ request('per_page', 10) }}'"
                    class="form-control" style="width: auto; min-width: 320px;"> {{-- 🔥 Lebarkan sedikit agar teks tidak terpotong --}}
                    <option value="">Semua Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->category }}: {{ $unit->name }} {{-- 🔥 Ubah format di sini --}}
                        </option>
                    @endforeach
                </select>

                {{-- PAGINATION PER HALAMAN --}}
                <select
                    onchange="window.location.href='{{ route('users.index') }}?per_page='+this.value+'&unit_id={{ request('unit_id', '') }}'"
                    class="form-control" style="width: auto;">
                    @foreach ([10, 15, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                            {{ $n }} / halaman
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
            </div>
        </div>

        {{-- ===== TABEL ===== --}}
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Level</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td><strong>{{ $user->id }}</strong></td>
                            <td>
                                {{-- 🔥 Perbaikan URL Gambar Avatar --}}
                                <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('assets/admin.png') }}"
                                    alt="{{ $user->name }}"
                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td><span class="status-badge available">{{ $user->level }}</span></td>
                            <td>{{ $user->unit->name ?? '-' }}</td>
                            <td>
                                <span class="status-badge {{ $user->status === 'Aktif' ? 'available' : 'maintenance' }}">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary">Edit</a>
                                    @if ($user->id !== 1)
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                            style="display: inline;"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                <div style="color: var(--text-secondary);">
                                    <p style="font-size: 2rem; margin-bottom: 0.5rem;">👤</p>
                                    <p>Tidak ada data pengguna.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ===== 🔥 CUSTOM PAGINATION & INFO 🔥 ===== --}}
        @if ($users->total() > 0)
            <div
                style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-top: 1px solid var(--border-color);">

                {{-- Info Teks --}}
                <div style="color: var(--text-secondary); font-size: 0.875rem;">
                    Menampilkan <strong>{{ $users->firstItem() }}</strong> - <strong>{{ $users->lastItem() }}</strong>
                    dari <strong>{{ $users->total() }}</strong> pengguna
                </div>

                {{-- Tombol Navigasi --}}
                @if ($users->hasPages())
                    <div style="display: flex; gap: 0.25rem; align-items: center;">

                        {{-- Previous --}}
                        @if ($users->onFirstPage())
                            <span
                                style="padding: 0.4rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--light-bg); color: #94a3b8; cursor: not-allowed; font-size: 0.85rem;">
                                &laquo; Prev
                            </span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}"
                                style="padding: 0.4rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; background: white; color: var(--text-primary); text-decoration: none; font-size: 0.85rem; transition: all 0.2s;">
                                &laquo; Prev
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                            @if ($page == $users->currentPage())
                                <span
                                    style="padding: 0.4rem 0.8rem; border: 1px solid var(--primary-color); border-radius: 6px; background: var(--primary-color); color: white; font-weight: 600; font-size: 0.85rem;">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                    style="padding: 0.4rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; background: white; color: var(--text-primary); text-decoration: none; font-size: 0.85rem; transition: all 0.2s;">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}"
                                style="padding: 0.4rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; background: white; color: var(--text-primary); text-decoration: none; font-size: 0.85rem; transition: all 0.2s;">
                                Next &raquo;
                            </a>
                        @else
                            <span
                                style="padding: 0.4rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--light-bg); color: #94a3b8; cursor: not-allowed; font-size: 0.85rem;">
                                Next &raquo;
                            </span>
                        @endif

                    </div>
                @endif
            </div>
        @endif

    </div>
@endsection
