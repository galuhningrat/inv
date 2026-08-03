<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Inventaris Kampus') - STTI Cirebon</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<script src="{{ asset('js/horizontal-scroll.js') }}"></script>

<body class="@if(session('dark_mode')) dark-mode @endif">
    <div id="app" class="dashboard">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar collapsed">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="logo">
                    <img src="{{ asset('images/logo-stti.png') }}" alt="STTI Logo" class="logo-img">
                    <span class="logo-text">Inventaris STTI</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                @php
                    $permissions = [
                        'Admin' => ['dashboard', 'assets-inv', 'borrowing', 'maintenance', 'users', 'requests', 'reports', 'qrCode'],
                        'Sarpras' => ['dashboard', 'assets-inv', 'borrowing', 'maintenance', 'reports', 'qrCode'],
                        'Rektor' => ['dashboard', 'reports'],
                        'Kaprodi' => ['dashboard', 'requests', 'reports'],
                        'Keuangan' => ['dashboard', 'requests', 'reports'],
                    ];
                    $userPermissions = $permissions[auth()->user()->level] ?? [];
                @endphp

                @if(in_array('dashboard', $userPermissions))
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-page="dashboard">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                @endif

                @if(in_array('assets-inv', $userPermissions))
                    <a href="{{ route('assets-inv.index') }}"
                        class="nav-link {{ request()->routeIs('assets-inv.*') ? 'active' : '' }}" data-page="assets">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                            <polyline points="3.27,6.96 12,12.01 20.73,6.96" />
                            <line x1="12" y1="22.08" x2="12" y2="12" />
                        </svg>
                        <span>Manajemen Aset</span>
                    </a>
                @endif

                @if(in_array('borrowing', $userPermissions))
                    <a href="{{ route('borrowings.index') }}"
                        class="nav-link {{ request()->routeIs('borrowings.*') ? 'active' : '' }}" data-page="borrowing">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                        </svg>
                        <span>Peminjaman</span>
                    </a>
                @endif

                @if(in_array('maintenance', $userPermissions))
                    <a href="{{ route('maintenances.index') }}"
                        class="nav-link {{ request()->routeIs('maintenances.*') ? 'active' : '' }}" data-page="maintenance">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                        </svg>
                        <span>Pemeliharaan</span>
                    </a>
                @endif

                @if(in_array('users', $userPermissions))
                    <a href="{{ route('users.index') }}"
                        class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" data-page="users">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <span>Pengguna</span>
                    </a>
                @endif

                @if(in_array('requests', $userPermissions))
                    <a href="{{ route('requests.index') }}"
                        class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}" data-page="requests">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14,2 14,8 20,8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10,9 9,9 8,9" />
                        </svg>
                        <span>Pengajuan Aset</span>
                    </a>
                @endif

                @if(in_array('reports', $userPermissions))
                    <a href="{{ route('reports.index') }}"
                        class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-page="reports">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14,2 14,8 20,8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                        <span>Laporan</span>
                    </a>
                @endif

                @if(in_array('qrCode', $userPermissions))
                    <a href="{{ route('qrcodes.index') }}"
                        class="nav-link {{ request()->routeIs('qrcodes.*') ? 'active' : '' }}" data-page="qrCode">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="3" height="3" />
                            <rect x="18" y="14" width="3" height="3" />
                            <rect x="14" y="18" width="3" height="3" />
                            <rect x="18" y="18" width="3" height="3" />
                        </svg>
                        <span>Manajemen QR Code</span>
                    </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content sidebar-collapsed" id="mainContent">
            <!-- Navbar -->
            <nav class="navbar">
                <div class="navbar-left">
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <span class="navbar-brand" id="pageTitle">@yield('page-title', 'Dashboard')</span>
                </div>
                <div class="navbar-nav">
                    <button class="btn btn-secondary" onclick="toggleDarkMode()" id="darkModeBtn"
                        aria-label="Toggle Dark Mode">
                        <span id="darkModeToggleIcon">🌙</span>
                    </button>
                    <div class="user-profile">
                        <img src="{{ auth()->user()->avatar ? Storage::url(auth()->user()->avatar) : asset('images/default-avatar.png') }}"
                            alt="Profile" class="profile-img" id="userAvatarImg">
                        <div>
                            <span id="currentUser">{{ auth()->user()->name }}</span>
                            <span id="userRole" class="user-role">{{ auth()->user()->level }}</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Logout</button>
                    </form>
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success" id="flashMessage"
                        style="margin-bottom: 1rem; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; border-left: 4px solid #10b981;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error" id="flashMessage"
                        style="margin-bottom: 1rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; border-left: 4px solid #ef4444;">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="footer footer--dashboard">
                <div class="footer-content">
                    <div class="footer-section">
                        <div class="footer-logo">
                            <img src="{{ asset('images/logo-stti.png') }}" alt="STTI Logo" class="footer-logo-img">
                            <div class="footer-logo-text">
                                <h3>SISTEM INVENTARIS</h3>
                                <p>Sekolah Tinggi Teknologi Indonesia Cirebon</p>
                            </div>
                        </div>
                        <p class="footer-description">
                            Sistem manajemen inventaris terintegrasi untuk pengelolaan aset kampus yang efisien dan
                            modern.
                        </p>
                    </div>

                    <div class="footer-section">
                        <h4 class="footer-title">Tautan Cepat</h4>
                        <ul class="footer-links">
                            <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            @if(in_array('assets-inv', $userPermissions ?? []))
                                <li><a href="{{ route('assets-inv.index') }}">Manajemen Aset</a></li>
                            @endif
                            @if(in_array('borrowing', $userPermissions ?? []))
                                <li><a href="{{ route('borrowings.index') }}">Peminjaman</a></li>
                            @endif
                            @if(in_array('reports', $userPermissions ?? []))
                                <li><a href="{{ route('reports.index') }}">Laporan</a></li>
                            @endif
                        </ul>
                    </div>

                    <div class="footer-section">
                        <h4 class="footer-title">Kontak</h4>
                        <div class="footer-contact">
                            <div class="contact-item">
                                <span class="contact-icon">📧</span>
                                <span>akademik@stti.ac.id</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-icon">📞</span>
                                <span>081392637640</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-icon">📍</span>
                                <span>Jalan Raya Cirebon Kuningan Desa Kondangsari</span>
                            </div>
                        </div>
                    </div>

                    <div class="footer-section">
                        <h4 class="footer-title">Status Sistem</h4>
                        <div class="system-status">
                            <div class="status-item">
                                <span class="status-dot online"></span>
                                <span>Sistem: Online</span>
                            </div>
                            <div class="status-item">
                                <span class="status-dot"></span>
                                <span>Pengguna: <span id="footerUserStatus">{{ auth()->user()->level }}</span></span>
                            </div>
                            <div class="status-item">
                                <span class="status-icon">🕒</span>
                                <span id="footerServerTime">{{ now()->format('H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-bottom">
                    <div class="footer-bottom-content">
                        <div class="copyright">
                            © {{ date('Y') }} STTI - Sekolah Tinggi Teknologi Indonesia Cirebon. All rights reserved.
                        </div>
                        <div class="footer-meta">
                            <span id="footerAssetCount">{{ \App\Models\Asset::count() }}</span> Aset •
                            <span id="footerUserCount">{{ \App\Models\User::count() }}</span> Pengguna • v1.0.0
                            <button class="footer-scroll-top" onclick="scrollToTop()">
                                <span class="arrow-up">↑</span> Ke Atas
                            </button>
                        </div>
                    </div>
                </div>
            </footer>
        </main>

        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>
    </div>

    <!-- Scroll Hint -->
    <div class="scroll-hint" id="scrollHint">
        💡 <strong>Tip:</strong> Gunakan <strong>Shift + Scroll</strong> untuk melihat konten tabel secara horizontal
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>Memproses...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <script>
        // CSRF Token for AJAX
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        // Initialize sidebar
        document.addEventListener('DOMContentLoaded', function () {
            initializeSidebar();
            initializeFlashMessages();
            initializeScrollHint();
            updateServerTime();
            setInterval(updateServerTime, 1000);
        });

        function initializeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const menuToggle = document.getElementById('menuToggle');
            const overlay = document.getElementById('overlay');

            if (menuToggle) {
                menuToggle.addEventListener('click', toggleSidebar);
            }

            if (overlay) {
                overlay.addEventListener('click', function () {
                    if (window.innerWidth < 1024) {
                        closeMobileSidebar();
                    }
                });
            }

            // Handle resize
            window.addEventListener('resize', handleResize);
        }

        function toggleSidebar() {
            if (window.innerWidth < 1024) {
                toggleMobileSidebar();
            } else {
                toggleDesktopSidebar();
            }
        }

        function toggleDesktopSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }

        function handleResize() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const isDarkMode = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);

            const icon = document.getElementById('darkModeToggleIcon');
            icon.textContent = isDarkMode ? '☀️' : '🌙';
        }

        // Apply saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
            document.getElementById('darkModeToggleIcon').textContent = '☀️';
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateServerTime() {
            const now = new Date();

            // Format waktu: HH.mm.ss
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}.${minutes}.${seconds}`;

            // Format tanggal: Day, dd Mon yyyy
            const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            const dayName = days[now.getDay()];
            const day = String(now.getDate()).padStart(2, '0');
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();

            const dateString = `${dayName}, ${day} ${monthName} ${year}`;

            // Gabungkan: HH.mm.ss • Day, dd Mon yyyy
            const element = document.getElementById('footerServerTime');
            if (element) {
                element.textContent = `${timeString} • ${dateString}`;
            }
        }

        function initializeFlashMessages() {
            const flashMessage = document.getElementById('flashMessage');
            if (flashMessage) {
                setTimeout(function () {
                    flashMessage.style.opacity = '0';
                    flashMessage.style.transition = 'opacity 0.3s ease';
                    setTimeout(function () {
                        flashMessage.remove();
                    }, 300);
                }, 3000);
            }
        }

        function initializeScrollHint() {
            const tables = document.querySelectorAll('.table-wrapper, .data-table-container');
            let hintShown = localStorage.getItem('scrollHintShown');

            tables.forEach(table => {
                if (table.scrollWidth > table.clientWidth && !hintShown) {
                    const hint = document.getElementById('scrollHint');
                    if (hint) {
                        hint.classList.add('show');
                        setTimeout(() => {
                            hint.classList.remove('show');
                            localStorage.setItem('scrollHintShown', 'true');
                        }, 5000);
                    }
                }
            });
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function showToast(message, type = 'success') {
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000;';
                document.body.appendChild(toastContainer);
            }

            const toast = document.createElement('div');
            const bgColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#f59e0b';
            toast.style.cssText = `background: ${bgColor}; color: white; padding: 12px 20px; border-radius: 6px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateX(100%); transition: transform 0.3s ease;`;
            toast.textContent = message;

            toastContainer.appendChild(toast);

            setTimeout(() => { toast.style.transform = 'translateX(0)'; }, 100);
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => { toast.remove(); }, 300);
            }, 3000);
        }
    </script>

    @stack('scripts')
</body>

</html>