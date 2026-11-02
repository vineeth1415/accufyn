<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebotApp Accounting By Webotapp - Installation Guide</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        neon: '#39FF14',
                        brand: '#0f172a',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .gradient-border {
            background: linear-gradient(135deg, rgba(57, 255, 20, 0.4), rgba(255, 255, 255, 0.05), rgba(57, 255, 20, 0.2));
        }
    </style>
</head>
<body class="min-h-full bg-[#090d16] text-slate-100 flex flex-col justify-between selection:bg-neon selection:text-black">

    <!-- Top Glow Background -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[350px] bg-neon/10 rounded-full blur-[140px] pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 right-0 w-[500px] h-[300px] bg-blue-500/10 rounded-full blur-[120px] pointer-events-none -z-10"></div>

    <!-- Top Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-neon/30 flex items-center justify-center shadow-lg shadow-neon/10">
                    <span class="text-neon font-black text-xl">E</span>
                </div>
                <div>
                    <h1 class="text-base font-black tracking-tight text-white flex items-center gap-2">
                        WebotApp Accounting <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full bg-neon/10 text-neon border border-neon/30">Installer</span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium">Accounting Platform Setup</p>
                </div>
            </div>

            <!-- WhatsApp Quick Help Pill -->
            <a href="{{ $whatsappLink }}" target="_blank" rel="noreferrer" class="group flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 hover:bg-emerald-900/90 transition-all text-xs font-semibold shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Need setup help?</span>
                <span class="text-white font-bold group-hover:underline">WhatsApp Us</span>
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-8 md:py-12">

        <!-- 🌟 EXPLICIT WHATSAPP INSTALLATION SERVICE BANNER 🌟 -->
        <div class="mb-8 rounded-2xl bg-gradient-to-r from-emerald-950/80 via-slate-900/90 to-emerald-950/80 border border-emerald-500/30 p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center shrink-0 text-emerald-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.158.572 4.187 1.571 5.945l-1.668 6.096 6.275-1.644c1.701.929 3.652 1.451 5.722 1.451 6.627 0 12-5.373 12-12s-5.373-12-12-12z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm sm:text-base">Problems in installing? Message WhatsApp: <span class="text-emerald-400 font-mono">+91 7002484119</span></h3>
                    <p class="text-xs text-slate-300 mt-0.5">We do complete installation, server setup, and configuration at as low as <strong class="text-neon font-black">at min price</strong>.</p>
                </div>
            </div>
            <a href="{{ $whatsappLink }}" target="_blank" rel="noreferrer" class="shrink-0 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black font-extrabold text-xs uppercase tracking-wider transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                Chat on WhatsApp
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <!-- Wizard Card -->
        <div class="bg-slate-900/90 rounded-3xl border border-slate-800 shadow-2xl overflow-hidden backdrop-blur-xl">

            <!-- Step Indicator Bar -->
            <div class="border-b border-slate-800/80 bg-slate-950/40 p-4 sm:p-6">
                <div class="grid grid-cols-4 gap-2 sm:gap-3 text-center">
                    <!-- Step 1: Requirements -->
                    <div id="tab-step-0" class="step-tab flex flex-col sm:flex-row items-center justify-center gap-2 p-2.5 sm:p-3 rounded-2xl bg-slate-800/80 border border-neon/40 text-neon transition-all">
                        <span class="w-6 h-6 rounded-full bg-neon text-black text-xs font-black flex items-center justify-center shrink-0">1</span>
                        <span class="text-xs font-bold text-white truncate">Requirements</span>
                    </div>

                    <!-- Step 2: License -->
                    <div id="tab-step-1" class="step-tab flex flex-col sm:flex-row items-center justify-center gap-2 p-2.5 sm:p-3 rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 transition-all">
                        <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 text-xs font-bold flex items-center justify-center shrink-0">2</span>
                        <span class="text-xs font-bold text-slate-400 truncate">License</span>
                    </div>

                    <!-- Step 3: Database -->
                    <div id="tab-step-2" class="step-tab flex flex-col sm:flex-row items-center justify-center gap-2 p-2.5 sm:p-3 rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 transition-all">
                        <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 text-xs font-bold flex items-center justify-center shrink-0">3</span>
                        <span class="text-xs font-bold text-slate-400 truncate">Database</span>
                    </div>

                    <!-- Step 4: Finished -->
                    <div id="tab-step-3" class="step-tab flex flex-col sm:flex-row items-center justify-center gap-2 p-2.5 sm:p-3 rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 transition-all">
                        <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 text-xs font-bold flex items-center justify-center shrink-0">4</span>
                        <span class="text-xs font-bold text-slate-400 truncate">Finished</span>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════
                 STEP 1: SYSTEM REQUIREMENTS CHECK
            ══════════════════════════════════════════ -->
            <div id="step-0-content" class="step-content p-6 sm:p-10 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/80 pb-4">
                    <div>
                        <h2 class="text-xl font-black text-white flex items-center gap-2">
                            <span>1. System Requirements & Directory Permissions</span>
                            @if($allPassed)
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] uppercase tracking-wider font-bold">All Passed</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] uppercase tracking-wider font-bold">Action Needed</span>
                            @endif
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1">
                            Verify that your hosting environment meets the required PHP version, PHP extensions, and writable folders.
                        </p>
                    </div>
                    <a href="{{ url('/install') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold shrink-0 border border-slate-700 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Re-check
                    </a>
                </div>

                <!-- PHP Version Box -->
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-blue-400 font-bold text-xs">
                            PHP
                        </div>
                        <div>
                            <span class="text-xs font-bold text-white block">PHP Version</span>
                            <span class="text-[11px] text-slate-400">Required: PHP &gt;= 8.2 &bull; Current: <strong class="font-mono text-slate-200">{{ $requirements['php_version'] }}</strong></span>
                        </div>
                    </div>
                    @if($requirements['list']['PHP >= 8.2'])
                        <span class="px-3 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Supported
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 font-bold text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Upgrade to PHP 8.2+
                        </span>
                    @endif
                </div>

                <!-- PHP Extensions Grid -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Required PHP Extensions</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                        @foreach($requirements['list'] as $reqName => $passed)
                            @if($reqName !== 'PHP >= 8.2')
                            <div class="p-3 rounded-xl bg-slate-950 border {{ $passed ? 'border-slate-800' : 'border-red-500/40 bg-red-950/20' }} flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-300">{{ $reqName }}</span>
                                @if($passed)
                                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">✓</span>
                                @else
                                    <span class="w-5 h-5 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center font-bold text-xs shrink-0">✕</span>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Directory Permissions Grid -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Directory Permissions</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($permissions['list'] as $dir => $isWritable)
                            <div class="p-3 rounded-xl bg-slate-950 border {{ $isWritable ? 'border-slate-800' : 'border-red-500/40 bg-red-950/20' }} flex items-center justify-between text-xs">
                                <span class="font-mono text-slate-300">{{ $dir }}</span>
                                @if($isWritable)
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold border border-emerald-500/30">Writable</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-red-500/10 text-red-400 text-[10px] font-bold border border-red-500/30">Not Writable</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Step 0 Action Buttons -->
                <div class="pt-4">
                    @if($allPassed)
                        <button type="button" id="btn-next-step-1" class="w-full py-4 px-6 rounded-2xl bg-neon hover:bg-neon/90 text-black font-black uppercase tracking-widest text-xs sm:text-sm flex items-center justify-center gap-2 shadow-xl shadow-neon/20 transition-all cursor-pointer">
                            <span>System Requirements Verified &bull; Continue to Step 2</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    @else
                        <div class="space-y-3">
                            <div class="p-3 rounded-xl bg-amber-950/40 border border-amber-500/30 text-amber-300 text-xs">
                                Some requirements or permissions are not fulfilled. Please fix permissions or enable extensions in your hosting control panel (cPanel / aaPanel / Hostinger).
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="{{ url('/install') }}" class="sm:w-1/2 py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider text-center border border-slate-700">
                                    Re-check System
                                </a>
                                <button type="button" id="btn-next-step-1" class="sm:w-1/2 py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer">
                                    <span>Continue Anyway</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ══════════════════════════════════════════
                 STEP 2: CUSTOMER DETAILS & PURCHASE CODE
            ══════════════════════════════════════════ -->
            <div id="step-1-content" class="step-content hidden p-6 sm:p-10 space-y-6">
                <div>
                    <h2 class="text-xl font-black text-white">2. Customer Details & Purchase Code</h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Enter your registration details and the purchase code provided with your WebotApp Accounting download.
                    </p>
                </div>

                <div id="step-1-alert" class="hidden p-4 rounded-xl text-xs font-medium border"></div>

                <form id="customer-form" class="space-y-4">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Full Name <span class="text-neon">*</span>
                        </label>
                        <input type="text" name="name" id="name" required placeholder="e.g. John Doe" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 transition-all font-medium">
                    </div>

                    <!-- Phone with Country Code Dropdown -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Valid Phone Number <span class="text-neon">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <!-- Country Code Dropdown -->
                            <div class="sm:col-span-5">
                                <select name="phone_country_code" id="phone_country_code" required class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-3 text-xs sm:text-sm text-white focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 font-medium">
                                    @foreach($countries as $c)
                                        <option value="{{ $c['code'] }}" {{ $c['code'] === '+91' ? 'selected' : '' }}>
                                            {{ $c['flag'] }} {{ $c['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Phone Digits -->
                            <div class="sm:col-span-7">
                                <input type="tel" name="phone_number" id="phone_number" required placeholder="e.g. 9854209873" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 transition-all font-mono font-medium">
                            </div>
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Email Address <span class="text-neon">*</span>
                        </label>
                        <input type="email" name="email" id="email" required placeholder="e.g. yourname@example.com" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 transition-all font-medium">
                    </div>

                    <!-- Optional Fields Row: Profession, Country, City -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                        <!-- Profession (Optional) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Profession <span class="text-slate-600 font-normal lowercase">(optional)</span>
                            </label>
                            <input type="text" name="profession" id="profession" placeholder="e.g. Accounting Broker" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-slate-500 font-medium">
                        </div>

                        <!-- Country (Optional) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Country <span class="text-slate-600 font-normal lowercase">(optional)</span>
                            </label>
                            <input type="text" name="country" id="country" placeholder="e.g. India" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-slate-500 font-medium">
                        </div>

                        <!-- City (Optional) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                                City <span class="text-slate-600 font-normal lowercase">(optional)</span>
                            </label>
                            <input type="text" name="city" id="city" placeholder="e.g. Guwahati" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-slate-500 font-medium">
                        </div>
                    </div>

                    <!-- Purchase Code Field -->
                    <div class="pt-3">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span>Purchase Code <span class="text-neon">*</span></span>
                            <span class="text-[10px] text-slate-500 font-normal normal-case">Format: WEBOT-REAL-XXXX-XXXX-XXXX</span>
                        </label>
                        <input type="text" name="purchase_code" id="purchase_code" required placeholder="WEBOT-REAL-2026-ESTATE-PASS" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-neon font-mono uppercase tracking-wider placeholder-slate-600 focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 transition-all font-bold">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between text-[11px] text-slate-500 mt-1.5 gap-1">
                            <span>Found in your <a href="https://lab.webotapp.com/dashboard?tab=products" target="_blank" class="text-neon hover:underline">Webotapp Dashboard</a> under Downloads.</span>
                            <span class="text-slate-400 font-medium">Purchased on CodeCanyon or Codester? <a href="https://lab.webotapp.com/activate" target="_blank" class="text-neon underline font-bold hover:text-white">Get License Key &rarr;</a></span>
                        </div>
                    </div>

                    <!-- Mandatory License Agreement Acceptance -->
                    <div class="pt-3 pb-1">
                        <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-700/70 space-y-2">
                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="agree_license" id="agree_license" required value="1" class="mt-1 w-4 h-4 rounded text-neon border-slate-700 bg-slate-900 focus:ring-neon focus:ring-offset-slate-950">
                                <span class="text-xs text-slate-300 leading-relaxed">
                                    I have read and agree to the <button type="button" onclick="openEulaModal()" class="text-neon underline font-semibold hover:text-white">End User License Agreement (EULA) &amp; Terms of Service</button>. I understand that this software is licensed for 1 domain, source code is delivered electronically, and all digital sales are final. <span class="text-neon">*</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Step 1 Buttons -->
                    <div class="pt-6 flex flex-col sm:flex-row gap-3">
                        <button type="button" id="btn-back-step-0" class="sm:w-1/3 py-3.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs uppercase tracking-wider transition-all border border-slate-700 flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Back to Step 1</span>
                        </button>
                        <button type="submit" id="btn-step-1" class="flex-1 py-4 px-6 rounded-2xl bg-neon hover:bg-neon/90 text-black font-black uppercase tracking-widest text-xs sm:text-sm flex items-center justify-center gap-2 shadow-xl shadow-neon/20 transition-all cursor-pointer">
                            <span>Accept License &amp; Continue</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ══════════════════════════════════════════
                 STEP 3: DATABASE CONFIGURATION
            ══════════════════════════════════════════ -->
            <div id="step-2-content" class="step-content hidden p-6 sm:p-10 space-y-6">
                <div>
                    <h2 class="text-xl font-black text-white">3. Database Details & Automatic Migration</h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Provide your MySQL database credentials. If the database does not exist, the installer will automatically create it for you.
                    </p>
                </div>

                <div id="step-2-alert" class="hidden p-4 rounded-xl text-xs font-medium border"></div>

                <!-- Verified Customer Summary Ribbon -->
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-bold block">Licensed To</span>
                        <span id="summary-customer-name" class="font-bold text-white">Customer</span> 
                        <span id="summary-customer-phone" class="text-slate-400 font-mono text-[11px] ml-1"></span>
                    </div>
                    <div>
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-bold block">Purchase Code</span>
                        <span id="summary-purchase-code" class="font-mono text-neon font-bold text-[11px]"></span>
                    </div>
                </div>

                <form id="database-form" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <!-- Host -->
                        <div class="sm:col-span-8">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Database Host <span class="text-neon">*</span>
                            </label>
                            <input type="text" name="db_host" id="db_host" required value="127.0.0.1" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 font-mono font-medium">
                        </div>

                        <!-- Port -->
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Port <span class="text-neon">*</span>
                            </label>
                            <input type="number" name="db_port" id="db_port" required value="3306" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 font-mono font-medium">
                        </div>
                    </div>

                    <!-- Database Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Database Name <span class="text-neon">*</span>
                        </label>
                        <input type="text" name="db_name" id="db_name" required value="accounting" placeholder="accounting" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 font-mono font-bold">
                        <p class="text-[11px] text-slate-500 mt-1">If this database does not exist in MySQL, it will be created automatically.</p>
                    </div>

                    <!-- Username & Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Database Username <span class="text-neon">*</span>
                            </label>
                            <input type="text" name="db_user" id="db_user" required value="root" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 font-mono font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Database Password
                            </label>
                            <input type="password" name="db_pass" id="db_pass" placeholder="(Leave blank if XAMPP root without password)" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 font-mono font-medium">
                        </div>
                    </div>

                    <!-- App URL -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Application URL
                        </label>
                        <input type="text" name="app_url" id="app_url" value="{{ url('/') }}" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-neon focus:ring-2 focus:ring-neon/20 font-mono text-xs font-medium">
                    </div>

                    <!-- Company & Super Admin Credentials -->
                    <div class="pt-4 border-t border-slate-800/80 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-neon"></span>
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Company &amp; Super Admin Credentials</h3>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Company / Business Name
                            </label>
                            <input type="text" name="company_name" id="company_name" placeholder="e.g. My Business Enterprise" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon font-medium">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                    Admin Login Email
                                </label>
                                <input type="email" name="admin_email" id="admin_email" placeholder="admin@example.com" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                    Admin Password <span class="text-neon">*</span>
                                </label>
                                <input type="password" name="admin_password" id="admin_password" value="admin123" placeholder="••••••••" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon font-mono font-medium">
                            </div>
                        </div>
                    </div>

                    <!-- Buttons Row -->
                    <div class="pt-6 flex flex-col sm:flex-row gap-3">
                        <button type="button" id="btn-back-step-1" class="sm:w-1/4 py-3.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs uppercase tracking-wider transition-all border border-slate-700 flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Back</span>
                        </button>

                        <button type="button" id="btn-test-db" class="sm:w-1/3 py-3.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider transition-all border border-slate-700 flex items-center justify-center gap-2 cursor-pointer">
                            <span>Test Connection</span>
                        </button>

                        <button type="submit" id="btn-run-install" class="flex-1 py-3.5 px-6 rounded-xl bg-neon hover:bg-neon/90 text-black font-black uppercase tracking-widest text-xs flex items-center justify-center gap-2 shadow-xl shadow-neon/20 transition-all cursor-pointer">
                            <span>Start Installation & Migrate</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ══════════════════════════════════════════
                 STEP 4: INSTALLATION PROGRESS
            ══════════════════════════════════════════ -->
            <div id="step-3-loading" class="step-content hidden p-10 sm:p-16 text-center space-y-6">
                <div class="w-20 h-20 rounded-3xl bg-neon/10 border border-neon/30 mx-auto flex items-center justify-center relative">
                    <div class="w-12 h-12 border-4 border-neon border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div>
                    <h3 class="text-2xl font-black text-white">Installing WebotApp Accounting...</h3>
                    <p class="text-sm text-slate-400 mt-2 max-w-md mx-auto">
                        Executing database migrations, generating application keys, seeding financial charts &amp; ledger accounts, and registering license. Please wait.
                    </p>
                </div>

                <div class="w-full max-w-sm mx-auto bg-slate-950 h-2 rounded-full overflow-hidden border border-slate-800">
                    <div class="bg-neon h-full w-2/3 animate-pulse"></div>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        <p>WebotApp Accounting Accounting Platform &copy; {{ date('Y') }} WebotApp. All rights reserved.</p>
        <p class="mt-1">For installation support, message WhatsApp: <strong class="text-slate-400">+91 7002484119</strong></p>
    </footer>

    <!-- Installer JavaScript Logic -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const step0Content = document.getElementById('step-0-content');
        const step1Content = document.getElementById('step-1-content');
        const step2Content = document.getElementById('step-2-content');
        const step3Loading = document.getElementById('step-3-loading');

        const tabStep0 = document.getElementById('tab-step-0');
        const tabStep1 = document.getElementById('tab-step-1');
        const tabStep2 = document.getElementById('tab-step-2');
        const tabStep3 = document.getElementById('tab-step-3');

        const tabs = [tabStep0, tabStep1, tabStep2, tabStep3];

        function setTab(activeIndex) {
            tabs.forEach((tab, idx) => {
                if (!tab) return;
                const badge = tab.querySelector('span:first-child');
                const label = tab.querySelector('span:last-child');
                if (idx === activeIndex) {
                    tab.className = 'step-tab flex flex-col sm:flex-row items-center justify-center gap-2 p-2.5 sm:p-3 rounded-2xl bg-slate-800/80 border border-neon/40 text-neon transition-all';
                    if (badge) badge.className = 'w-6 h-6 rounded-full bg-neon text-black text-xs font-black flex items-center justify-center shrink-0';
                    if (label) label.className = 'text-xs font-bold text-white truncate';
                } else if (idx < activeIndex) {
                    tab.className = 'step-tab flex flex-col sm:flex-row items-center justify-center gap-2 p-2.5 sm:p-3 rounded-2xl bg-slate-950/60 border border-emerald-500/30 text-emerald-400 transition-all';
                    if (badge) badge.className = 'w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0';
                    if (label) label.className = 'text-xs font-bold text-slate-300 truncate';
                } else {
                    tab.className = 'step-tab flex flex-col sm:flex-row items-center justify-center gap-2 p-2.5 sm:p-3 rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 transition-all';
                    if (badge) badge.className = 'w-6 h-6 rounded-full bg-slate-800 text-slate-400 text-xs font-bold flex items-center justify-center shrink-0';
                    if (label) label.className = 'text-xs font-bold text-slate-400 truncate';
                }
            });
        }

        const step1Alert = document.getElementById('step-1-alert');
        const step2Alert = document.getElementById('step-2-alert');

        let verifiedCustomer = null;

        function showAlert(elem, type, message) {
            elem.classList.remove('hidden', 'bg-red-950/60', 'border-red-500/40', 'text-red-300', 'bg-emerald-950/60', 'border-emerald-500/40', 'text-emerald-300');
            if (type === 'error') {
                elem.classList.add('bg-red-950/60', 'border-red-500/40', 'text-red-300');
            } else {
                elem.classList.add('bg-emerald-950/60', 'border-emerald-500/40', 'text-emerald-300');
            }
            elem.innerHTML = message;
        }

        // STEP 1 NAVIGATION: Continue from Requirements to License
        const btnNextStep1 = document.getElementById('btn-next-step-1');
        if (btnNextStep1) {
            btnNextStep1.addEventListener('click', () => {
                step0Content.classList.add('hidden');
                step1Content.classList.remove('hidden');
                setTab(1);
            });
        }

        // STEP 2 NAVIGATION: Back to Requirements
        const btnBackStep0 = document.getElementById('btn-back-step-0');
        if (btnBackStep0) {
            btnBackStep0.addEventListener('click', () => {
                step1Content.classList.add('hidden');
                step0Content.classList.remove('hidden');
                setTab(0);
            });
        }

        // STEP 3 NAVIGATION: Back to License
        const btnBackStep1 = document.getElementById('btn-back-step-1');
        if (btnBackStep1) {
            btnBackStep1.addEventListener('click', () => {
                step2Content.classList.add('hidden');
                step1Content.classList.remove('hidden');
                setTab(1);
            });
        }

        // STEP 2 FORM: Verify Customer & Purchase Code
        document.getElementById('customer-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-step-1');
            btn.disabled = true;
            btn.innerHTML = `
                <div class="w-4 h-4 border-2 border-black border-t-transparent rounded-full animate-spin"></div>
                <span>Verifying Purchase Code...</span>
            `;

            const payload = {
                name: document.getElementById('name').value,
                phone_country_code: document.getElementById('phone_country_code').value,
                phone_number: document.getElementById('phone_number').value,
                email: document.getElementById('email').value,
                profession: document.getElementById('profession').value,
                country: document.getElementById('country').value,
                city: document.getElementById('city').value,
                purchase_code: document.getElementById('purchase_code').value,
                agree_license: document.getElementById('agree_license')?.checked ? 1 : 0
            };

            if (!payload.agree_license) {
                showAlert(step1Alert, 'error', 'You must read and agree to the End User License Agreement (EULA) before proceeding.');
                btn.disabled = false;
                btn.innerHTML = `
                    <span>Accept License &amp; Continue</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                `;
                return;
            }

            try {
                const res = await fetch("{{ url('/install/verify-customer') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    verifiedCustomer = data.customer;
                    // Populate Step 3 Summary Ribbon & Defaults
                    document.getElementById('summary-customer-name').innerText = verifiedCustomer.name;
                    document.getElementById('summary-customer-phone').innerText = `(${verifiedCustomer.phone})`;
                    document.getElementById('summary-purchase-code').innerText = verifiedCustomer.purchase_code;
                    if (document.getElementById('company_name') && !document.getElementById('company_name').value) {
                        document.getElementById('company_name').value = verifiedCustomer.name + ' Enterprise';
                    }
                    if (document.getElementById('admin_email') && !document.getElementById('admin_email').value) {
                        document.getElementById('admin_email').value = verifiedCustomer.email;
                    }

                    // Transition to Step 3 (Database Setup)
                    step1Content.classList.add('hidden');
                    step2Content.classList.remove('hidden');
                    setTab(2);
                } else {
                    showAlert(step1Alert, 'error', data.message || 'Verification failed. Please ensure the purchase code is valid.');
                }
            } catch (err) {
                showAlert(step1Alert, 'error', 'Network error during license verification. Please try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `
                    <span>Accept License &amp; Continue</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                `;
            }
        });

        // STEP 3: Test Database Connection
        document.getElementById('btn-test-db').addEventListener('click', async () => {
            const btn = document.getElementById('btn-test-db');
            btn.disabled = true;
            btn.innerText = 'Testing...';

            const payload = {
                db_host: document.getElementById('db_host').value,
                db_port: document.getElementById('db_port').value,
                db_name: document.getElementById('db_name').value,
                db_user: document.getElementById('db_user').value,
                db_pass: document.getElementById('db_pass').value,
            };

            try {
                const res = await fetch("{{ url('/install/test-db') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showAlert(step2Alert, 'success', data.message);
                } else {
                    showAlert(step2Alert, 'error', data.message || 'Database test failed.');
                }
            } catch (e) {
                showAlert(step2Alert, 'error', 'Error connecting to server.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Test Connection';
            }
        });

        // STEP 3: Execute Install & Migrate
        document.getElementById('database-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-run-install');
            btn.disabled = true;

            const payload = {
                db_host: document.getElementById('db_host').value,
                db_port: document.getElementById('db_port').value,
                db_name: document.getElementById('db_name').value,
                db_user: document.getElementById('db_user').value,
                db_pass: document.getElementById('db_pass').value,
                app_url: document.getElementById('app_url').value,
                company_name: document.getElementById('company_name')?.value || '',
                company_email: verifiedCustomer?.email || 'admin@webotapp.com',
                admin_email: document.getElementById('admin_email')?.value || verifiedCustomer?.email || 'admin@webotapp.com',
                admin_password: document.getElementById('admin_password')?.value || 'admin123',
            };

            // Switch to loading view
            step2Content.classList.add('hidden');
            step3Loading.classList.remove('hidden');
            setTab(3);

            try {
                const res = await fetch("{{ url('/install/process') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    window.location.href = data.redirect || "{{ url('/install/complete') }}";
                } else {
                    step3Loading.classList.add('hidden');
                    step2Content.classList.remove('hidden');
                    setTab(2);
                    showAlert(step2Alert, 'error', data.message || 'Installation encountered an error. Please verify credentials.');
                    btn.disabled = false;
                }
            } catch (err) {
                step3Loading.classList.add('hidden');
                step2Content.classList.remove('hidden');
                setTab(2);
                showAlert(step2Alert, 'error', 'Installation interrupted by server error: ' + err.message);
                btn.disabled = false;
            }
        });

        function openEulaModal() {
            document.getElementById('eula-modal').classList.remove('hidden');
        }
        function closeEulaModal() {
            document.getElementById('eula-modal').classList.add('hidden');
        }
    </script>

    <!-- EULA & LICENSE AGREEMENT MODAL -->
    <div id="eula-modal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-2xl w-full max-h-[85vh] overflow-y-auto shadow-2xl p-6 sm:p-8 space-y-5 text-slate-300 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-neon"></span>
                    <h3 class="text-base font-black text-white">Software License Agreement &amp; EULA</h3>
                </div>
                <button type="button" onclick="closeEulaModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">✕</button>
            </div>
            
            <div class="space-y-4 text-xs leading-relaxed text-slate-300">
                <p>This End User License Agreement (&quot;Agreement&quot;) is a legally binding contract between WebotApp / WebotApp Lab (&quot;Licensor&quot;) and you or the entity you represent (&quot;Licensee&quot;).</p>
                
                <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">1. Grant of License</h4>
                <p>Upon verification of a valid Purchase Code, Licensor grants Licensee a non-exclusive, non-transferable license to install, execute, and operate one (1) instance of WebotApp Accounting Accounting Platform solely on a single primary domain or subdomain registered during this installation.</p>

                <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">2. Single-Domain Lock</h4>
                <p>Each license key is electronically tied to the authorized domain name. Installation across multiple domains, resale, sub-licensing, or public distribution of source code files without an extended multi-domain license is strictly prohibited.</p>

                <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">3. Digital Products &amp; Non-Refundable Nature</h4>
                <p>Because the Software consists of immediately accessible, transparent digital source code and electronic assets, all sales are considered final and irrevocable once deployed. Fraudulent chargebacks or disputes initiated after delivery constitute breach of contract and will trigger immediate license key blacklisting and legal enforcement under the Information Technology Act and applicable international commercial treaties.</p>

                <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">4. Audit &amp; Electronic Signature Proof</h4>
                <p>By checking the acceptance box and proceeding with installation, Licensee acknowledges and consents that the installation timestamp, licensee credentials, domain name, client IP address, and cryptographic SHA-256 verification hash are recorded on the central licensing infrastructure as admissible electronic evidence of contract execution.</p>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-end">
                <button type="button" onclick="closeEulaModal()" class="px-6 py-2.5 rounded-xl bg-neon hover:bg-neon/90 text-black font-bold text-xs uppercase tracking-wider transition-all">
                    I Understand &amp; Close
                </button>
            </div>
        </div>
    </div>
</body>
</html>
