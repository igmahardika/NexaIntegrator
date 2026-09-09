<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — WiFiPads NAC Controller</title>
    <meta name="description" content="WiFiPads — Multi-Tenant Network Access Control & Edge Gateway Controller">

    <!-- Google Fonts: Plus Jakarta Sans + Inter -->
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
                            600: '#22449E',
                            700: '#1b3680',
                            800: '#152a63',
                            900: '#0f1d45',
                        },
                        canvas: '#F4F7FC',
                        surface: '#FFFFFF'
                    },
                    fontSize: {
                        '2xs': ['10px', '14px'],
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; 
            background-color: #F4F7FC;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-canvas flex items-center justify-center p-4 sm:p-6 lg:p-8">

    <!-- Main Container (Split Screen) -->
    <div class="w-full max-w-5xl bg-surface rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">
        
        <!-- ======================= LEFT HERO / PREVIEW BANNER ======================= -->
        <div class="hidden lg:flex lg:col-span-5 bg-brand p-8 flex-col justify-between relative overflow-hidden text-white m-3 rounded-2xl">
            <!-- Subtle Radial Gradient Glow -->
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-indigo-900/40 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Header & Carousel Indicator -->
            <div class="relative z-10 text-center pt-4">
                <h2 class="text-2xl font-extrabold tracking-tight text-white">Get Started Now</h2>
                <p class="text-xs text-blue-100/90 mt-1.5 max-w-xs mx-auto font-normal">
                    Our platform is designed to be user-friendly, responsive, and easy to navigate.
                </p>

                <!-- Carousel Dots -->
                <div class="flex items-center justify-center gap-1.5 mt-4" aria-hidden="true">
                    <span class="w-6 h-1.5 rounded-full bg-white"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                </div>
            </div>

            <!-- Inner App Mockup Card Preview (SMARTIV DNA) -->
            <div class="relative z-10 mt-6 bg-white rounded-2xl p-4 text-slate-800 shadow-2xl shadow-black/20 border border-white/20 transform translate-y-2">
                <!-- Mini Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-brand flex items-center justify-center text-white">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor"/>
                                <path d="M7 19l2 2m8-2l-2 2m-6-2h6" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="text-xs font-extrabold text-slate-900">WiFi<span class="text-brand">Pads</span></span>
                    </div>
                    <div class="text-xs font-bold text-slate-700 flex items-center gap-1">
                        Halo, Admin 👋
                    </div>
                </div>

                <!-- Mini Content -->
                <div class="pt-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-extrabold text-slate-900">Guest & Device Control</div>
                            <div class="text-2xs text-slate-500 font-medium">Monitor live connected sessions</div>
                        </div>
                        <span class="text-2xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ● Active
                        </span>
                    </div>

                    <!-- Mini Search Bar -->
                    <div class="mt-2.5 bg-slate-50 border border-slate-200/80 rounded-lg px-2.5 py-1.5 flex items-center gap-1.5 text-2xs text-slate-500">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Search client / MAC...</span>
                    </div>

                    <!-- Mini List Rows -->
                    <div class="mt-2 space-y-1.5">
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100 text-2xs">
                            <span class="font-bold text-brand px-1.5 py-0.5 rounded bg-blue-50">L2-POS</span>
                            <span class="font-semibold text-slate-700 truncate">Kasir POS Tablet</span>
                            <span class="text-2xs text-emerald-700 font-bold">Bypassed</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-100 text-2xs">
                            <span class="font-bold text-indigo-600 px-1.5 py-0.5 rounded bg-indigo-50">GUEST</span>
                            <span class="font-semibold text-slate-700 truncate">iPhone 15 Pro</span>
                            <span class="text-2xs text-indigo-600 font-mono">5M/10M</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Brand Badge -->
            <div class="relative z-10 pt-4 text-center">
                <span class="text-2xs uppercase font-bold tracking-widest text-blue-200/90">Next-Gen Network Access Control</span>
            </div>
            </div>
        </div>

        <!-- ======================= RIGHT LOGIN FORM ======================= -->
        <div class="lg:col-span-7 p-8 sm:p-12 lg:p-14 flex flex-col justify-between" x-data="{ loading: false, showPass: false }">
            
            <!-- Brand Logo at Top -->
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 mb-8 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none rounded-xl">
                    <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center text-white shadow-sm shadow-brand/30">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor"/>
                            <path d="M7 19l2 2m8-2l-2 2m-6-2h6" stroke-linecap="round"/>
                            <path d="M7 11h.01M10 11a2 2 0 012-2M15 11a5 5 0 00-5-5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight text-slate-900">WiFi<span class="text-brand">Pads</span></span>
                    </div>
                </a>

                <!-- Title & Subtitle -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Get started now</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5 font-medium">
                        Please enter your credentials to access your controller account.
                    </p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('login.post') }}" @submit="loading = true" class="space-y-4">
                    @csrf

                    <!-- Email Field with Icon -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Email</label>
                        <div class="relative flex items-center">
                            <div class="absolute left-3.5 pointer-events-none text-slate-400" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', 'admin@wifipads.com') }}"
                                autocomplete="email"
                                required
                                autofocus
                                placeholder="name@domain.com"
                                @if(isset($errors) && $errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                                class="w-full bg-white border {{ (isset($errors) && $errors->has('email')) ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 focus:border-brand focus:ring-brand/15' }} rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 transition-all shadow-2xs"
                            >
                        </div>
                        @if(isset($errors) && $errors->has('email'))
                        <p id="email-error" class="text-rose-600 text-xs mt-1 font-semibold">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <!-- Password Field with Lock & Eye Toggle -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password</label>
                        <div class="relative flex items-center">
                            <div class="absolute left-3.5 pointer-events-none text-slate-400" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                :type="showPass ? 'text' : 'password'"
                                name="password"
                                id="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••••••"
                                class="w-full bg-white border border-slate-200 focus:border-brand rounded-xl pl-10 pr-12 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand/15 transition-all shadow-2xs"
                            >
                            <!-- Password visibility toggle with compliant 40x40px touch target & visible focus -->
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="absolute right-1 w-10 h-10 flex items-center justify-center text-slate-500 hover:text-slate-700 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none rounded-lg transition-colors"
                                :aria-label="showPass ? 'Sembunyikan password' : 'Lihat password'"
                            >
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                checked
                                class="w-4 h-4 rounded text-brand focus:ring-brand border-slate-300"
                            >
                            <span class="text-xs font-semibold text-slate-700">Remember Me</span>
                        </label>
                        <a href="#" class="text-xs font-semibold text-brand hover:text-brand-hover hover:underline focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none rounded">
                            Forgot Password?
                        </a>
                    </div>

                    <!-- Login Button with Reactive Loading Spinner -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            id="login-btn"
                            :disabled="loading"
                            class="w-full bg-brand hover:bg-brand-hover focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 focus-visible:outline-none disabled:opacity-60 disabled:cursor-not-allowed disabled:pointer-events-none text-white py-3 rounded-xl font-bold text-sm transition-all duration-200 shadow-md shadow-brand/25 hover:shadow-lg hover:shadow-brand/35 active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2"
                        >
                            <svg x-show="loading" x-cloak class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loading ? 'Memverifikasi...' : 'Login'">Login</span>
                        </button>
                    </div>
                </form>

                <!-- Footer Hint -->
                <div class="mt-6 text-center text-xs text-slate-600">
                    Don't have an account? <a href="#" class="text-brand font-bold hover:underline focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none rounded">Register Now</a>
                </div>
            </div>

            <!-- Copyright Footer with WCAG AA Compliant Contrast -->
            <div class="pt-8 text-center text-xs text-slate-500 font-medium">
                &copy; {{ date('Y') }} WiFiPads. All Rights Reserved.
            </div>
        </div>

    </div>

    <!-- Alpine.js script for eye toggle -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
