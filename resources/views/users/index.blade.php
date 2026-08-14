@extends('layouts.app')

@section('title', 'Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
    <div class="data-table-container">
        {{-- ===== HEADER + TOMBOL TAMBAH + FILTER ===== --}}
        <div class="table-header">
            <h3 class="table-title">Manajemen Pengguna</h3>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                {{-- FILTER UNIT — BARU! --}}
                <select
                    onchange="window.location.href='{{ route('users.index') }}?unit_id='+this.value+'&per_page={{ request('per_page', 10) }}'"
                    class="form-control" style="width: auto; min-width: 160px;">
                    <option value="">Semua Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
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
                        <th>Unit</th> {{-- BARU! --}}
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td><strong>{{ $user->id }}</strong></td>
                            <td>
                                <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('storage/app/public/default-avatar.jpg') }}"
                                    alt="{{ $user->name }}"
                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td><span class="status-badge available">{{ $user->level }}</span></td>
                            <td>{{ $user->unit->name ?? '-' }}</td> {{-- BARU! --}}
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
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ===== PAGINATION LINK ===== --}}
        <div style="padding: 1rem 2rem;">
            {{ $users->links() }}
        </div>
    </div>
@endsection
