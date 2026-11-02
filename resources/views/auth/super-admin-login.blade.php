<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Console - {{ $company->name ?? config('app.name', 'Accufyn') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono-code { font-family: 'JetBrains Mono', monospace; }
        .glow-amber {
            box-shadow: 0 0 50px -10px rgba(245, 158, 11, 0.25);
        }
        .glow-border {
            border: 1px solid rgba(245, 158, 11, 0.25);
        }
        .bg-radial-grid {
            background-image: radial-gradient(rgba(245, 158, 11, 0.08) 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-slate-950 text-slate-100 bg-radial-grid relative selection:bg-amber-500 selection:text-slate-950">

    <!-- Ambient Glow Orbs -->
    <div class="fixed top-1/4 left-1/3 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-1/4 right-1/3 translate-x-1/2 translate-y-1/2 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 my-auto">

        <!-- Top Header & Owner Badge -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold mb-4 shadow-inner">
                <i class="fa-solid fa-crown text-amber-400 animate-bounce"></i>
                <span class="tracking-wide uppercase text-[10px] font-bold">Top Owner Console &bull; Full Privilege</span>
            </div>

            <!-- Master Emblem -->
            <div class="flex justify-center mb-3">
                <div class="relative">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 flex items-center justify-center text-slate-950 font-extrabold text-2xl shadow-xl shadow-amber-500/30 ring-4 ring-amber-500/20">
                        <i class="fa-solid fa-shield-halved text-slate-950"></i>
                    </div>
                    <div class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center text-xs shadow-md">
                        <i class="fa-solid fa-crown text-[10px]"></i>
                    </div>
                </div>
            </div>

            <h1 class="text-2xl font-black text-white tracking-tight flex items-center justify-center gap-2">
                <span>Super Administrator</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Master Authentication &bull; User Governance &bull; Complete Control</p>
        </div>

        <!-- Super Admin Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-7 shadow-2xl glow-amber relative overflow-hidden">
            
            <!-- Top Card Accents -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 via-emerald-500 to-amber-500"></div>

            <!-- Alerts -->
            @if(session('info'))
                <div class="mb-5 p-3.5 rounded-xl bg-blue-950/60 border border-blue-800 text-blue-300 text-xs flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-info text-blue-400 text-sm"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-800 text-emerald-300 text-xs flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-950/60 border border-red-800 text-red-300 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-red-400 text-sm mt-0.5 shrink-0"></i>
                    <div class="space-y-0.5">
                        <p class="font-bold text-red-200">Authentication Failed</p>
                        <p class="text-red-300/90">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('superadmin.login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Super Admin Email</span>
                        <span class="text-[10px] text-amber-400 lowercase font-mono">top owner credentials</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" id="super_email" name="email" value="{{ old('email', 'superadmin@accufyn.com') }}" required autofocus
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-700 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition font-mono-code"
                            placeholder="superadmin@accufyn.com">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Master Password</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                            <i class="fa-solid fa-key text-amber-500"></i>
                        </span>
                        <input type="password" id="super_password" name="password" required
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-950/80 border border-slate-700 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 text-xs">
                            <i id="eyeIcon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-400 cursor-pointer hover:text-slate-300">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-amber-500 focus:ring-0">
                        <span>Keep Super Admin session active</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full mt-2 py-3 px-4 bg-gradient-to-r from-amber-500 via-amber-600 to-amber-500 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-amber-500/25 transition flex items-center justify-center gap-2 uppercase tracking-wider">
                    <i class="fa-solid fa-unlock-keyhole"></i>
                    <span>Authenticate as Super Admin &rarr;</span>
                </button>
            </form>

            <!-- Quick Credential Auto-Fill Box -->
            <div class="mt-5 p-3 rounded-xl bg-slate-950/70 border border-slate-800 text-[11px] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card-clip"></i>
                        <span>Default Super Admin Credentials</span>
                    </span>
                    <button type="button" onclick="fillCredentials('superadmin@accufyn.com', 'admin123')"
                        class="text-[10px] px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 hover:bg-amber-500/30 transition font-semibold">
                        Fill Demo
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2 font-mono text-[10px] text-slate-300">
                    <div class="bg-slate-900 p-1.5 rounded border border-slate-800/80">
                        <span class="text-slate-500 block text-[9px] uppercase">Email</span>
                        <span class="text-amber-300 truncate block">superadmin@accufyn.com</span>
                    </div>
                    <div class="bg-slate-900 p-1.5 rounded border border-slate-800/80">
                        <span class="text-slate-500 block text-[9px] uppercase">Password</span>
                        <span class="text-amber-300 block">admin123</span>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 flex items-center gap-1">
                    <i class="fa-solid fa-check text-emerald-400 text-[9px]"></i>
                    <span>Owner email <strong class="text-white">vineeth141510@gmail.com</strong> is also configured as Super Admin.</span>
                </p>
            </div>

            <!-- Privilege Highlights -->
            <div class="mt-4 pt-4 border-t border-slate-800/80 grid grid-cols-3 gap-2 text-center text-[10px] text-slate-400">
                <div class="p-1.5 rounded-lg bg-slate-950/40 border border-slate-800/60">
                    <i class="fa-solid fa-user-plus text-emerald-400 block text-xs mb-0.5"></i>
                    <span>Add Users</span>
                </div>
                <div class="p-1.5 rounded-lg bg-slate-950/40 border border-slate-800/60">
                    <i class="fa-solid fa-user-pen text-blue-400 block text-xs mb-0.5"></i>
                    <span>Edit Roles</span>
                </div>
                <div class="p-1.5 rounded-lg bg-slate-950/40 border border-slate-800/60">
                    <i class="fa-solid fa-user-xmark text-red-400 block text-xs mb-0.5"></i>
                    <span>Delete Users</span>
                </div>
            </div>
        </div>

        <!-- Return to Standard Login -->
        <div class="text-center mt-5">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs text-slate-400 hover:text-white transition font-medium">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Switch to Standard User Login</span>
            </a>
        </div>

        <p class="text-center text-[11px] text-slate-600 mt-6 font-mono">
            {{ $company->name ?? config('app.name', 'Accufyn') }} &bull; High Security Super Admin Console
        </p>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('super_password');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function fillCredentials(email, password) {
            document.getElementById('super_email').value = email;
            document.getElementById('super_password').value = password;
        }
    </script>
</body>
</html>
