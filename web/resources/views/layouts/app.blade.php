<!DOCTYPE html>
<html lang="{{ App::getLocale() }}" class="{{ session('theme', 'light') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — Smart Farming</title>

  {{-- Icons + Fonts --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  @stack('styles')
</head>
<body>

{{-- Top loading bar --}}
<div id="nprogress"><div class="bar" id="topbar-bar"></div></div>

{{-- Toast container --}}
<div class="toast-container" id="toast-container"></div>

{{-- App shell --}}
<div class="app-shell">

  {{-- Mobile sidebar backdrop --}}
  <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

  {{-- Sidebar --}}
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon"><i class="ri-leaf-line"></i></div>
      <span class="logo-text">Farmify</span>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-label">Main</div>
        <a href="{{ route('dashboard.index') }}"
           class="nav-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }}"
           data-tooltip="Overview">
          <span class="nav-icon"><i class="ri-dashboard-3-line"></i></span>
          <span class="nav-text">Overview</span>
        </a>
        <a href="{{ route('dashboard.devices') }}"
           class="nav-item {{ request()->routeIs('dashboard.devices') ? 'active' : '' }}"
           data-tooltip="Devices">
          <span class="nav-icon"><i class="ri-cpu-line"></i></span>
          <span class="nav-text">Devices</span>
          <span class="nav-badge" id="sb-offline-count" style="display:none"></span>
        </a>
        <a href="{{ route('dashboard.commands') }}"
           class="nav-item {{ request()->routeIs('dashboard.commands') ? 'active' : '' }}"
           data-tooltip="Commands">
          <span class="nav-icon"><i class="ri-remote-control-line"></i></span>
          <span class="nav-text">Commands</span>
        </a>
        <a href="{{ route('dashboard.logs') }}"
           class="nav-item {{ request()->routeIs('dashboard.logs') ? 'active' : '' }}"
           data-tooltip="Activity Log">
          <span class="nav-icon"><i class="ri-file-list-3-line"></i></span>
          <span class="nav-text">Activity Log</span>
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-label">System</div>
        <a href="{{ route('dashboard.settings') }}" class="nav-item" data-tooltip="Settings">
          <span class="nav-icon"><i class="ri-settings-4-line"></i></span>
          <span class="nav-text">Settings</span>
        </a>
      </div>
    </nav>

    <div class="sidebar-footer">
      <button class="sidebar-collapse-btn" id="sidebar-toggle">
        <span class="btn-icon"><i class="ri-menu-fold-line"></i></span>
        <span class="btn-text">Collapse</span>
      </button>
    </div>
  </aside>

  {{-- Main area --}}
  <div class="main-area">

    {{-- Topbar --}}
    <header class="topbar">
      <nav class="topbar-breadcrumb">
        <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Open menu">
          <i class="ri-menu-line"></i>
        </button>
        <span class="breadcrumb-item">
          <i class="ri-leaf-line"></i> Farmify
        </span>
        <span class="breadcrumb-sep"><i class="ri-arrow-right-s-line"></i></span>
        <span class="breadcrumb-item active">@yield('breadcrumb', 'Overview')</span>
      </nav>

      <div class="topbar-actions">
        {{-- Search --}}
        <div class="topbar-search">
          <i class="ri-search-line"></i>
          <input type="text" placeholder="Search..." id="global-search">
        </div>

        {{-- Language --}}
        <button class="lang-chip" id="lang-toggle" data-tip="Switch language">
          <i class="ri-translate-2"></i>
          <span data-lang-label>EN</span>
        </button>

        {{-- Dark mode --}}
        <button class="icon-btn" id="theme-toggle" data-tip="Toggle theme">
          <i data-theme-icon class="ri-moon-line"></i>
        </button>

        {{-- Notifications --}}
        <div class="dropdown" style="position:relative">
          <button class="icon-btn" id="notif-btn" data-tip="Notifications">
            <i class="ri-notification-3-line"></i>
            <span class="notif-dot" id="notif-dot"></span>
          </button>
          <div class="notif-panel" id="notif-panel">
            <div class="notif-panel-header">
              <span class="notif-panel-title">Notifications</span>
              <button class="btn btn-ghost btn-sm" onclick="document.getElementById('notif-panel').classList.remove('open')">
                <i class="ri-close-line"></i>
              </button>
            </div>
            <div id="notif-list">
              <div class="notif-item">
                <div class="notif-icon success-bg" style="background:var(--success-bg);color:var(--success)">
                  <i class="ri-drop-line"></i>
                </div>
                <div>
                  <div class="notif-title">Auto irrigation triggered</div>
                  <div class="notif-message">Zone-B moisture dropped below 30%</div>
                  <div class="notif-time">2 min ago</div>
                </div>
              </div>
              <div class="notif-item">
                <div class="notif-icon" style="background:var(--warning-bg);color:var(--warning)">
                  <i class="ri-wifi-off-line"></i>
                </div>
                <div>
                  <div class="notif-title">Device offline</div>
                  <div class="notif-message">ZONE-C has not responded for 5 min</div>
                  <div class="notif-time">8 min ago</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Avatar --}}
        <div class="topbar-avatar" data-tip="Admin">AD</div>
      </div>

      {{-- Topbar progress --}}
      <div class="topbar-progress">
        <div class="topbar-progress-bar" id="topbar-bar"></div>
      </div>
    </header>

    {{-- Content --}}
    <main class="content-area">
      @yield('content')
    </main>

  </div>{{-- .main-area --}}
</div>{{-- .app-shell --}}

{{-- Global modals go here --}}
@stack('modals')

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@stack('scripts')

</body>
</html>