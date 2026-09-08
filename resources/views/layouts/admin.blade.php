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

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            border-radius: var(--radius-md, 12px);
            color: #475569;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }
        .sidebar-item:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .sidebar-item.active { 
            background-color: var(--color-brand-hex, #22449E);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(var(--color-brand), 0.25);
            font-weight: 700;
        }
        .sidebar-item.active svg {
            color: #ffffff;
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
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-sm, 8px);
            font-size: 0.6875rem;
            font-weight: 700;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-danger:hover {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .input { 
            width: 100%;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-sm, 8px);
            padding: 0.625rem 0.875rem;
            font-size: 0.8125rem;
            color: #0f172a;
            transition: all 0.15s ease;
        }
        .input::placeholder {
            color: #94a3b8;
        }
        .input:focus {
            outline: none;
            border-color: var(--color-brand-hex, #22449E);
            box-shadow: 0 0 0 3px rgba(var(--color-brand), 0.12);
        }

        /* Standardized Input Sizing Scale */
        .input-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            border-radius: var(--radius-sm, 8px);
        }
        .input-md {
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            border-radius: var(--radius-md, 12px);
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
<body class="bg-[#F4F7FC] text-slate-800 h-full antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    <!-- ======================== SIDEBAR (PRISTINE WHITE) ======================== -->
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col transition-transform duration-300 lg:relative lg:translate-x-0 shadow-sm lg:shadow-none"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        <!-- Brand Header (Keeping WiFiPads brand name with geometric signal DNA) -->
        <div class="h-16 px-6 border-b border-slate-100 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-brand flex items-center justify-center text-white shadow-sm">
                    <!-- Geometric Screen Signal Logo -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor"/>
                        <path d="M7 19l2 2m8-2l-2 2m-6-2h6" stroke-linecap="round"/>
                        <path d="M7 11h.01M10 11a2 2 0 012-2M15 11a5 5 0 00-5-5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <span class="text-base font-extrabold tracking-tight text-[#0F172A]">WiFi<span class="text-brand">Pads</span></span>
                    <span class="block text-2xs uppercase tracking-wider font-bold text-slate-500 -mt-1">NAC Controller</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Navigation Menu Items (Professional English Enterprise Terminology) -->
        <nav class="flex-1 px-3.5 py-4 space-y-1 overflow-y-auto">
            <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider px-3 mb-1.5 mt-1">MAIN MENU</div>

            <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.sites.index') }}" class="sidebar-item {{ request()->routeIs('admin.sites*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Sites & Locations
            </a>

            <a href="{{ route('admin.devices.index') }}" class="sidebar-item {{ request()->routeIs('admin.devices.index') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Connected Devices
            </a>

            <a href="{{ route('admin.devices.monitoring') }}" class="sidebar-item {{ request()->routeIs('admin.devices.monitoring*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Traffic & Monitoring
            </a>

            <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider px-3 mb-1.5 mt-4">NETWORK OPERATIONS</div>

            <a href="{{ route('admin.policy.bindings') }}" class="sidebar-item {{ request()->routeIs('admin.policy.bindings*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Layer-2 MAC Policies
            </a>

            <a href="{{ route('admin.profiles.index') }}" class="sidebar-item {{ request()->routeIs('admin.profiles*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Bandwidth Profiles (QoS)
            </a>

            <a href="{{ route('admin.ap.index') }}" class="sidebar-item {{ request()->routeIs('admin.ap*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                </svg>
                AP Watchdog
            </a>

            <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider px-3 mb-1.5 mt-4">MARKETING & ENGAGEMENT</div>

            <a href="{{ route('admin.campaigns.index') }}" class="sidebar-item {{ request()->routeIs('admin.campaigns*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Campaigns & Surveys
            </a>

            <a href="{{ route('admin.vouchers.index') }}" class="sidebar-item {{ request()->routeIs('admin.vouchers*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Voucher Management
            </a>

            <a href="{{ route('admin.members.index') }}" class="sidebar-item {{ request()->routeIs('admin.members*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Member & Staff Accounts
            </a>

            <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider px-3 mb-1.5 mt-4">SYSTEM & ANALYTICS</div>

            <a href="{{ route('admin.analytics.index') }}" class="sidebar-item {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Analytics & Reports
            </a>

            @if(auth()->user()?->isSuperadmin())
            <a href="{{ route('admin.locations.index') }}" class="sidebar-item {{ request()->routeIs('admin.locations*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                </svg>
                Edge Gateways & Hardware
            </a>

            <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider px-3 mb-1.5 mt-4">PORTAL & TEMPLATES</div>

            <a href="{{ route('admin.templates.index') }}" class="sidebar-item {{ request()->routeIs('admin.templates*') || request()->routeIs('admin.sites.template*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/>
                </svg>
                Template Studio
                <span class="ml-auto px-1.5 py-0.5 text-2xs font-extrabold bg-brand/10 text-brand rounded-md">6 Methods</span>
            </a>

            <a href="{{ route('portal') }}?preview=1" target="_blank" class="sidebar-item">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Live Portal (Preview)
            </a>
            @endif

            <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider px-3 mb-1.5 mt-4">DEVELOPER & INTEGRATIONS</div>

            <a href="{{ route('admin.docs.index') }}" class="sidebar-item {{ request()->routeIs('admin.docs*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Integration Docs & API
                <span class="ml-auto px-1.5 py-0.5 text-2xs font-extrabold bg-emerald-500/10 text-emerald-700 rounded-md">PDF</span>
            </a>
        </nav>

        <!-- User Profile Card in Sidebar Footer -->
        <div class="p-3.5 border-t border-slate-100 bg-slate-50/60">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="w-9 h-9 rounded-xl bg-brand text-white flex items-center justify-center text-xs font-bold shadow-sm">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()?->name }}</div>
                    <div class="text-xs text-slate-500 capitalize">{{ auth()->user()?->role }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full btn-secondary text-xs py-1.5 justify-center">
                    Sign Out
                </button>
            </form>
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
                <!-- Mobile hamburger button -->
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500 hover:text-slate-800 p-1">
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
                                🌐 All Sites (Global NOC)
                            </option>
                            @foreach($availableTenantSites as $site)
                            <option value="{{ $site->id }}" {{ isset($currentTenantSite) && $currentTenantSite && $currentTenantSite->id === $site->id ? 'selected' : '' }}>
                                📍 {{ $site->name }}
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

                <!-- Notification Bell -->
                <div class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-center text-slate-600 hover:text-slate-900 cursor-pointer relative transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                </div>

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
        <main class="flex-1 overflow-y-auto p-6 bg-[#F4F7FC]">
            @yield('content')
        </main>
    </div>
</div>

@yield('scripts')
</body>
</html>
