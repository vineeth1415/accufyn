<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - WebotApp Accounting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-slate-50 text-slate-800">
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 items-center justify-center text-emerald-600 font-bold text-2xl shadow-sm mb-4">
                <i class="fa-solid fa-coins"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">WebotApp Accounting</h1>
            <p class="text-xs text-slate-500 mt-1">Enterprise Financial Ledger & Invoicing Platform</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-8 shadow-xl shadow-slate-200/50">
            @if(isset($errors) && $errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-sm text-red-500"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email', 'admin@webotapp.com') }}" required
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition shadow-sm"
                            placeholder="admin@webotapp.com">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Password</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" required
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition shadow-sm"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 text-slate-500 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-white border-slate-300 text-emerald-600 focus:ring-0">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                    <span class="text-white">Sign In to Accounting</span>
                    <i class="fa-solid fa-arrow-right text-xs text-white"></i>
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-400 mt-8">
            WebotApp Accounting Suite &bull; Secure Encrypted Installation
        </p>
    </div>
</body>
</html>
