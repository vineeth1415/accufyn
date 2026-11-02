<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete — WebotApp Accounting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        neon: '#10b981',
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
</head>
<body class="min-h-full bg-[#090d16] text-slate-100 flex flex-col justify-between selection:bg-emerald-500 selection:text-black">

    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[350px] bg-emerald-500/10 rounded-full blur-[140px] pointer-events-none -z-10"></div>

    <!-- Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl py-4">
        <div class="max-w-4xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold shadow-lg shadow-emerald-500/10">
                    <i class="fa-solid fa-coins text-lg"></i>
                </div>
                <h1 class="text-base font-black text-white">WebotApp Accounting</h1>
            </div>
            <span class="text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Live & Installed
            </span>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1 max-w-2xl w-full mx-auto px-6 py-12 flex flex-col justify-center">
        <div class="bg-slate-900/90 rounded-3xl border border-slate-800 p-8 sm:p-10 shadow-2xl backdrop-blur-xl text-center space-y-8">
            
            <!-- Success Icon -->
            <div class="w-20 h-20 rounded-3xl bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 mx-auto flex items-center justify-center shadow-2xl shadow-emerald-500/20">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>

            <div>
                <span class="text-emerald-400 font-mono text-xs uppercase font-bold tracking-widest">Setup Successful</span>
                <h2 class="text-3xl font-black text-white mt-1">WebotApp Accounting is Ready!</h2>
                <p class="text-sm text-slate-400 mt-2">
                    Your financial database has been migrated, accounting chart & ledger accounts initialized, and your official license recorded.
                </p>
            </div>

            <!-- Admin Credentials Card -->
            <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800/80 text-left space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Super Admin Account</span>
                    <span class="text-[10px] font-mono text-emerald-400 font-bold uppercase px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30">Role: ADMIN</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono">
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-bold block">Login Email</span>
                        <span class="text-white font-bold text-sm select-all">admin@webotapp.com</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-bold block">Password</span>
                        <span class="text-white font-bold text-sm select-all">admin123</span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 font-sans italic pt-1 border-t border-slate-800/50">
                    * You can change your password anytime under Profile Settings inside the Dashboard.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-2">
                <a href="{{ route('dashboard') }}" class="flex-1 py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-emerald-950 flex items-center justify-center gap-2">
                    <span>Go to Accounting Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <!-- WhatsApp Support Mention -->
            <div class="pt-4 border-t border-slate-800/60 text-xs text-slate-400">
                Need any help or custom feature development? 
                <a href="https://wa.me/917002484119" target="_blank" class="text-emerald-400 font-bold hover:underline ml-1">
                    WhatsApp +91 7002484119
                </a>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        <p>WebotApp Accounting Platform &copy; {{ date('Y') }} WebotApp.</p>
    </footer>

</body>
</html>
