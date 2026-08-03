@extends('layouts.app')

@section('title', 'Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Manajemen Pengguna</h3>
            <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Level</th>
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
                            <td>
                                <span class="status-badge {{ $user->status === 'Aktif' ? 'available' : 'maintenance' }}">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary">Edit</a>
                                    @if($user->id !== 1)
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;"
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
                            <td colspan="7" style="text-align: center; padding: 2rem;">Tidak ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">
            {{ $users->links() }}
        </div>
    </div>
@endsection