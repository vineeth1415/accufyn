<!DOCTYPE html>
<html lang="en" class="h-full light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - WebotApp Accounting</title>

    <script>
        // Immediate theme detection (default: light)
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @php
        $themeName = $company->theme_color ?? 'emerald';
        $themePalettes = [
            'emerald' => [
                '50' => '#ecfdf5', '100' => '#d1fae5', '200' => '#a7f3d0', 
                '400' => '#34d399', '500' => '#10b981', '600' => '#059669', '700' => '#047857'
            ],
            'indigo' => [
                '50' => '#eef2ff', '100' => '#e0e7ff', '200' => '#c7d2fe', 
                '400' => '#818cf8', '500' => '#6366f1', '600' => '#4f46e5', '700' => '#4338ca'
            ],
            'blue' => [
                '50' => '#eff6ff', '100' => '#dbeafe', '200' => '#bfdbfe', 
                '400' => '#60a5fa', '500' => '#3b82f6', '600' => '#2563eb', '700' => '#1d4ed8'
            ],
            'purple' => [
                '50' => '#faf5ff', '100' => '#f3e8ff', '200' => '#e9d5ff', 
                '400' => '#c084fc', '500' => '#a855f7', '600' => '#9333ea', '700' => '#7e22ce'
            ],
            'rose' => [
                '50' => '#fff1f2', '100' => '#ffe4e6', '200' => '#fecdd3', 
                '400' => '#fb7185', '500' => '#f43f5e', '600' => '#e11d48', '700' => '#be123c'
            ],
            'amber' => [
                '50' => '#fffbeb', '100' => '#fef3c7', '200' => '#fde68a', 
                '400' => '#fbbf24', '500' => '#f59e0b', '600' => '#d97706', '700' => '#b45309'
            ],
        ];
        $activePalette = $themePalettes[$themeName] ?? $themePalettes['emerald'];
    @endphp

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        emerald: {
                            50: '{{ $activePalette["50"] }}',
                            100: '{{ $activePalette["100"] }}',
                            200: '{{ $activePalette["200"] }}',
                            400: '{{ $activePalette["400"] }}',
                            500: '{{ $activePalette["500"] }}',
                            600: '{{ $activePalette["600"] }}',
                            700: '{{ $activePalette["700"] }}',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* LIGHT MODE (DEFAULT) */
        html:not(.dark) {
            background-color: #f8fafc;
            color: #0f172a;
        }
        html:not(.dark) body {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }

        /* Sidebar Styling in Light Mode */
        html:not(.dark) aside {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 1px 0 3px rgba(0, 0, 0, 0.03);
        }
        html:not(.dark) aside .border-b,
        html:not(.dark) aside .border-t {
            border-color: #f1f5f9 !important;
        }
        html:not(.dark) aside h1 {
            color: #0f172a !important;
        }
        html:not(.dark) aside p {
            color: #64748b !important;
        }
        html:not(.dark) aside .nav-link {
            color: #475569 !important;
        }
        html:not(.dark) aside .nav-link:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        html:not(.dark) aside .nav-link.active {
            background: #ecfdf5 !important;
            border-left: 3px solid #059669 !important;
            color: #047857 !important;
            font-weight: 600;
        }
        html:not(.dark) aside .nav-link.active i {
            color: #059669 !important;
        }
        html:not(.dark) aside .user-footer {
            background-color: #f8fafc !important;
        }

        /* Top Header in Light Mode */
        html:not(.dark) header {
            background-color: rgba(255, 255, 255, 0.95) !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }
        html:not(.dark) header h2 {
            color: #0f172a !important;
        }

        /* Cards & Containers in Light Mode */
        html:not(.dark) .glass-card {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
        }

        /* Typography & Elements in Light Mode */
        html:not(.dark) .text-white {
            color: #0f172a !important;
        }
        /* Preserve white text inside vibrant colored action buttons */
        html:not(.dark) .bg-emerald-600,
        html:not(.dark) .bg-emerald-500,
        html:not(.dark) .bg-blue-600,
        html:not(.dark) .bg-red-600,
        html:not(.dark) .bg-indigo-600,
        html:not(.dark) .btn-primary,
        html:not(.dark) [class*="bg-emerald-600"],
        html:not(.dark) [class*="bg-red-600"],
        html:not(.dark) [class*="bg-blue-600"],
        html:not(.dark) .force-white {
            color: #ffffff !important;
        }
        html:not(.dark) [class*="bg-emerald-600"] *,
        html:not(.dark) [class*="bg-red-600"] *,
        html:not(.dark) [class*="bg-blue-600"] * {
            color: #ffffff !important;
        }

        html:not(.dark) .text-slate-100,
        html:not(.dark) .text-slate-200,
        html:not(.dark) .text-slate-300 {
            color: #334155 !important;
        }
        html:not(.dark) .text-slate-400 {
            color: #64748b !important;
        }
        html:not(.dark) .text-slate-500 {
            color: #64748b !important;
        }

        /* Borders in Light Mode */
        html:not(.dark) .border-slate-800,
        html:not(.dark) .border-slate-700,
        html:not(.dark) .border-slate-800\/80,
        html:not(.dark) .border-slate-700\/80,
        html:not(.dark) .border-slate-800\/60,
        html:not(.dark) .border-slate-800\/90 {
            border-color: #e2e8f0 !important;
        }
        html:not(.dark) .divide-slate-800,
        html:not(.dark) .divide-slate-800\/60 {
            border-color: #f1f5f9 !important;
        }

        /* Tables in Light Mode */
        html:not(.dark) thead,
        html:not(.dark) thead tr {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        html:not(.dark) thead th {
            color: #475569 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        html:not(.dark) tbody tr:hover {
            background-color: #f8fafc !important;
        }
        html:not(.dark) tbody td {
            border-color: #f1f5f9 !important;
        }

        /* Inputs & Form Fields in Light Mode */
        html:not(.dark) input:not([type="checkbox"]):not([type="radio"]),
        html:not(.dark) select,
        html:not(.dark) textarea {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        html:not(.dark) input::placeholder,
        html:not(.dark) textarea::placeholder {
            color: #94a3b8 !important;
        }
        html:not(.dark) input:focus,
        html:not(.dark) select:focus,
        html:not(.dark) textarea:focus {
            border-color: #059669 !important;
            box-shadow: 0 0 0 1px #059669 !important;
        }

        /* Secondary Action Buttons in Light Mode */
        html:not(.dark) .bg-slate-800 {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
        }
        html:not(.dark) .bg-slate-800:hover {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }
        html:not(.dark) .bg-slate-800 * {
            color: #1e293b !important;
        }

        /* Modals & Dialogs in Light Mode */
        html:not(.dark) .modal-content,
        html:not(.dark) [id$="Modal"] .bg-slate-900,
        html:not(.dark) [id$="modal"] .bg-slate-900 {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #0f172a !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
        }

        /* DARK MODE STYLING */
        html.dark body {
            background-color: #020617;
            color: #e2e8f0;
        }
        html.dark .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        html.dark .nav-link.active {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.05) 100%);
            border-left: 3px solid #10b981;
            color: #34d399;
        }

        /* Smooth custom scrollbar for sidebar */
        .custom-sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.35);
            border-radius: 9999px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.6);
        }
    </style>
</head>
<body class="h-full antialiased flex overflow-hidden">

    <!-- Sidebar Navigation (Scrollable & Responsive) -->
    <aside class="w-64 h-screen max-h-screen flex flex-col shrink-0 select-none z-30 transition-colors border-r">
        <!-- App Brand (Fixed at Top) -->
        <div class="h-16 flex items-center gap-3 px-6 border-b shrink-0">
            @if(!empty($company->logo_path) && file_exists(public_path($company->logo_path)))
                <img src="{{ asset($company->logo_path) }}" alt="{{ $company->name ?? 'WebotApp' }}" class="w-9 h-9 object-contain rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-0.5 bg-white shrink-0">
            @else
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-lg shadow-sm shrink-0">
                    <i class="fa-solid fa-coins"></i>
                </div>
            @endif
            <div class="min-w-0">
                <h1 class="text-sm font-bold tracking-wide flex items-center gap-1.5 truncate">
                    {{ $company->name ?? 'WebotApp' }} <span class="text-emerald-600 dark:text-emerald-400 font-semibold text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20 shrink-0">PRO</span>
                </h1>
                <p class="text-[10px] tracking-wider uppercase font-medium truncate">Accounting & Invoicing</p>
            </div>
        </div>

        <!-- Navigation Links (Scrollable Area) -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden p-3 space-y-1 text-sm font-medium custom-sidebar-scroll">
            <nav class="space-y-1">
                @if(Auth::check() && !Auth::user()->isAuditor())
                    <a href="{{ route('dashboard') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Dashboard</span>
                    </a>
                @endif

                @if(Auth::check() && Auth::user()->canManageUsers())
                    <a href="{{ route('users.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fa-solid {{ Auth::user()->isSuperAdmin() ? 'fa-crown text-amber-500' : 'fa-users-gear text-purple-600 dark:text-purple-400' }} w-5 text-center"></i>
                        <span class="flex-1 font-semibold">User Management</span>
                        @if(Auth::user()->isSuperAdmin())
                            <span class="text-[9px] uppercase px-1.5 py-0.5 rounded font-black bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30">Owner</span>
                        @endif
                    </a>
                @endif

                @if(Auth::check() && Auth::user()->canManageAccounting())
                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Sales &amp; Revenue</div>
                    <a href="{{ route('invoices.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Invoices</span>
                    </a>
                    <a href="{{ route('customers.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Customers</span>
                    </a>

                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Purchases &amp; Expenses</div>
                    <a href="{{ route('bills.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('bills.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-receipt w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Bills &amp; Expenses</span>
                    </a>
                    <a href="{{ route('vendors.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck-field w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Vendors</span>
                    </a>
                @endif

                @if(Auth::check() && !Auth::user()->isAuditor())
                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Finance &amp; Catalog</div>
                    @if(Auth::user()->isAdmin() || Auth::user()->isAccountant())
                        <a href="{{ route('banking.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('banking.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-building-columns w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                            <span>Banking &amp; Cash</span>
                        </a>
                    @endif
                    <a href="{{ route('items.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('items.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Products &amp; Services</span>
                    </a>
                @endif

                @if(Auth::check() && (Auth::user()->canViewReports() || Auth::user()->isAuditor()))
                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        {{ Auth::user()->isAuditor() ? 'Auditor Financials & Filing' : 'Ledgers & Financials' }}
                    </div>
                    <a href="{{ route('ledgers.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('ledgers.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-book-bookmark w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Custom Ledgers</span>
                    </a>
                    <a href="{{ route('reports.profit_loss') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('reports.*') && !request()->routeIs('reports.trial_balance') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Financial Reports</span>
                    </a>
                    <a href="{{ route('reports.trial_balance') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('reports.trial_balance') ? 'active' : '' }}">
                        <i class="fa-solid fa-scale-balanced w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Trial Balance Audit</span>
                    </a>
                @endif

                @if(Auth::check() && Auth::user()->isAdmin())
                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Administration</div>
                    <a href="{{ route('users.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear w-5 text-center text-purple-600 dark:text-purple-400"></i>
                        <span>User Management</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span>Settings</span>
                    </a>
                    <a href="{{ route('updates.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('updates.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-cloud-arrow-down w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                        <span class="flex-1">System Updates</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- User Profile & System Status (Fixed at Bottom) -->
        <div class="user-footer p-4 border-t shrink-0">
            <div class="flex items-center justify-between">
                    @if(Auth::user()->isSuperAdmin())
                        <div class="w-8 h-8 rounded-full bg-amber-500/15 text-amber-500 dark:text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/30 shadow-xs">
                            <i class="fa-solid fa-crown text-[11px]"></i>
                        </div>
                    @else
                        <div class="w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/20">
                            {{ strtoupper(substr(Auth::user()->name ?? 'Admin', 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-xs font-semibold truncate flex items-center gap-1">
                            <span>{{ Auth::user()->name ?? 'Administrator' }}</span>
                            @if(Auth::user()->isSuperAdmin())
                                <i class="fa-solid fa-crown text-[10px] text-amber-500" title="Super Administrator / Top Owner"></i>
                            @endif
                        </p>
                        <p class="text-[10px] truncate flex items-center gap-1 font-semibold {{ Auth::user()->isSuperAdmin() ? 'text-amber-500 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ Auth::user()->isSuperAdmin() ? 'bg-amber-400' : 'bg-emerald-500' }} animate-pulse"></span>
                            <span>{{ Auth::user()->isSuperAdmin() ? 'SUPER ADMIN (OWNER)' : strtoupper(Auth::user()->role ?? 'ADMIN') }}</span>
                        </p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <i class="fa-solid fa-power-off text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Bar -->
        <header class="h-16 px-6 flex items-center justify-between shrink-0 border-b">
            <div class="flex items-center gap-3">
                <h2 class="text-base font-semibold">@yield('title', 'Overview')</h2>
                @yield('breadcrumbs')
            </div>

            <div class="flex items-center gap-3">
                <!-- Theme Toggle Button -->
                <button type="button" onclick="toggleTheme()" id="themeToggleBtn" class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-emerald-600 transition shadow-sm" title="Toggle Light / Dark Mode">
                    <i id="themeIconSun" class="fa-solid fa-sun text-amber-500 hidden"></i>
                    <i id="themeIconMoon" class="fa-solid fa-moon text-slate-600"></i>
                </button>

                @if(Auth::check() && Auth::user()->canManageUsers())
                    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-bold px-3 py-2 rounded-xl shadow-sm transition">
                        <i class="fa-solid fa-user-plus text-slate-950"></i>
                        <span class="text-slate-950 font-bold">+ New User</span>
                    </a>
                @endif

                @if(Auth::check() && Auth::user()->canManageAccounting())
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-3 py-2 rounded-xl shadow-sm transition">
                        <i class="fa-solid fa-plus text-white"></i>
                        <span class="text-white force-white">New Invoice</span>
                    </a>
                    <a href="{{ route('bills.create') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-xs font-semibold px-3 py-2 rounded-xl border transition">
                        <i class="fa-solid fa-receipt"></i>
                        <span>New Bill</span>
                    </a>
                @elseif(Auth::check() && Auth::user()->isAuditor())
                    <a href="{{ route('reports.profit_loss.export') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-3.5 py-2 rounded-xl shadow-sm transition">
                        <i class="fa-solid fa-file-excel text-white"></i>
                        <span class="text-white force-white">Export Audit Excel</span>
                    </a>
                @endif
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('info'))
            <div class="mx-6 mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm flex items-center justify-between shadow-sm animate-fade">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-info text-amber-600 text-base"></i>
                    <span>{{ session('info') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('success'))
            <div class="mx-6 mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm animate-fade">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-sm animate-fade">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-red-600 text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        <!-- Scrollable Page Body -->
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>

    <script>
        function updateThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            const sunIcon = document.getElementById('themeIconSun');
            const moonIcon = document.getElementById('themeIconMoon');
            if (isDark) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcons();
        }

        // Initialize icons on DOM load
        document.addEventListener('DOMContentLoaded', updateThemeIcons);
    </script>

    @include('partials.ai-assistant')

    @yield('scripts')
</body>
</html>
