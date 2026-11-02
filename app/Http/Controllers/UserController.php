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
        $adminCount      = User::where('role', 'ADMIN')->count();
        $accountantCount = User::where('role', 'ACCOUNTANT')->count();
        $auditorCount    = User::where('role', 'AUDITOR')->count();
        $staffCount      = User::whereIn('role', ['STAFF', 'SELLER', 'BUYER'])->count();
        $activeCount     = User::where('is_active', true)->count();

        return view('users.index', compact(
            'users', 'totalUsers', 'adminCount', 'accountantCount', 'auditorCount', 'staffCount', 'activeCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email|max:150',
            'phone'    => 'nullable|string|max:30',
            'role'     => 'required|string|in:ADMIN,ACCOUNTANT,AUDITOR,STAFF',
            'password' => 'required|string|min:6|max:100',
            'is_active'=> 'nullable|boolean',
        ]);

        try {
            User::create([
                'name'               => trim($request->name),
                'email'              => strtolower(trim($request->email)),
                'phone'              => $request->phone ? trim($request->phone) : null,
                'role'               => strtoupper($request->role),
                'password'           => Hash::make($request->password),
                'is_active'          => $request->boolean('is_active', true),
                'created_by_user_id' => Auth::id(),
            ]);

            return back()->with('success', "User '{$request->name}' ({$request->role}) created successfully.");
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
            'role'     => 'required|string|in:ADMIN,ACCOUNTANT,AUDITOR,STAFF',
            'password' => 'nullable|string|min:6|max:100',
            'is_active'=> 'nullable|boolean',
        ]);

        // Safety: Prevent admin from locking themselves out
        if (Auth::id() === $user->id) {
            if (strtoupper($request->role) !== 'ADMIN') {
                return back()->with('error', 'You cannot change your own administrator role.');
            }
            if ($request->has('is_active') && !$request->boolean('is_active')) {
                return back()->with('error', 'You cannot deactivate your own account.');
            }
        }

        try {
            $user->name      = trim($request->name);
            $user->email     = strtolower(trim($request->email));
            $user->phone     = $request->phone ? trim($request->phone) : null;
            $user->role      = strtoupper($request->role);
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

        if (strtoupper($user->role) === 'ADMIN') {
            $adminCount = User::where('role', 'ADMIN')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot delete the last remaining Administrator.');
            }
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "User '{$name}' has been deleted successfully.");
    }
}
