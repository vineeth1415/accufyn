@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">System Settings</h1>
            <p class="text-xs text-slate-500">Configure company identity, currency, financial year periods, tax rates, and category tags</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Active Currency: {{ $company->currency_code ?? 'INR' }} ({{ $company->currency_symbol ?? '₹' }})</span>
            </span>
        </div>
    </div>

    <!-- Company Settings Form -->
    <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Company & Financial Defaults</h3>
                <p class="text-[11px] text-slate-500">Base configuration for ledger, invoicing, and tax reporting</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">
                <i class="fa-solid fa-calendar-check mr-1"></i> FY: {{ $company->financial_year ?? 'April - March' }}
            </span>
        </div>

        <form action="{{ route('settings.company') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <!-- Brand Logo & Theme Palette -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-5 mb-2">
                <!-- Brand Logo -->
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-2">Company Brand Logo</label>
                    <div class="flex items-center gap-4">
                        <div id="logo-preview-box" class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center bg-white overflow-hidden shrink-0 shadow-sm">
                            @if(!empty($company->logo_path))
                                <img src="{{ asset(ltrim($company->logo_path, '/')) }}" alt="Logo" class="w-full h-full object-contain p-1" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <i class="fa-solid fa-image text-slate-400 text-2xl" style="display: none;"></i>
                            @else
                                <i class="fa-solid fa-image text-slate-400 text-2xl"></i>
                            @endif
                        </div>
                        <div class="space-y-2 flex-1">
                            <input type="file" name="logo" id="logo-file-input" accept="image/*" class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                            <p class="text-[10px] text-slate-400">PNG, JPG, SVG, WebP up to 2MB. Square or horizontal icon recommended.</p>
                            @if(!empty($company->logo_path))
                                <label class="inline-flex items-center gap-1.5 text-[11px] text-red-600 cursor-pointer">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span>Remove current logo</span>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Theme Color Palette -->
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-2">Theme Accent Color</label>
                    <div class="grid grid-cols-3 gap-2" id="color-palette-container">
                        @php
                            $colors = [
                                'emerald' => ['name' => 'Emerald Green', 'hex' => '#10b981'],
                                'indigo' => ['name' => 'Royal Indigo', 'hex' => '#6366f1'],
                                'blue' => ['name' => 'Ocean Blue', 'hex' => '#3b82f6'],
                                'purple' => ['name' => 'Amethyst Purple', 'hex' => '#a855f7'],
                                'rose' => ['name' => 'Crimson Rose', 'hex' => '#f43f5e'],
                                'amber' => ['name' => 'Warm Amber', 'hex' => '#f59e0b'],
                            ];
                            $currentColor = $company->theme_color ?? 'emerald';
                        @endphp
                        @foreach($colors as $code => $c)
                            <label class="color-palette-card flex items-center gap-2 p-2 rounded-xl border cursor-pointer transition hover:bg-slate-100 {{ $currentColor === $code ? 'border-slate-900 bg-white ring-2 ring-slate-900/15 font-bold shadow-xs' : 'border-slate-200 bg-white opacity-85 hover:opacity-100' }}" data-color="{{ $code }}">
                                <input type="radio" name="theme_color" value="{{ $code }}" {{ $currentColor === $code ? 'checked' : '' }} class="sr-only">
                                <span class="w-4 h-4 rounded-full shrink-0 shadow-xs border border-black/10" style="background-color: {{ $c['hex'] }};"></span>
                                <span class="text-[11px] font-medium text-slate-700 truncate">{{ $c['name'] }}</span>
                                <i class="fa-solid fa-check text-[10px] ml-auto text-slate-800 check-icon {{ $currentColor === $code ? '' : 'hidden' }}"></i>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1.5">Applies across sidebar, buttons, links, and system highlights</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Company Name *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name ?? 'WebotApp Enterprise') }}" required 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Corporate Email *</label>
                    <input type="email" name="email" value="{{ old('email', $company->email ?? 'billing@webotapp.com') }}" required 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $company->phone ?? '+91 7002484119') }}" 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Default Currency Code *</label>
                    <input type="text" name="currency_code" value="{{ old('currency_code', $company->currency_code ?? 'INR') }}" required 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 uppercase font-mono font-bold focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm" placeholder="INR">
                    <p class="text-[10px] text-slate-400 mt-1">e.g. INR, USD, EUR, GBP</p>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Currency Symbol *</label>
                    <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $company->currency_symbol ?? '₹') }}" required 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-bold focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm" placeholder="₹">
                    <p class="text-[10px] text-slate-400 mt-1">e.g. ₹ (Rupee), $, €, £</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Financial Year Period *</label>
                    <select name="financial_year" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-medium focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                        <option value="April - March" {{ ($company->financial_year ?? 'April - March') === 'April - March' ? 'selected' : '' }}>April - March (Standard India FY)</option>
                        <option value="January - December" {{ ($company->financial_year ?? '') === 'January - December' ? 'selected' : '' }}>January - December (Calendar Year)</option>
                        <option value="July - June" {{ ($company->financial_year ?? '') === 'July - June' ? 'selected' : '' }}>July - June (AUS/NZ FY)</option>
                        <option value="October - September" {{ ($company->financial_year ?? '') === 'October - September' ? 'selected' : '' }}>October - September (US Fed FY)</option>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Used for annual profit/loss & tax summaries</p>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Financial Year Start Date</label>
                    <input type="text" name="financial_year_start" value="{{ old('financial_year_start', $company->financial_year_start ?? '04-01') }}" 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-mono focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm" placeholder="04-01">
                    <p class="text-[10px] text-slate-400 mt-1">MM-DD format (04-01 for April 1)</p>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">GSTIN / Tax Business ID</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $company->tax_number ?? 'GSTIN18AABCT2345K1Z5') }}" 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 uppercase font-mono focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm" placeholder="GSTIN...">
                    <p class="text-[10px] text-slate-400 mt-1">Appears on tax invoices & receipts</p>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Registered Office Address</label>
                <input type="text" name="address" value="{{ old('address', $company->address ?? 'Tech Innovation Hub, MG Road') }}" 
                    class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm mb-2">
                <div class="grid grid-cols-3 gap-3">
                    <input type="text" name="city" placeholder="City" value="{{ old('city', $company->city ?? 'Guwahati') }}" 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                    <input type="text" name="state" placeholder="State" value="{{ old('state', $company->state ?? 'Assam') }}" 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                    <input type="text" name="country" placeholder="Country" value="{{ old('country', $company->country ?? 'India') }}" 
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-white"></i>
                    <span class="text-white force-white">Save Company & Financial Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Administrator Security & Password Change -->
    <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 shadow-xs">
                    <i class="fa-solid fa-shield-halved text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Administrator Security & Password</h3>
                    <p class="text-[11px] text-slate-500">Change your administrative account login password</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-user text-slate-400"></i> {{ auth()->user()->email ?? 'admin@domain.com' }}
            </span>
        </div>

        <form action="{{ route('settings.password') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Current Password *</label>
                    <input type="password" name="current_password" required placeholder="Enter current password"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">New Password *</label>
                    <input type="password" name="new_password" required minlength="6" placeholder="Min 6 characters"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Confirm New Password *</label>
                    <input type="password" name="new_password_confirmation" required minlength="6" placeholder="Confirm new password"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-key text-white"></i>
                    <span class="text-white force-white">Update Password</span>
                </button>
            </div>
        </form>
    </div>

    {{-- AI Assistant — OpenRouter Settings --}}
    <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5 bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white shadow-sm">
                    <i class="fa-solid fa-robot text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">AI Accounting Assistant <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-violet-100 text-violet-700 border border-violet-200 ml-1">OpenRouter</span></h3>
                    <p class="text-[11px] text-slate-500">Free AI models via OpenRouter — supports Malayalam & English</p>
                </div>
            </div>
            <div id="aiStatusBadge">
                @if(!empty($company->openrouter_api_key))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>AI Configured & Active</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-semibold">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>API Key Not Configured</span>
                    </span>
                @endif
            </div>
        </div>

        <form action="{{ route('settings.ai') }}" method="POST" class="space-y-4 text-xs" id="aiSettingsForm">
            @csrf

            {{-- OpenRouter API Key --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="font-semibold text-slate-700 uppercase tracking-wider">OpenRouter API Key</label>
                    <a href="https://openrouter.ai/keys" target="_blank" class="text-violet-600 hover:text-violet-700 text-[11px] font-medium flex items-center gap-1">
                        <span>Get Free Key at openrouter.ai/keys</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                    </a>
                </div>
                <div class="relative">
                    <input type="password" id="openrouterApiKeyInput" name="openrouter_api_key"
                        value="{{ old('openrouter_api_key', $company->openrouter_api_key ?? '') }}"
                        placeholder="sk-or-v1-..."
                        autocomplete="off"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 font-mono text-xs focus:outline-none focus:border-violet-600 focus:ring-1 focus:ring-violet-600 shadow-sm pr-16">
                    <button type="button" onclick="toggleOpenRouterKey()" class="absolute right-3 top-2.5 text-xs text-slate-500 hover:text-slate-800 font-semibold transition">
                        <span id="orKeyVisibilityText">Show</span>
                    </button>
                </div>
                <p class="text-[10px] text-slate-500 mt-1">
                    Key is stored securely in the database and never exposed to the browser or client-side JavaScript.
                </p>
            </div>

            {{-- Free Model Selector + Temperature + Max Tokens --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Free AI Model</label>
                    <select name="openrouter_model" id="orModelSelect"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-medium focus:outline-none focus:border-violet-600 focus:ring-1 focus:ring-violet-600 shadow-sm text-xs">
                        @php
                            $freeModels = \App\Http\Controllers\AiChatController::$FREE_MODELS;
                            $currentModel = $company->openrouter_model ?? 'meta-llama/llama-3.2-3b-instruct:free';
                        @endphp
                        @foreach($freeModels as $modelId => $modelLabel)
                            <option value="{{ $modelId }}" {{ $currentModel === $modelId ? 'selected' : '' }}>
                                {{ $modelLabel }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">All models listed are 100% free on OpenRouter.</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Temperature</label>
                    <input type="number" name="openrouter_temperature" step="0.05" min="0" max="2"
                        value="{{ old('openrouter_temperature', $company->openrouter_temperature ?? 0.20) }}"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-violet-600 focus:ring-1 focus:ring-violet-600 shadow-sm">
                    <p class="text-[10px] text-slate-400 mt-1">0.0 = deterministic, 1.0 = creative</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Max Tokens</label>
                    <input type="number" name="openrouter_max_tokens" step="50" min="100" max="4000"
                        value="{{ old('openrouter_max_tokens', $company->openrouter_max_tokens ?? 800) }}"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-violet-600 focus:ring-1 focus:ring-violet-600 shadow-sm">
                    <p class="text-[10px] text-slate-400 mt-1">Max response length (100–4000)</p>
                </div>
            </div>

            {{-- Connection Status Box --}}
            <div id="aiConnectionStatus" class="hidden p-3 rounded-xl border text-xs font-medium"></div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-1">
                <button type="button" onclick="testOpenRouterConnection()"
                    class="flex-1 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl border border-slate-300 transition flex items-center justify-center gap-2">
                    <i id="testBtnIcon" class="fa-solid fa-satellite-dish text-slate-500"></i>
                    <span id="testBtnText">Test Connection</span>
                </button>
                <button type="submit"
                    class="flex-1 px-5 py-2.5 bg-violet-600 hover:bg-violet-500 text-white font-bold rounded-xl shadow-md shadow-violet-600/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-white"></i>
                    <span class="text-white force-white">Save AI Settings</span>
                </button>
            </div>
        </form>

        {{-- Info Banner --}}
        <div class="p-3 rounded-xl bg-violet-50 border border-violet-200 text-[11px] text-violet-800 space-y-1">
            <p class="font-bold">OpenRouter AI — Free Models for Accounting</p>
            <p>Supports: Invoicing, Bills, GST Reports, P&amp;L, Customer/Vendor Balances, Cash Flow — in <strong>Malayalam and English</strong>.</p>
            <p>Get your free API key at <a href="https://openrouter.ai/keys" target="_blank" class="underline font-semibold">openrouter.ai/keys</a> — no credit card required for free models.</p>
        </div>
    </div>

    <!-- Taxes & Categories Dual Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tax Rates -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4 bg-white">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2">Tax Rates (GST / VAT)</h3>
            <div class="space-y-2 text-xs">
                @foreach($taxes as $t)
                    <div class="flex justify-between items-center p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="font-medium text-slate-800">{{ $t->name }}</span>
                        <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">{{ $t->rate }}%</span>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('settings.taxes') }}" method="POST" class="pt-3 border-t border-slate-200 space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="name" placeholder="Tax Name (e.g. GST 18%)" required class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900">
                    <input type="number" name="rate" placeholder="Rate %" step="0.1" required class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900">
                </div>
                <input type="hidden" name="type" value="percent">
                <button type="submit" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-semibold border border-slate-300 transition">Add Tax Rate</button>
            </form>
        </div>

        <!-- Categories -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4 bg-white">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2">Chart of Categories</h3>
            <div class="space-y-2 text-xs max-h-48 overflow-y-auto pr-1">
                @foreach($categories as $cat)
                    <div class="flex justify-between items-center p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="font-medium text-slate-800">{{ $cat->name }}</span>
                        <span class="px-2 py-0.5 rounded-lg text-[10px] uppercase font-bold {{ $cat->type === 'income' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ $cat->type }}
                        </span>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('settings.categories') }}" method="POST" class="pt-3 border-t border-slate-200 space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="name" placeholder="Category Name" required class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900">
                    <select name="type" required class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-medium">
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-semibold border border-slate-300 transition">Add Category</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleOpenRouterKey() {
        const input = document.getElementById('openrouterApiKeyInput');
        const txt = document.getElementById('orKeyVisibilityText');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (txt) txt.innerText = 'Hide';
        } else {
            input.type = 'password';
            if (txt) txt.innerText = 'Show';
        }
    }

    async function testOpenRouterConnection() {
        const keyInput = document.getElementById('openrouterApiKeyInput');
        const modelSelect = document.getElementById('orModelSelect');
        const statusBox = document.getElementById('aiConnectionStatus');
        const btnIcon = document.getElementById('testBtnIcon');
        const btnText = document.getElementById('testBtnText');
        const badge = document.getElementById('aiStatusBadge');

        const apiKey = keyInput ? keyInput.value.trim() : '';
        const model = modelSelect ? modelSelect.value : '';

        if (!apiKey) {
            statusBox.className = 'p-3 rounded-xl border border-amber-300 bg-amber-50 text-amber-800 text-xs font-medium space-y-1';
            statusBox.innerHTML = '<div class="flex items-center gap-1.5 font-bold"><i class="fa-solid fa-triangle-exclamation"></i><span>API Key Missing</span></div><p>Please enter your OpenRouter API key above before testing the connection.</p>';
            statusBox.classList.remove('hidden');
            keyInput.focus();
            return;
        }

        // Loading state
        statusBox.className = 'p-3 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-800 text-xs font-medium';
        statusBox.innerHTML = '<div class="flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin"></i><span>Connecting to OpenRouter API (' + model + ')...</span></div>';
        statusBox.classList.remove('hidden');

        btnIcon.className = 'fa-solid fa-spinner fa-spin text-slate-500';
        btnText.innerText = 'Testing...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"]')?.value;

            const res = await fetch('{{ route("ai.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    api_key: apiKey,
                    model: model
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                statusBox.className = 'p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-900 text-xs font-medium space-y-1';
                statusBox.innerHTML = `
                    <div class="flex items-center gap-1.5 font-bold text-emerald-800">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>Connection Successful!</span>
                        <span class="ml-auto text-[10px] font-normal text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">${data.latency_ms}ms</span>
                    </div>
                    <p class="text-[11px] text-emerald-800 leading-relaxed">${data.message}</p>
                    <p class="text-[10px] text-emerald-700 font-mono">Model: ${data.model_used}</p>
                `;

                if (badge) {
                    badge.innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Connected & Verified</span>
                        </span>
                    `;
                }
            } else {
                statusBox.className = 'p-3 rounded-xl border border-red-300 bg-red-50 text-red-900 text-xs font-medium space-y-1';
                statusBox.innerHTML = `
                    <div class="flex items-center gap-1.5 font-bold text-red-800">
                        <i class="fa-solid fa-circle-xmark text-red-600"></i>
                        <span>Connection Failed</span>
                    </div>
                    <p class="text-[11px] text-red-800 leading-relaxed">${data.message || 'Unknown error occurred while connecting to OpenRouter.'}</p>
                `;
            }
        } catch (err) {
            statusBox.className = 'p-3 rounded-xl border border-red-300 bg-red-50 text-red-900 text-xs font-medium space-y-1';
            statusBox.innerHTML = `
                <div class="flex items-center gap-1.5 font-bold text-red-800">
                    <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                    <span>Network / Server Error</span>
                </div>
                <p class="text-[11px] text-red-800 leading-relaxed">${err.message || 'Could not communicate with the local server.'}</p>
            `;
        } finally {
            btnIcon.className = 'fa-solid fa-satellite-dish text-slate-500';
            btnText.innerText = 'Test Connection';
        }
    }

    // Instant interactive color selection highlight
    document.querySelectorAll('.color-palette-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.color-palette-card').forEach(c => {
                c.classList.remove('border-slate-900', 'ring-2', 'ring-slate-900/15', 'font-bold', 'shadow-xs');
                c.classList.add('border-slate-200', 'opacity-85');
                const check = c.querySelector('.check-icon');
                if (check) check.classList.add('hidden');
            });
            this.classList.remove('border-slate-200', 'opacity-85');
            this.classList.add('border-slate-900', 'ring-2', 'ring-slate-900/15', 'font-bold', 'shadow-xs');
            const check = this.querySelector('.check-icon');
            if (check) check.classList.remove('hidden');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // Instant logo file preview on selection
    const logoInput = document.getElementById('logo-file-input');
    const logoBox = document.getElementById('logo-preview-box');
    if (logoInput && logoBox) {
        logoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoBox.innerHTML = `<img src="${e.target.result}" alt="Logo Preview" class="w-full h-full object-contain p-1">`;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
</script>
@endsection
