@extends('layouts.app')

@section('title', 'Banking & Accounts')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Banking & Accounts</h1>
            <p class="text-xs text-slate-500">Manage Indian bank accounts, cash drawers, IFSC codes, and fund transfers</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('banking.transfer') }}" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-4 py-2 rounded-xl border border-slate-200 transition">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>Transfer Funds</span>
            </a>
            <button onclick="document.getElementById('addAccountModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
                <i class="fa-solid fa-plus text-white"></i>
                <span class="text-white force-white">Add Bank Account</span>
            </button>
        </div>
    </div>

    <!-- Total Balance Banner -->
    <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm bg-white flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Liquid Reserves</span>
            <h2 class="text-3xl font-black text-slate-900 mt-1">{{ $currencySymbol }}{{ number_format($totalBalance, 2) }}</h2>
            <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved"></i> Synced with {{ count($accounts) }} operational bank & cash accounts
            </p>
        </div>
        <div class="hidden sm:block">
            <a href="{{ route('banking.transactions') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition flex items-center gap-1.5">
                <i class="fa-solid fa-list-check"></i> View Full Ledger
            </a>
        </div>
    </div>

    <!-- Accounts Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($accounts as $acc)
            <div class="glass-card p-5 rounded-2xl border border-slate-200 shadow-sm bg-white relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $acc->name }}</h4>
                        <p class="text-xs text-slate-500 font-medium">{{ $acc->bank_name }}</p>
                    </div>
                    <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-200">
                        <i class="fa-solid fa-building-columns"></i>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Available Balance</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ $currencySymbol }}{{ number_format($acc->current_balance, 2) }}</h3>
                    <div class="mt-2 space-y-0.5 text-[11px] text-slate-500">
                        <p class="font-mono">A/C: <span class="font-semibold text-slate-700">{{ $acc->account_number }}</span></p>
                        @if($acc->ifsc_code)
                            <p class="font-mono">IFSC: <span class="font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">{{ $acc->ifsc_code }}</span></p>
                        @endif
                        @if($acc->branch_name)
                            <p class="truncate">Branch: {{ $acc->branch_name }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center text-[11px] text-slate-500">
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">{{ $acc->account_type ?: 'Current' }}</span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('banking.transactions') }}?bank_account_id={{ $acc->id }}" class="text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1" title="Ledger">
                            <span>Ledger</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                        <button type="button" onclick='openEditAccountModal(@json($acc))' class="p-1 text-slate-400 hover:text-blue-600 transition" title="Edit Account">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <form action="{{ route('banking.accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this bank account? All associated transactions will be permanently deleted.');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="Delete Account">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Account Modal with Indian Banking Fields -->
    <div id="addAccountModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white border border-slate-200 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Add Bank Account (India)</h3>
                        <p class="text-[11px] text-slate-500">Register Indian banking ledger account with IFSC</p>
                    </div>
                </div>
                <button onclick="document.getElementById('addAccountModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('banking.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Account Holder / Name *</label>
                        <input type="text" name="account_name" placeholder="e.g. WebotApp Current A/C" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Bank Name *</label>
                        <input type="text" name="bank_name" placeholder="e.g. State Bank of India, HDFC" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Account Number *</label>
                        <input type="text" name="account_number" placeholder="e.g. 30891283741" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-mono focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Account Type *</label>
                        <select name="account_type" required class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 shadow-sm">
                            <option value="Current Account" selected>Current Account</option>
                            <option value="Savings Account">Savings Account</option>
                            <option value="Cash Credit (CC)">Cash Credit (CC)</option>
                            <option value="Overdraft (OD)">Overdraft (OD)</option>
                            <option value="Cash in Hand / Drawer">Cash in Hand / Drawer</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">IFSC Code *</label>
                        <input type="text" name="ifsc_code" placeholder="e.g. SBIN0001234" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-mono uppercase font-bold focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Branch Name</label>
                        <input type="text" name="branch_name" placeholder="e.g. MG Road Branch, Guwahati" 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">UPI ID / VPA (Optional)</label>
                        <input type="text" name="upi_id" placeholder="e.g. webotapp@sbi" 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Opening Balance ({{ $currencySymbol }}) *</label>
                        <input type="number" name="opening_balance" value="0.00" step="0.01" min="0" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-bold focus:outline-none focus:border-emerald-600 shadow-sm">
                    </div>
                </div>

                <input type="hidden" name="currency" value="INR">

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addAccountModal').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium">Cancel</button>
                    <button type="submit" 
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-white"></i>
                        <span class="text-white force-white">Register Bank Account</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div id="editAccountModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-white border border-slate-200 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Edit Bank Account</h3>
                        <p class="text-[11px] text-slate-500">Update bank account details and ledger balance</p>
                    </div>
                </div>
                <button onclick="document.getElementById('editAccountModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form id="editAccountForm" method="POST" class="space-y-3.5 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Account Holder / Name *</label>
                        <input type="text" name="account_name" id="edit_account_name" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Bank Name *</label>
                        <input type="text" name="bank_name" id="edit_bank_name" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Account Number *</label>
                        <input type="text" name="account_number" id="edit_account_number" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-mono focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Account Type *</label>
                        <select name="account_type" id="edit_account_type" required class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                            <option value="Current Account">Current Account</option>
                            <option value="Savings Account">Savings Account</option>
                            <option value="Cash Credit (CC)">Cash Credit (CC)</option>
                            <option value="Overdraft (OD)">Overdraft (OD)</option>
                            <option value="Cash in Hand / Drawer">Cash in Hand / Drawer</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">IFSC Code</label>
                        <input type="text" name="ifsc_code" id="edit_ifsc_code" 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-mono uppercase font-bold focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Branch Name</label>
                        <input type="text" name="branch_name" id="edit_branch_name" 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">UPI ID / VPA (Optional)</label>
                        <input type="text" name="upi_id" id="edit_upi_id" 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Current Balance ({{ $currencySymbol }}) *</label>
                        <input type="number" name="current_balance" id="edit_current_balance" step="0.01" required 
                            class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-900 font-bold focus:outline-none focus:border-blue-600 shadow-sm">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editAccountModal').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium">Cancel</button>
                    <button type="submit" 
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-md shadow-blue-600/20 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-white"></i>
                        <span class="text-white force-white">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditAccountModal(acc) {
    var form = document.getElementById('editAccountForm');
    var baseAction = "{{ url('banking/accounts') }}";
    form.action = baseAction + '/' + acc.id;
    
    document.getElementById('edit_account_name').value = acc.name || '';
    document.getElementById('edit_bank_name').value = acc.bank_name || '';
    document.getElementById('edit_account_number').value = acc.account_number || '';
    document.getElementById('edit_account_type').value = acc.account_type || 'Current Account';
    document.getElementById('edit_ifsc_code').value = acc.ifsc_code || '';
    document.getElementById('edit_branch_name').value = acc.branch_name || '';
    document.getElementById('edit_upi_id').value = acc.upi_id || '';
    document.getElementById('edit_current_balance').value = acc.current_balance || '0.00';
    
    document.getElementById('editAccountModal').classList.remove('hidden');
}
</script>
@endsection
