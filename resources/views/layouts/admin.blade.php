<!doctype html>
<html lang="id" data-bs-theme="light">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title', 'Dapur Ina Aina') | POS System</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      crossorigin="anonymous" media="print" onload="this.media = 'all'" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.4.0/dist/css/tabulator_bootstrap5.min.css" crossorigin="anonymous" />
    <style>
      /* Clean white Tabulator styling (no gray footer/rows) */
      .tabulator {
        background-color: #ffffff !important;
        border-color: #dee2e6 !important;
      }
      .tabulator .tabulator-header {
        background-color: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
      }
      .tabulator .tabulator-row.tabulator-row-even,
      .tabulator .tabulator-row.tabulator-row-odd,
      .tabulator .tabulator-tableholder .tabulator-table .tabulator-row {
        background-color: #ffffff !important;
      }
      .tabulator .tabulator-row:hover {
        background-color: rgba(13, 110, 253, 0.04) !important;
      }
      /* Footer and pagination background clean white */
      .tabulator .tabulator-footer {
        background-color: #ffffff !important;
        border-top: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        padding: 0.75rem 0.5rem !important;
      }
      .tabulator .tabulator-footer .tabulator-paginator {
        color: #475569 !important;
      }
      .tabulator .tabulator-footer .tabulator-page {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
        border-radius: 0.375rem !important;
        margin: 0 2px !important;
        padding: 4px 10px !important;
      }
      .tabulator .tabulator-footer .tabulator-page.active {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #ffffff !important;
      }
      .tabulator .tabulator-footer .tabulator-page:hover:not(.active):not([disabled]) {
        background-color: #f1f5f9 !important;
      }
      .tabulator .tabulator-footer .tabulator-page-size {
        border-radius: 0.375rem !important;
        border: 1px solid #cbd5e1 !important;
        padding: 4px 8px !important;
        background-color: #ffffff !important;
      }
    </style>

    @livewireStyles
    @stack('styles')
  </head>
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
      {{-- Navbar --}}
      <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
          {{-- Sidebar toggle --}}
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
              </a>
            </li>
          </ul>

          <ul class="navbar-nav ms-auto">
            {{-- Fullscreen toggle --}}
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen" aria-label="Toggle fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none"></i>
              </a>
            </li>



            {{-- User menu --}}
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5 me-1"></i>
                <span class="d-none d-md-inline">{{ Auth::user()->nama }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <i class="bi bi-person-circle" style="font-size: 4rem;"></i>
                  <p>
                    {{ Auth::user()->nama }}
                    <small>{{ Auth::user()->role }}</small>
                  </p>
                </li>
                <li class="user-footer">
                  <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger float-end">
                      <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                  </form>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>

      {{-- Sidebar --}}
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
          <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('kasir.pos') }}" class="brand-link">
            {{-- <i class="bi bi-shop brand-image opacity-75" style="font-size: 1.8rem;"></i> --}}
            <span class="brand-text fw-light">Dapur Ina Aina</span>
          </a>
        </div>
        <div class="sidebar-wrapper">
          <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">

              @if(Auth::user()->isAdmin())
                <li class="nav-header">ADMIN</li>
                <li class="nav-item">
                  <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-speedometer2"></i>
                    <p>Dashboard</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('admin.kategori.index') }}" class="nav-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-tags"></i>
                    <p>Kategori Menu</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('admin.menu.index') }}" class="nav-link {{ request()->routeIs('admin.menu.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-book"></i>
                    <p>Data Menu & Stok</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('admin.laporan.index') }}" class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-graph-up"></i>
                    <p>Laporan Penjualan</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('admin.user.index') }}" class="nav-link {{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-people"></i>
                    <p>Manajemen Pengguna</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('kasir.pos') }}" class="nav-link {{ request()->routeIs('kasir.pos') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-cart3"></i>
                    <p>Buka Kasir (POS)</p>
                  </a>
                </li>
              @endif

              @if(Auth::user()->isKasir())
                <li class="nav-header">KASIR</li>
                <li class="nav-item">
                  <a href="{{ route('kasir.pos') }}" class="nav-link {{ request()->routeIs('kasir.pos') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-cart3"></i>
                    <p>Point of Sale</p>
                  </a>
                </li>
              @endif
            </ul>
          </nav>
        </div>
      </aside>

      {{-- Main Content --}}
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">@yield('page-title')</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  @yield('breadcrumb')
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            {{-- Flash Messages --}}
            @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if(session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                
                <ul class="mb-0">
                  @foreach($errors->all() as $error)
                    <li><i class="bi bi-exclamation-triangle me-2"></i>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @yield('content')
          </div>
        </div>
      </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
      crossorigin="anonymous"></script>
    <script src="{{ asset('adminlte/js/adminlte.js') }}"></script>

    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined && !isMobile) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
          setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
          }, 5000);
        });
      });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/tabulator-tables@6.4.0/dist/js/tabulator.min.js" crossorigin="anonymous"></script>
    @stack('scripts')
    @livewireScripts
  </body>
</html>
