<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — WiFiPads NAC Controller</title>
    <meta name="description" content="WiFiPads — Multi-Tenant Network Access Control & Edge Gateway Controller">

    <!-- Google Fonts: Plus Jakarta Sans (Headings/Brand) + Inter (Body/UI) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN (with console warning filter) -->
    <script>
        (function(){
            var w = console.warn;
            console.warn = function(){
                if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].indexOf('cdn.tailwindcss.com') !== -1) return;
                w.apply(console, arguments);
            };
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Menlo', 'monospace'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#22449E',
                            hover: '#1b3680',
                            light: '#eef4ff',
                            50: '#eef4ff',
                            100: '#d9e6ff',
                            200: '#bcd3ff',
                            300: '#8eb8ff',
                            400: '#5890fc',
                            500: '#326bf7',
                            600: '#22449E', // Primary Royal Cobalt from SMARTIV DNA
                            700: '#1b3680',
                            800: '#152a63',
                            900: '#0f1d45',
                            950: '#0a132c',
                        },
                        canvas: '#F4F7FC',
                        surface: '#FFFFFF',
                    },
                    borderRadius: {
                        'sm': 'var(--radius-sm, 8px)',
                        'md': 'var(--radius-md, 12px)',
                        'lg': 'var(--radius-lg, 20px)',
                        'pill': 'var(--radius-pill, 9999px)',
                    },
                    boxShadow: {
                        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.04)',
                        'sm': '0 2px 6px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.04)',
                        'md': '0 4px 14px 0 rgba(34, 68, 158, 0.15)',
                    },
                    fontSize: {
                        '2xs': ['10px', { lineHeight: '14px' }],
                        'xs': ['12px', { lineHeight: '16px' }],
                        'sm': ['14px', { lineHeight: '20px' }],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            /* Brand Color Palette Tokens */
            --color-brand: 34 68 158;
            --color-brand-hex: #22449E;
            --color-brand-hover: #1b3680;
            --color-brand-light: #eef4ff;

            /* 4-Tier Border Radius Scale */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-pill: 9999px;
        }

        body { 
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; 
            background-color: #F4F7FC;
            color: #0F172A;
        }
        [x-cloak] { display: none !important; }
        
        .sidebar-item { 
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem; /* Strict 8px 12px grid */
            border-radius: var(--radius-sm, 8px);
            color: #475569;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            min-height: 40px;
        }
        .sidebar-item:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .sidebar-item:hover svg {
            color: #0f172a;
        }
        .sidebar-item.active { 
            background-color: var(--color-brand-hex, #22449E);
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(var(--color-brand), 0.25);
            font-weight: 700;
        }
        .sidebar-item.active svg {
            color: #ffffff;
        }
        .sidebar-item.active .item-badge {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border-color: transparent !important;
        }
        .sidebar-item.active .item-dot {
            background-color: #ffffff !important;
        }
        
        .card { 
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md, 12px);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
        }
        .kpi-card { 
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md, 12px);
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .kpi-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .badge { 
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            border-radius: var(--radius-pill, 9999px);
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.025em;
        }
        
        .btn-primary { 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: var(--color-brand-hex, #22449E);
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm, 8px);
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.15s ease;
            box-shadow: 0 2px 8px rgba(var(--color-brand), 0.25);
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: var(--color-brand-hover, #1b3680);
            box-shadow: 0 4px 12px rgba(var(--color-brand), 0.35);
        }
        .btn-primary:active {
            transform: scale(0.98);
        }
        
        .btn-secondary { 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #ffffff;
            color: #334155;
            border: 1px solid #e2e8f0;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm, 8px);
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background-color: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }
        
        .btn-accent {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #f59e0b;
            color: #0f172a;
            padding: 0.5rem 1.25rem;
            border-radius: var(--radius-sm, 8px);
            font-size: 0.75rem;
            font-weight: 800;
            transition: all 0.15s ease;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
            cursor: pointer;
        }
        .btn-accent:hover {
            background-color: #d97706;
        }
        
        .btn-danger { 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            background-color: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
            padding: 0.5rem 0.875rem;
            border-radius: var(--radius-sm, 8px);
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-danger:hover {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .btn-secondary:active,
        .btn-accent:active,
        .btn-danger:active {
            transform: scale(0.98);
        }

        .btn-loading {
            position: relative;
            pointer-events: none;
            color: transparent !important;
        }
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            top: 50%;
            left: 50%;
            margin-top: -7px;
            margin-left: -7px;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            animation: btn-spin 0.6s linear infinite;
        }
        .btn-primary.btn-loading::after,
        .btn-danger.btn-loading::after {
            border-color: #ffffff;
            border-top-color: transparent;
        }
        .btn-secondary.btn-loading::after {
            border-color: #334155;
            border-top-color: transparent;
        }
        @keyframes btn-spin {
            to { transform: rotate(360deg); }
        }

        .checkbox {
            width: 1rem;
            height: 1rem;
            border-radius: 4px;
            accent-color: var(--color-brand-hex, #22449E);
            cursor: pointer;
        }
        
        .input { 
            width: 100%;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-sm, 8px);
            padding: 0.5rem 0.75rem; /* Strict 8px 12px grid */
            font-size: 0.875rem;
            color: #0f172a;
            transition: all 0.15s ease;
        }
        .input::placeholder {
            color: #64748b;
        }
        .input:focus {
            outline: none;
            border-color: var(--color-brand-hex, #22449E);
            box-shadow: 0 0 0 3px rgba(var(--color-brand), 0.12);
        }
        .input-error {
            border-color: #f87171 !important;
        }
        .input-error:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }

        /* Standardized Input Sizing Scale */
        .input-sm {
            padding: 0.375rem 0.625rem;
            font-size: 0.75rem;
            border-radius: var(--radius-sm, 8px);
        }
        .input-md {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border-radius: var(--radius-md, 12px);
        }

        /* Standardized Table Header Token */
        .table-th {
            font-size: 0.6875rem; /* 11px */
            line-height: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b; /* slate-500 for WCAG AA >= 4.5:1 contrast on table surface */
            padding: 0.75rem 1rem;
        }

        /* Micro-typography badge token */
        .text-2xs {
            font-size: 10px;
            line-height: 14px;
        }

        /* Core Component States (WCAG 2.4.7 Focus & Form Feedback) */
        .btn-primary:focus-visible,
        .btn-secondary:focus-visible,
        .btn-accent:focus-visible,
        .btn-danger:focus-visible,
        .input:focus-visible {
            outline: 2px solid var(--color-brand-hex, #22449E);
            outline-offset: 2px;
        }

        .btn-primary:disabled,
        .btn-secondary:disabled,
        .btn-accent:disabled,
        .btn-danger:disabled,
        .input:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        .label { 
            display: block;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.375rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .table-row { 
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.15s ease;
        }
        .table-row:hover {
            background-color: #f8fafc;
        }
        
        .section-title { 
            font-size: 0.9375rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Modern Subtle Scrollbars */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    @yield('styles')
</head>
<body class="bg-canvas text-slate-800 h-full antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    <!-- ======================== SIDEBAR (PRISTINE WHITE) ======================== -->
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col transition-transform duration-300 lg:relative lg:translate-x-0 shadow-sm lg:shadow-none"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        <!-- Brand Header (Enterprise WiFiPads NAC Controller) -->
        <div class="h-16 px-5 border-b border-slate-200/80 flex items-center justify-between bg-white shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center text-white shadow-xs group-hover:bg-brand-hover transition-colors">
                    <!-- Geometric Screen Signal Logo -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="13" rx="2.5"/>
                        <path d="M8 20h8"/>
                        <path d="M12 17v3"/>
                        <path d="M7 11c1.5-1.5 3-2 5-2s3.5.5 5 2"/>
                        <circle cx="12" cy="13" r="1" fill="currentColor"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-base font-extrabold tracking-tight text-slate-900 leading-none">WiFi<span class="text-brand">Pads</span></span>
                    <span class="text-2xs font-bold text-slate-500 tracking-wider uppercase mt-1">NAC Controller</span>
                </div>
            </a>
            <button type="button" @click="sidebarOpen = false" aria-label="Close navigation menu" class="lg:hidden w-11 h-11 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Navigation Menu Items: Unified Enterprise Hotspot & NAC Architecture -->
        <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto">
            @php
                $activeSite = $currentTenantSite ?? null;
            @endphp

            <!-- Active Site Context Card -->
            <div class="mb-3 px-0.5">
                <div class="p-2.5 rounded-xl bg-slate-50/90 border border-slate-200/80 transition-all hover:bg-slate-50">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <span class="text-2xs font-extrabold uppercase tracking-wider text-slate-500">Site Scope</span>
                        @if($activeSite)
                            <span class="inline-flex items-center gap-1.5 text-2xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Online
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-2xs font-bold text-brand bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                                Global NOC
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-white border border-slate-200/90 shadow-2xs flex items-center justify-center text-slate-600 shrink-0">
                            @if($activeSite)
                                <svg class="w-3.5 h-3.5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-900 truncate">
                                {{ $activeSite ? $activeSite->name : 'All Locations (Global)' }}
                            </div>
                            <div class="text-2xs text-slate-500 font-mono truncate">
                                {{ $activeSite ? ($activeSite->router_ip ?: 'Cloud Standalone Router') : 'Centralized NOC Monitor' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: SITE OPERATIONS -->
            <div class="px-2 pt-1.5 pb-1 text-2xs font-extrabold uppercase tracking-wider text-slate-500">Site Operations</div>

            <!-- Item 1: Overview & Status (All Roles) -->
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                </svg>
                <span class="truncate">Overview & Health</span>
            </a>

            <!-- Item 2: Captive Portal Studio (Site Admin & Superadmin) -->
            @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isSiteAdmin())
            @if($activeSite)
            <a href="{{ route('admin.sites.template.customizer', $activeSite) }}" class="sidebar-item {{ request()->routeIs('admin.sites.template*') || request()->routeIs('admin.templates*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.39m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                </svg>
                <span class="truncate">Portal Studio</span>
            </a>
            @else
            <a href="{{ route('admin.templates.index') }}" class="sidebar-item {{ request()->routeIs('admin.templates*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.39m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                </svg>
                <span class="truncate">Portal Studio</span>
            </a>
            @endif

            <!-- Item 3: Bandwidth & QoS Profiles (Site Admin & Superadmin) -->
            <a href="{{ route('admin.profiles.index') }}" class="sidebar-item {{ request()->routeIs('admin.profiles*') || request()->routeIs('admin.policy.bindings*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                </svg>
                <span class="truncate">Bandwidth Profiles</span>
            </a>
            @endif

            <!-- Item 4: Hotspot Users & Vouchers (Cashier, Operator, Site Admin, Superadmin) -->
            @if(in_array(auth()->user()?->role, ['superadmin', 'site_admin', 'operator', 'cashier']))
            <a href="{{ route('admin.hotspot-users.index') }}" class="sidebar-item {{ request()->routeIs('admin.hotspot-users*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
                </svg>
                <span class="truncate">{{ auth()->user()?->isCashier() ? 'Voucher Desk' : 'Hotspot Users' }}</span>
            </a>
            @endif

            <!-- Item 5: Edge Gateway & RADIUS (Site Admin & Superadmin) -->
            @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isSiteAdmin())
            <a href="{{ route('admin.radius.index') }}" class="sidebar-item {{ request()->routeIs('admin.radius*') || request()->routeIs('admin.sites.radius*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.75 5.1a1.5 1.5 0 011.2-.6h10.1a1.5 1.5 0 011.2.6l2.1 3.45a4.5 4.5 0 01.9 2.7M6.75 17.25h.008v.008H6.75v-.008zm3 0h.008v.008H9.75v-.008z"/>
                </svg>
                <span class="truncate">Gateway & RADIUS</span>
            </a>
            @endif

            <!-- Item 6: Live Sessions & Monitoring (Operator, Site Admin, Superadmin) -->
            @if(in_array(auth()->user()?->role, ['superadmin', 'site_admin', 'operator']))
            <a href="{{ route('admin.devices.index') }}" class="sidebar-item {{ request()->routeIs('admin.devices*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/>
                </svg>
                <span class="truncate">Live Sessions</span>
            </a>
            @endif

            <!-- SECTION: CAMPAIGNS & MARKETING -->
            @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isAdvertiser())
            <div class="px-2 pt-4 pb-1 text-2xs font-extrabold uppercase tracking-wider text-slate-500">Marketing & Ads</div>

            <a href="{{ route('admin.campaigns.index') }}" class="sidebar-item {{ request()->routeIs('admin.campaigns*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.455a20.01 20.01 0 01-1.378-3.929m3.04-9.47c.253-.962.584-1.892.985-2.783.247-.55.06-1.21-.463-1.511l-.657-.38c-.551-.318-1.26-.117-1.527.455a20.01 20.01 0 00-1.378 3.929m12.39 4.887a1.5 1.5 0 000-2.828M15 7.5v9"/>
                </svg>
                <span class="truncate">Campaigns & Surveys</span>
            </a>
            @endif

            <!-- SECTION 2: GLOBAL MANAGEMENT (NOC) -->
            @if(auth()->user()?->isSuperadmin())
            <div class="px-2 pt-4 pb-1 text-2xs font-extrabold uppercase tracking-wider text-slate-500">Multi-Site & NOC</div>

            <a href="{{ route('admin.sites.index') }}" class="sidebar-item {{ request()->routeIs('admin.sites.index*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a1.5 1.5 0 011.5-1.5h1.5a1.5 1.5 0 011.5 1.5V21m6-9h.75m-.75 3h.75m-.75 3h.75"/>
                </svg>
                <span class="truncate">Sites Directory</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="sidebar-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                <span class="truncate">Operator Access</span>
            </a>

            <a href="{{ route('admin.analytics.index') }}" class="sidebar-item {{ request()->routeIs('admin.analytics.index') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                <span class="truncate">Global Analytics</span>
            </a>

            <a href="{{ route('admin.analytics.duration') }}" class="sidebar-item {{ request()->routeIs('admin.analytics.duration*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="truncate">Duration & Forensik</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 ml-auto item-badge">Audit</span>
            </a>

            <a href="{{ route('portal') }}?preview=1" target="_blank" class="sidebar-item">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                <span class="truncate">Live Portal (Preview)</span>
            </a>

            <a href="{{ route('admin.docs.index') }}" class="sidebar-item {{ request()->routeIs('admin.docs*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
                <span class="truncate">API & Documentation</span>
            </a>
            @endif
        </nav>

        <!-- User Profile Card in Sidebar Footer (Streamlined Enterprise Row) -->
        <div class="p-3 border-t border-slate-200/80 bg-slate-50/70 shrink-0">
            <div class="flex items-center justify-between gap-2.5">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-brand text-white flex items-center justify-center text-xs font-black shadow-2xs shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-900 truncate leading-snug">{{ auth()->user()?->name }}</div>
                        <div class="text-[10px] text-slate-500 capitalize leading-tight flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="truncate">{{ auth()->user()?->role ?? 'Operator' }}</span>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="Sign Out" aria-label="Sign Out" class="w-8 h-8 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-500 flex items-center justify-center transition-all cursor-pointer focus-visible:ring-2 focus-visible:ring-rose-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Sidebar overlay (mobile) -->
    <div
        x-show="sidebarOpen"
        x-cloak
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/40 backdrop-blur-xs z-40 lg:hidden"
    ></div>

    <!-- ======================== MAIN CONTENT CANVAS ======================== -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Bar Header (Pristine White Surface with SMARTIV Quick Actions) -->
        <header class="h-16 flex items-center justify-between px-6 border-b border-slate-200/80 bg-white/95 backdrop-blur-md flex-shrink-0 z-10">
            <div class="flex items-center gap-4">
                <!-- Mobile hamburger button (WCAG 44x44px target) -->
                <button type="button" @click="sidebarOpen = !sidebarOpen" aria-label="Open navigation menu" class="lg:hidden w-11 h-11 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <!-- Personalized Greeting (SMARTIV DNA) -->
                <div>
                    <h1 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Hi {{ auth()->user()?->name ? explode(' ', auth()->user()->name)[0] : 'Admin' }} Team — {{ isset($currentTenantSite) && $currentTenantSite ? $currentTenantSite->name : 'NOC Global' }}!</span>
                    </h1>
                    <div class="text-xs text-slate-500 font-medium">@yield('page-title', 'Overview')</div>
                </div>
            </div>

            <!-- Header Quick Actions & Tenant Context Switcher -->
            <div class="flex items-center gap-3">
                @if(isset($availableTenantSites) && $availableTenantSites->count() > 0)
                <!-- Tenant Context Switcher (Impersonation / Switch Account) -->
                <form method="POST" action="{{ route('admin.context.switch') }}" class="flex items-center">
                    @csrf
                    <div class="relative flex items-center">
                        <div class="absolute left-2.5 pointer-events-none text-slate-400">
                            <span class="w-2 h-2 rounded-full inline-block {{ isset($currentTenantSite) && $currentTenantSite ? 'bg-emerald-500' : 'bg-brand' }}"></span>
                        </div>
                        <select name="site_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl pl-7 pr-8 py-1.5 text-xs text-slate-700 font-semibold focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/15 cursor-pointer hover:border-slate-300 transition-all appearance-none shadow-xs">
                            <option value="all" {{ !isset($currentTenantSite) || !$currentTenantSite ? 'selected' : '' }}>
                                All Sites (Global NOC)
                            </option>
                            @foreach($availableTenantSites as $site)
                            <option value="{{ $site->id }}" {{ isset($currentTenantSite) && $currentTenantSite && $currentTenantSite->id === $site->id ? 'selected' : '' }}>
                                Site: {{ $site->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="absolute right-2.5 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </form>
                @endif

                <!-- Status / Emergency Pill -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 border border-blue-200/60 text-brand text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Online</span>
                </div>

                <!-- Notification Bell (Semantic Button with Accessible Name) -->
                <button type="button" aria-label="View system notifications" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none cursor-pointer relative transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-2 right-2 w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                </button>

                <!-- User Profile Avatar Pill -->
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-brand text-white flex items-center justify-center text-xs font-extrabold shadow-sm">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                    </div>
                </div>

                @yield('header-actions')
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="mx-6 mt-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center gap-2 shadow-xs">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
        <div class="mx-6 mt-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold shadow-xs">
            @if(session('error')){{ session('error') }}@endif
            @if(isset($errors) && $errors->any())
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            @endif
        </div>
        @endif

        <!-- Main Workspace Area -->
        <main class="flex-1 overflow-y-auto p-6 bg-canvas">
            @yield('content')
        </main>
    </div>
</div>

@yield('scripts')
@stack('scripts')
</body>
</html>
