@extends('layouts.app')

@section('title', 'System Updates')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12" id="update-manager">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <i class="fa-solid fa-cloud-arrow-down text-xs"></i> Official Release Channel
                </span>
                <span class="text-xs text-slate-400 font-medium">Auto-Updater Engine</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1.5">System Updates</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Check and apply official updates, database patches, and AI engine improvements with 1-click.</p>
        </div>
        
        <div class="flex items-center gap-2.5">
            <button id="btn-check-updates" onclick="checkForUpdates()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <i id="check-icon" class="fa-solid fa-arrows-rotate text-emerald-500"></i>
                <span>Check for Updates</span>
            </button>
        </div>
    </div>

    <!-- Alert Banner (Hidden by default) -->
    <div id="alert-box" class="hidden p-4 rounded-2xl border text-sm font-semibold flex items-center justify-between transition-all">
        <div class="flex items-center gap-2.5">
            <i id="alert-icon" class="fa-solid"></i>
            <span id="alert-text"></span>
        </div>
        <button onclick="hideAlert()" class="text-xs opacity-60 hover:opacity-100">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Current Version Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-tag"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Current Installed Version</span>
                    <h3 id="display-current-version" class="text-3xl font-black text-slate-900 dark:text-white mt-1">v{{ $currentVersion }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">WebotApp Accounting Suite</p>
                </div>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                <span class="text-slate-500">Status</span>
                <span class="inline-flex items-center gap-1.5 font-bold text-emerald-600 dark:text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Production Active
                </span>
            </div>
        </div>

        <!-- Latest Version Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-500 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Latest Available Release</span>
                    <h3 id="display-latest-version" class="text-3xl font-black text-slate-900 dark:text-white mt-1">v{{ $updateInfo['latest_version'] ?? '1.0.1' }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Released: {{ $updateInfo['release_date'] ?? '2026-09-19' }}</p>
                </div>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                <span class="text-slate-500">Update Available</span>
                <span id="display-has-update" class="font-bold {{ $updateInfo['has_update'] ? 'text-amber-500' : 'text-emerald-500' }}">
                    {{ $updateInfo['has_update'] ? 'Yes, Update Ready' : 'Up to Date' }}
                </span>
            </div>
        </div>

        <!-- License & Support Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-500 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">License Verification</span>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mt-1">
                        {{ strtoupper($updateInfo['license_type'] ?? 'REGULAR') }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Updates: <strong class="text-slate-700 dark:text-slate-200">{{ $updateInfo['updates_remaining'] ?? 'Active' }}</strong>
                    </p>
                </div>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                <span class="text-slate-500">Central Sync</span>
                <span class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-circle-check"></i> Connected
                </span>
            </div>
        </div>
    </div>

    <!-- Action Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white" id="release-title">{{ $updateInfo['title'] ?? 'Release Notes' }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Review enhancements, security patches, and database optimizations included in this update.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a id="btn-download-zip" href="{{ $updateInfo['download_url'] ?? 'https://lab.webotapp.com/uploads/products/webotapp-accounts-source.zip' }}" target="_blank" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-solid fa-file-zipper text-emerald-500"></i>
                    <span>Download Update (.ZIP)</span>
                </a>
                <button id="btn-apply-update" onclick="applyUpdate()" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i id="apply-icon" class="fa-solid fa-cloud-arrow-down"></i>
                    <span id="apply-btn-text">1-Click Apply Update</span>
                </button>
            </div>
        </div>

        <!-- Changelog -->
        <div class="space-y-3">
            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">What's New in This Release:</h3>
            <ul id="changelog-list" class="space-y-2.5">
                @if(!empty($updateInfo['changelog']))
                    @foreach($updateInfo['changelog'] as $item)
                        <li class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-[10px] shrink-0 mt-0.5">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <span class="leading-relaxed">{{ $item }}</span>
                        </li>
                    @endforeach
                @else
                    <li class="text-xs text-slate-400">System is up to date. No pending updates found.</li>
                @endif
            </ul>
        </div>

        <!-- Output Console (Hidden until update applied) -->
        <div id="output-console-box" class="hidden space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Migration & Cache Output:</span>
            </div>
            <pre id="output-console" class="p-4 rounded-xl bg-slate-950 text-emerald-400 font-mono text-xs overflow-x-auto max-h-48 whitespace-pre-wrap"></pre>
        </div>
    </div>

    <!-- VIP WhatsApp Helpdesk -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-500/10 via-blue-500/10 to-transparent border border-emerald-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-xl shrink-0 shadow-lg shadow-emerald-500/30">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white text-sm">Need Help With Installation or Updates?</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Our VIP engineering team can assist with deployment, backups, and custom feature additions.</p>
            </div>
        </div>
        <a href="https://wa.me/917002484119?text=Hi%20WebotApp%20Team%2C%20I%20need%20assistance%20with%20WebotApp%20Accounting%20updates." target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-xs shadow-md transition shrink-0">
            <i class="fa-brands fa-whatsapp text-sm"></i>
            <span>Chat on WhatsApp</span>
        </a>
    </div>
</div>

<script>
    function showAlert(text, type = 'info') {
        const box = document.getElementById('alert-box');
        const icon = document.getElementById('alert-icon');
        const alertText = document.getElementById('alert-text');

        box.className = 'p-4 rounded-2xl border text-sm font-semibold flex items-center justify-between transition-all';
        if (type === 'success') {
            box.classList.add('bg-emerald-50', 'dark:bg-emerald-950/40', 'border-emerald-200', 'dark:border-emerald-800', 'text-emerald-800', 'dark:text-emerald-300');
            icon.className = 'fa-solid fa-circle-check text-emerald-500';
        } else if (type === 'error') {
            box.classList.add('bg-rose-50', 'dark:bg-rose-950/40', 'border-rose-200', 'dark:border-rose-800', 'text-rose-800', 'dark:text-rose-300');
            icon.className = 'fa-solid fa-triangle-exclamation text-rose-500';
        } else {
            box.classList.add('bg-blue-50', 'dark:bg-blue-950/40', 'border-blue-200', 'dark:border-blue-800', 'text-blue-800', 'dark:text-blue-300');
            icon.className = 'fa-solid fa-circle-info text-blue-500';
        }

        alertText.textContent = text;
        box.classList.remove('hidden');
    }

    function hideAlert() {
        document.getElementById('alert-box').classList.add('hidden');
    }

    async function checkForUpdates() {
        const icon = document.getElementById('check-icon');
        icon.classList.add('fa-spin');
        hideAlert();

        try {
            const res = await fetch("{{ route('updates.check') }}");
            const data = await res.json();

            document.getElementById('display-current-version').textContent = 'v' + data.current_version;
            document.getElementById('display-latest-version').textContent = 'v' + data.latest_version;
            
            const hasUpdateEl = document.getElementById('display-has-update');
            if (data.has_update) {
                hasUpdateEl.textContent = 'Yes, Update Ready';
                hasUpdateEl.className = 'font-bold text-amber-500';
                showAlert('A new release (v' + data.latest_version + ') is ready for 1-click update!', 'success');
            } else {
                hasUpdateEl.textContent = 'Up to Date';
                hasUpdateEl.className = 'font-bold text-emerald-500';
                showAlert('Your system is running the latest available version (v' + data.current_version + ').', 'info');
            }

            if (data.title) {
                document.getElementById('release-title').textContent = data.title;
            }

            if (Array.isArray(data.changelog) && data.changelog.length > 0) {
                const list = document.getElementById('changelog-list');
                list.innerHTML = '';
                data.changelog.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300';
                    li.innerHTML = `<span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-[10px] shrink-0 mt-0.5"><i class="fa-solid fa-check"></i></span><span class="leading-relaxed">${item}</span>`;
                    list.appendChild(li);
                });
                if (data.download_url) {
                    const dlBtn = document.getElementById('btn-download-zip');
                    if (dlBtn) dlBtn.href = data.download_url;
                }
            }
        } catch (err) {
            showAlert('Unable to connect to updates server. Please check your internet connection.', 'error');
        } finally {
            icon.classList.remove('fa-spin');
        }
    }

    async function applyUpdate() {
        if (!confirm('Apply this update now? It will run database migrations and refresh application caches.')) {
            return;
        }

        const btn = document.getElementById('btn-apply-update');
        const icon = document.getElementById('apply-icon');
        const btnText = document.getElementById('apply-btn-text');

        btn.disabled = true;
        icon.className = 'fa-solid fa-spinner fa-spin';
        btnText.textContent = 'Applying Update...';
        hideAlert();

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch("{{ route('updates.apply') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await res.json();

            if (data.success) {
                showAlert(data.message || 'System updated successfully!', 'success');
                document.getElementById('display-current-version').textContent = 'v' + data.current_version;
                document.getElementById('display-has-update').textContent = 'Up to Date';
                document.getElementById('display-has-update').className = 'font-bold text-emerald-500';

                if (data.output) {
                    document.getElementById('output-console').textContent = data.output;
                    document.getElementById('output-console-box').classList.remove('hidden');
                }
            } else {
                showAlert(data.message || 'Failed to apply update.', 'error');
            }
        } catch (err) {
            showAlert('Error applying update: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            icon.className = 'fa-solid fa-cloud-arrow-down';
            btnText.textContent = '1-Click Apply Update';
        }
    }
</script>
@endsection
