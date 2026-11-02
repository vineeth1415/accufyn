@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>User Management &amp; RBAC</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-violet-50 text-violet-700 border border-violet-200 font-semibold">Security</span>
            </h1>
            <p class="text-xs text-slate-400">Manage user accounts, roles, access permissions, and auditor privileges</p>
        </div>
        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" 
            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i class="fa-solid fa-user-plus text-white"></i>
            <span class="text-white force-white">Add User</span>
        </button>
    </div>

    <!-- Role Privileges Info Banner -->
    <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
        <div class="p-3 rounded-xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800/50 space-y-1">
            <div class="flex items-center gap-1.5 font-bold text-purple-700 dark:text-purple-300">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Administrator</span>
            </div>
            <p class="text-[11px] text-slate-600 dark:text-slate-400">Full system control, user accounts, company settings, updates &amp; AI engine.</p>
        </div>

        <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/50 space-y-1">
            <div class="flex items-center gap-1.5 font-bold text-blue-700 dark:text-blue-300">
                <i class="fa-solid fa-calculator"></i>
                <span>Accountant</span>
            </div>
            <p class="text-[11px] text-slate-600 dark:text-slate-400">Invoices, bills, vendors, banking, custom ledgers, inventory &amp; reports.</p>
        </div>

        <div class="p-3 rounded-xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 space-y-1">
            <div class="flex items-center gap-1.5 font-bold text-amber-700 dark:text-amber-300">
                <i class="fa-solid fa-file-excel"></i>
                <span>Auditor (Restricted)</span>
            </div>
            <p class="text-[11px] text-slate-600 dark:text-slate-400"><strong>View &amp; Excel Export only</strong> for financial reports &amp; ledgers for tax filing.</p>
        </div>

        <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 space-y-1">
            <div class="flex items-center gap-1.5 font-bold text-emerald-700 dark:text-emerald-300">
                <i class="fa-solid fa-user"></i>
                <span>Staff / Sales</span>
            </div>
            <p class="text-[11px] text-slate-600 dark:text-slate-400">Operations: Invoices, customers, bills, vendors, and product inventory.</p>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-slate-500">Total Users</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $totalUsers }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-purple-600 dark:text-purple-400">Admins</p>
            <p class="text-xl font-bold text-purple-700 dark:text-purple-300 mt-0.5">{{ $adminCount }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-blue-600 dark:text-blue-400">Accountants</p>
            <p class="text-xl font-bold text-blue-700 dark:text-blue-300 mt-0.5">{{ $accountantCount }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-amber-600 dark:text-amber-400">Auditors</p>
            <p class="text-xl font-bold text-amber-700 dark:text-amber-300 mt-0.5">{{ $auditorCount }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">Staff</p>
            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300 mt-0.5">{{ $staffCount }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-slate-500">Active Accounts</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $activeCount }}</p>
        </div>
    </div>

    <!-- Search & Filter Filter Bar -->
    <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <form action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone..." 
                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-9 pr-3.5 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="role" class="bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                    <option value="">All Roles</option>
                    <option value="ADMIN" {{ request('role') === 'ADMIN' ? 'selected' : '' }}>Administrator</option>
                    <option value="ACCOUNTANT" {{ request('role') === 'ACCOUNTANT' ? 'selected' : '' }}>Accountant</option>
                    <option value="AUDITOR" {{ request('role') === 'AUDITOR' ? 'selected' : '' }}>Auditor</option>
                    <option value="STAFF" {{ request('role') === 'STAFF' ? 'selected' : '' }}>Staff</option>
                </select>

                <select name="status" class="bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('users.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 underline">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="glass-card rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-white dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/90 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">User</th>
                        <th class="py-3.5 px-4">Role &amp; Privileges</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Joined Date</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($users as $u)
                        @php
                            $badge = $u->role_badge;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span>{{ $u->name }}</span>
                                            @if(Auth::id() === $u->id)
                                                <span class="text-[10px] font-normal px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 border border-emerald-200">You</span>
                                            @endif
                                        </p>
                                        <p class="text-[11px] text-slate-500 font-mono">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $badge['class'] }}">
                                    <i class="fa-solid {{ $badge['icon'] }} text-[10px]"></i>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                <div>{{ $u->phone ?: 'No phone' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Active</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>Inactive</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $u->created_at ? $u->created_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" onclick="openEditUserModal({{ json_encode($u) }})" 
                                    class="p-1.5 px-2.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-emerald-600 hover:text-emerald-700 transition" 
                                    title="Edit User">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                
                                @if(Auth::id() !== $u->id)
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline" 
                                        onsubmit="return confirm('Are you sure you want to delete user \'{{ $u->name }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="p-1.5 px-2.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition" 
                                            title="Delete User">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-users-slash text-3xl mb-2"></i>
                                <p class="text-sm">No users found matching your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Add New User</h3>
            </div>
            <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. John Doe"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                    <input type="email" name="email" required placeholder="user@company.com"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="+91 9876543210"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">System Role &amp; Permissions *</label>
                <select name="role" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-medium">
                    <option value="STAFF">Staff / Sales (Invoices, Customers, Bills, Items)</option>
                    <option value="ACCOUNTANT">Accountant (Invoices, Bills, Banking, Custom Ledgers, Reports)</option>
                    <option value="AUDITOR" selected>Auditor (Reports View &amp; Excel Export Only for Filing)</option>
                    <option value="ADMIN">Administrator (Full Access &amp; Settings)</option>
                </select>
                <p class="text-[10px] text-slate-500 mt-1">
                    * <strong>Auditor:</strong> Specially restricted to viewing financial reports &amp; custom ledgers, and exporting Excel data for tax and audit filing.
                </p>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Password *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimum 6 characters"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="add_is_active" name="is_active" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                <label for="add_is_active" class="font-medium text-slate-700 dark:text-slate-300">Account is Active</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold shadow-sm">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-user-pen text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit User Account</h3>
            </div>
            <button onclick="document.getElementById('editUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form id="editUserForm" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                <input type="text" id="edit_name" name="name" required
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                    <input type="email" id="edit_email" name="email" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" id="edit_phone" name="phone"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">System Role &amp; Permissions *</label>
                <select id="edit_role" name="role" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-medium">
                    <option value="STAFF">Staff / Sales (Invoices, Customers, Bills, Items)</option>
                    <option value="ACCOUNTANT">Accountant (Invoices, Bills, Banking, Custom Ledgers, Reports)</option>
                    <option value="AUDITOR">Auditor (Reports View &amp; Excel Export Only for Filing)</option>
                    <option value="ADMIN">Administrator (Full Access &amp; Settings)</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">New Password (leave blank to keep current)</label>
                <input type="password" name="password" minlength="6" placeholder="Enter new password if resetting"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                <label for="edit_is_active" class="font-medium text-slate-700 dark:text-slate-300">Account is Active</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditUserModal(user) {
        const form = document.getElementById('editUserForm');
        form.action = '{{ url("/users") }}/' + user.id;

        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_role').value = (user.role || 'STAFF').toUpperCase();
        document.getElementById('edit_is_active').checked = !!user.is_active;

        document.getElementById('editUserModal').classList.remove('hidden');
    }
</script>
@endsection
