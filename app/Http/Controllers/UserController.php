<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', strtoupper($request->role));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Metrics
        $totalUsers      = User::count();
        $superAdminCount = User::whereIn('role', ['SUPER_ADMIN', 'SUPERADMIN'])->count();
        $adminCount      = User::where('role', 'ADMIN')->count();
        $accountantCount = User::where('role', 'ACCOUNTANT')->count();
        $auditorCount    = User::where('role', 'AUDITOR')->count();
        $staffCount      = User::whereIn('role', ['STAFF', 'SELLER', 'BUYER'])->count();
        $activeCount     = User::where('is_active', true)->count();

        return view('users.index', compact(
            'users', 'totalUsers', 'superAdminCount', 'adminCount', 'accountantCount', 'auditorCount', 'staffCount', 'activeCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email|max:150',
            'phone'    => 'nullable|string|max:30',
            'role'     => 'required|string|in:SUPER_ADMIN,ADMIN,ACCOUNTANT,AUDITOR,STAFF',
            'password' => 'required|string|min:6|max:100',
            'is_active'=> 'nullable|boolean',
        ]);

        $targetRole = strtoupper($request->role);

        // Security check: Only a Super Admin can create another Super Admin
        if (in_array($targetRole, ['SUPER_ADMIN', 'SUPERADMIN']) && (!Auth::check() || !Auth::user()->isSuperAdmin())) {
            return back()->with('error', 'Only the Super Administrator (Top Owner) has the privilege to create a Super Administrator account.')->withInput();
        }

        try {
            User::create([
                'name'               => trim($request->name),
                'email'              => strtolower(trim($request->email)),
                'phone'              => $request->phone ? trim($request->phone) : null,
                'role'               => $targetRole,
                'password'           => Hash::make($request->password),
                'is_active'          => $request->boolean('is_active', true),
                'created_by_user_id' => Auth::id(),
            ]);

            return back()->with('success', "User '{$request->name}' ({$targetRole}) created successfully.");
        } catch (\Throwable $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            return back()->with('error', 'Could not create user: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => "required|email|max:150|unique:users,email,{$id}",
            'phone'    => 'nullable|string|max:30',
            'role'     => 'required|string|in:SUPER_ADMIN,ADMIN,ACCOUNTANT,AUDITOR,STAFF',
            'password' => 'nullable|string|min:6|max:100',
            'is_active'=> 'nullable|boolean',
        ]);

        $newRole = strtoupper($request->role);

        // Security check: Only a Super Admin can edit or promote a Super Admin
        if (($user->isSuperAdmin() || in_array($newRole, ['SUPER_ADMIN', 'SUPERADMIN'])) && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Only a Super Administrator (Top Owner) has the privilege to edit or assign Super Administrator status.');
        }

        // Safety: Prevent self lockout
        if (Auth::id() === $user->id) {
            if ($user->isSuperAdmin() && !in_array($newRole, ['SUPER_ADMIN', 'SUPERADMIN'])) {
                return back()->with('error', 'You cannot downgrade your own Super Administrator role.');
            }
            if ($user->isAdmin() && !in_array($newRole, ['ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'])) {
                return back()->with('error', 'You cannot downgrade your own Administrator role.');
            }
            if ($request->has('is_active') && !$request->boolean('is_active')) {
                return back()->with('error', 'You cannot deactivate your own account.');
            }
        }

        try {
            $user->name      = trim($request->name);
            $user->email     = strtolower(trim($request->email));
            $user->phone     = $request->phone ? trim($request->phone) : null;
            $user->role      = $newRole;
            $user->is_active = $request->boolean('is_active');

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return back()->with('success', "User '{$user->name}' updated successfully.");
        } catch (\Throwable $e) {
            Log::error('User update failed: ' . $e->getMessage());
            return back()->with('error', 'Could not update user: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Only Super Admin can delete a Super Admin
        if ($user->isSuperAdmin()) {
            if (!Auth::user()->isSuperAdmin()) {
                return back()->with('error', 'Only a Super Administrator (Top Owner) can delete a Super Administrator account.');
            }

            $superAdminCount = User::whereIn('role', ['SUPER_ADMIN', 'SUPERADMIN'])->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Cannot delete the primary/last Super Administrator account.');
            }
        }

        if (strtoupper($user->role) === 'ADMIN') {
            $adminCount = User::where('role', 'ADMIN')->count();
            if ($adminCount <= 1 && !User::whereIn('role', ['SUPER_ADMIN', 'SUPERADMIN'])->exists()) {
                return back()->with('error', 'Cannot delete the last remaining Administrator.');
            }
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "User '{$name}' has been deleted successfully.");
    }
}
