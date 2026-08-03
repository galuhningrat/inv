<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Inventaris Kampus STTI Cirebon</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div id="loginPage" class="login-container">
        <div class="login-card animate-slide-down">
            <div class="logo">
                <div class="logo-image">
                    <img src="{{ asset('assets/logo-stti.png') }}" alt="Logo STTI Cirebon" class="logo-img">
                </div>
                <h1>Sistem Inventaris</h1>
                <p>Sekolah Tinggi Teknologi Indonesia Cirebon</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error"
                    style="margin-bottom: 1rem; padding: 0.75rem; background: #fee2e2; color: #dc2626; border-radius: 8px;">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success"
                    style="margin-bottom: 1rem; padding: 0.75rem; background: #d1fae5; color: #059669; border-radius: 8px;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>

    <!-- Footer untuk halaman Login -->
    <footer class="footer footer--login">
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="copyright">
                    © {{ date('Y') }} STTI - Sekolah Tinggi Teknologi Indonesia Cirebon. All rights reserved.
                </div>
                <div class="footer-meta">
                    Sistem Inventaris Kampus • v1.0.0
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Dark mode support untuk login page
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>

</html>