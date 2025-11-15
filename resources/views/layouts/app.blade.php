<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Subtitle Generator') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bs-body-font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        body {
            font-family: var(--bs-body-font-family);
            background-color: #f5f5f9;
        }
        .layout-wrapper {
            display: flex;
        }
        .layout-menu {
            width: 260px;
            min-height: 100vh;
            background-color: #fff;
            box-shadow: 0 0 0 1px rgba(67, 89, 113, 0.1);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1030;
        }
        .app-brand {
            padding: 1.5rem 1.5rem 0.75rem;
            border-bottom: 1px solid rgba(67, 89, 113, 0.1);
        }
        .app-brand-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: #696cff;
        }
        .menu-inner {
            padding: 0.5rem 0;
        }
        .menu-item {
            margin: 0.25rem 0.75rem;
        }
        .menu-link {
            display: flex;
            align-items: center;
            padding: 0.625rem 1rem;
            color: #697a8d;
            text-decoration: none;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        .menu-link:hover {
            background-color: #f5f5f9;
            color: #696cff;
        }
        .menu-link.active {
            background-color: rgba(105, 108, 255, 0.1);
            color: #696cff;
        }
        .menu-icon {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }
        .layout-page {
            margin-left: 260px;
            flex: 1;
            min-height: 100vh;
        }
        .layout-navbar {
            background-color: #fff;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
            padding: 1rem 1.5rem;
        }
        .content-wrapper {
            padding: 1.5rem;
        }
        .navbar-brand {
            font-weight: 600;
        }
        .card {
            border: none;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        }
        .btn-primary {
            background-color: #696cff;
            border-color: #696cff;
        }
        .btn-primary:hover {
            background-color: #5f62e6;
            border-color: #5f62e6;
        }
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 500;
            border-radius: 0.25rem;
        }
        .status-uploaded { background-color: #e7e7ff; color: #696cff; }
        .status-processing_subtitle { background-color: #fff3cd; color: #856404; }
        .status-subtitle_generated { background-color: #d1ecf1; color: #0c5460; }
        .status-processing_video { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-failed { background-color: #f8d7da; color: #721c24; }
        @media (max-width: 1199.98px) {
            .layout-menu {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .layout-menu.show {
                transform: translateX(0);
            }
            .layout-page {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('videos.index') }}" class="app-brand-link">
                        <span class="app-brand-text demo menu-text fw-bold ms-2">Subtitle Generator</span>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-item">
                        <a href="{{ route('videos.index') }}" class="menu-link">
                            <i class='menu-icon bx bx-video'></i>
                            <div data-i18n="Videos">Videos</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('videos.create') }}" class="menu-link">
                            <i class='menu-icon bx bx-upload'></i>
                            <div data-i18n="Upload Video">Upload Video</div>
                        </a>
                    </li>
                </ul>
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <nav class="layout-navbar navbar navbar-expand-xl navbar-detached align-items-center" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)" onclick="toggleMenu()">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>
                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-nav-right">
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center">
                                <span class="fw-semibold d-block">{{ config('app.name', 'Laravel') }}</span>
                            </div>
                        </div>
                    </div>
                </nav>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                    <!-- / Content -->
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        function toggleMenu() {
            document.getElementById('layout-menu').classList.toggle('show');
        }
    </script>
    @stack('scripts')
</body>
</html>

