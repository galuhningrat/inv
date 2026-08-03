@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Edit Pengguna: {{ $user->name }}</h3>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-secondary">Edit</a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Lengkap <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') error @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="username">Username <span style="color: red;">*</span></label>
                        <input type="text" id="username" name="username"
                            class="form-control @error('username') error @enderror"
                            value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span style="color: red;">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') error @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="level">Level <span style="color: red;">*</span></label>
                        <select id="level" name="level" class="form-control @error('level') error @enderror" required>
                            <option value="">Pilih Level</option>
                            @foreach($levels as $lvl)
                                <option value="{{ $lvl }}" {{ old('level', $user->level) === $lvl ? 'selected' : '' }}>{{ $lvl }}
                                </option>
                            @endforeach
                        </select>
                        @error('level')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') error @enderror">
                        <small class="form-text">Kosongkan jika tidak ingin mengubah password</small>
                        @error('password')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: red;">*</span></label>
                    <select id="status" name="status" class="form-control @error('status') error @enderror" required>
                        <option value="Aktif" {{ old('status', $user->status) === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Nonaktif" {{ old('status', $user->status) === 'Nonaktif' ? 'selected' : '' }}>Nonaktif
                        </option>
                    </select>
                    @error('status')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="avatar">Foto Profil</label>
                    @if($user->avatar)
                        <div style="margin-bottom: 1rem;">
                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">Foto saat ini</p>
                        </div>
                    @endif
                    <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*"
                        onchange="previewAvatar(this)">
                    <div class="image-preview" id="avatarPreview"></div>
                    <small class="form-text">Upload foto baru untuk mengganti (opsional, max 2MB)</small>
                    @error('avatar')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function previewAvatar(input) {
            const preview = document.getElementById('avatarPreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-top: 1rem;">`;
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
@endpush