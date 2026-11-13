<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Masuk - SINADAS STTI Cirebon</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@1,900&display=swap"
        rel="stylesheet">

    <style>
        /* ============================================================
           LIGHT MODE (default) — tema ivory hangat
        ============================================================ */
        :root {
            color-scheme: light;

            --stti-blue: #0067B1;
            --stti-blue-dark: #005795;
            --stti-blue-soft: rgba(0, 103, 177, 0.08);
            --stti-blue-rgb: 0, 103, 177;

            /* Putih gading — hangat, tidak silau */
            --paper: #fdfaf3;
            --card-bg: #ffffff;
            --card-border: rgba(0, 0, 0, 0.04);

            --ink: #1a1a1a;
            --ink-soft: #6b7280;
            --ink-faint: #9ca3af;

            --input-bg: #fafbfc;
            --input-bg-hover: #ffffff;
            --input-border: #e5e7eb;
            --input-border-hover: #d1d5db;

            --alert-error-bg: #fef2f2;
            --alert-error-text: #b42318;
            --alert-error-border: #fecaca;

            --alert-success-bg: #f0f9ff;
            --alert-success-text: #005795;
            --alert-success-border: #bae6fd;
        }

        /* ============================================================
           DARK MODE — otomatis mengikuti setting OS/perangkat user
           Aktif ketika user memilih "Dark" di pengaturan sistem.
        ============================================================ */
        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;

                /* Blue accent sedikit lebih terang agar kontras di dark bg */
                --stti-blue: #3b9ee5;
                --stti-blue-dark: #5aafe8;
                --stti-blue-soft: rgba(59, 158, 229, 0.15);
                --stti-blue-rgb: 59, 158, 229;

                /* Warm stone palette — konsisten dengan nuansa ivory di light mode */
                --paper: #1c1917;
                --card-bg: #262220;
                --card-border: rgba(255, 255, 255, 0.05);

                --ink: #f5f5f4;
                --ink-soft: #a8a29e;
                --ink-faint: #78716c;

                --input-bg: #201d1b;
                --input-bg-hover: #2a2624;
                --input-border: #3a352e;
                --input-border-hover: #4a4440;

                --alert-error-bg: rgba(185, 28, 28, 0.12);
                --alert-error-text: #fca5a5;
                --alert-error-border: rgba(239, 68, 68, 0.25);

                --alert-success-bg: rgba(59, 130, 246, 0.12);
                --alert-success-text: #93c5fd;
                --alert-success-border: rgba(59, 130, 246, 0.3);
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: clamp(15px, 0.95vw + 12px, 17px);
        }

        html,
        body {
            width: 100%;
            min-height: 100vh;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--ink);
            background: var(--paper);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;

            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;

            /* Transisi halus kalau OS/user ganti tema */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ============================================================
           WRAPPER
        ============================================================ */
        .login-wrap {
            width: 100%;
            max-width: 26rem;
            animation: fadeUp 0.5s ease-out;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================================
           BRAND
        ============================================================ */
        .brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-style: italic;
            font-size: clamp(2rem, 4.5vw, 2.875rem);
            line-height: 1.05;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .brand-subtitle {
            font-size: clamp(0.7rem, 1.1vw, 0.85rem);
            color: var(--ink-soft);
            line-height: 1.4;
            white-space: nowrap;
            letter-spacing: 0.01em;
            transition: color 0.3s ease;
        }

        @media (max-width: 340px) {
            .brand-subtitle {
                white-space: normal;
                font-size: 0.7rem;
            }
        }

        /* ============================================================
           CARD
        ============================================================ */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 1.75rem 1.6rem 1.4rem;
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.02),
                0 8px 32px rgba(var(--stti-blue-rgb), 0.06);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        /* ============================================================
           FORM FIELDS
        ============================================================ */
        .field {
            position: relative;
            margin-bottom: 0.75rem;
        }

        .field-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            width: 1rem;
            height: 1rem;
            color: var(--ink-faint);
            transform: translateY(-50%);
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .field input {
            width: 100%;
            height: 2.875rem;
            padding: 0 0.875rem 0 2.625rem;
            font-family: inherit;
            font-size: 0.85rem;
            color: var(--ink);
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            outline: none;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .field input::placeholder {
            color: var(--ink-faint);
        }

        .field input:hover {
            background: var(--input-bg-hover);
            border-color: var(--input-border-hover);
        }

        .field input:focus {
            background: var(--input-bg-hover);
            border-color: var(--stti-blue);
            box-shadow: 0 0 0 3px var(--stti-blue-soft);
        }

        .field:focus-within .field-icon {
            color: var(--stti-blue);
        }

        /* Fix autofill browser di dark mode — bawaannya putih menyilaukan */
        .field input:-webkit-autofill,
        .field input:-webkit-autofill:hover,
        .field input:-webkit-autofill:focus {
            -webkit-text-fill-color: var(--ink);
            -webkit-box-shadow: 0 0 0 1000px var(--input-bg) inset;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* ============================================================
           ALERTS
        ============================================================ */
        .alert {
            padding: 0.625rem 0.75rem;
            margin-bottom: 0.875rem;
            border-radius: 8px;
            font-size: 0.75rem;
            line-height: 1.5;
        }

        .alert-error {
            background: var(--alert-error-bg);
            color: var(--alert-error-text);
            border: 1px solid var(--alert-error-border);
        }

        .alert-success {
            background: var(--alert-success-bg);
            color: var(--alert-success-text);
            border: 1px solid var(--alert-success-border);
        }

        /* ============================================================
           BUTTON
        ============================================================ */
        .btn {
            width: 100%;
            height: 2.875rem;
            margin-top: 0.5rem;
            border: 0;
            border-radius: 8px;
            color: #ffffff;
            background: var(--stti-blue);
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn:hover {
            background: var(--stti-blue-dark);
            box-shadow: 0 8px 20px rgba(var(--stti-blue-rgb), 0.25);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(var(--stti-blue-rgb), 0.18);
        }

        /* ============================================================
           FOOTER — logo + copyright
        ============================================================ */
        .footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin-top: 1.4rem;
            font-size: 0.72rem;
            color: var(--ink-faint);
            letter-spacing: 0.02em;
            transition: color 0.3s ease;
        }

        .footer-logo {
            height: 0.9rem;
            width: auto;
            opacity: 0.7;
            display: block;
            flex-shrink: 0;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .login-wrap {
                max-width: 100%;
            }

            .card {
                padding: 1.5rem 1.25rem 1.2rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrap">
        {{-- ============================================================
             BRAND
        ============================================================ --}}
        <div class="brand">
            <h1 class="brand-title">SINADAS</h1>
            <p class="brand-subtitle">Sistem Informasi Inventaris dan Administrasi Aset</p>
        </div>

        {{-- ============================================================
             CARD
        ============================================================ --}}
        <div class="card">
            @if ($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="field">
                    <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <input type="text" id="username" name="username" placeholder="Username"
                        value="{{ old('username') }}" required autofocus autocomplete="username">
                </div>

                <div class="field">
                    <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <input type="password" id="password" name="password" placeholder="Password" required
                        autocomplete="current-password">
                </div>

                <button type="submit" class="btn">Masuk</button>
            </form>
        </div>

        {{-- ============================================================
             FOOTER
        ============================================================ --}}
        <div class="footer">
            <img src="{{ asset('assets/logo-stti.png') }}" alt="STTI Cirebon" class="footer-logo">
            <span>© {{ date('Y') }} STTI Cirebon</span>
        </div>
    </div>
</body>

</html>
