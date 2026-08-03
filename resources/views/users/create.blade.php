@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah Pengguna')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Tambah Pengguna</h3>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Lengkap <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') error @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="username">Username <span style="color: red;">*</span></label>
                        <input type="text" id="username" name="username"
                            class="form-control @error('username') error @enderror" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span style="color: red;">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') error @enderror"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="level">Level <span style="color: red;">*</span></label>
                        <select id="level" name="level" class="form-control @error('level') error @enderror" required>
                            <option value="">Pilih Level</option>
                            @foreach($levels as $lvl)
                                <option value="{{ $lvl }}" {{ old('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                            @endforeach
                        </select>
                        @error('level')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span style="color: red;">*</span></label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') error @enderror" required>
                        @error('password')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password <span style="color: red;">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="avatar">Foto Profil</label>
                    <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*"
                        onchange="previewAvatar(this)">
                    <div class="image-preview" id="avatarPreview"></div>
                    <small class="form-text">Upload foto profil (opsional, max 2MB)</small>
                    @error('avatar')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan</button>
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