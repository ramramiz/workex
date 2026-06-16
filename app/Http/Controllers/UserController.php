<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserEmail;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['role', 'emails'])->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"))->latest()->paginate(20);
        $roles = Role::all();
        return view('settings.users.index', compact('users', 'roles'));
    }
    public function create() { $roles = Role::all(); return view('settings.users.create', compact('roles')); }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'role_id' => 'required|exists:roles,id', 'password' => 'required|min:8|confirmed']);
        User::create(['name' => $request->name, 'email' => $request->email, 'role_id' => $request->role_id, 'password' => Hash::make($request->password), 'status' => 'active', 'email_verified_at' => now()]);
        return redirect()->route('users.index')->with('success', 'User created!');
    }
    public function edit(User $user) { $roles = Role::all(); return view('settings.users.edit', compact('user', 'roles')); }
    public function update(Request $request, User $user)
    {
        $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email,' . $user->id, 'role_id' => 'required|exists:roles,id']);
        $data = $request->only(['name', 'email', 'role_id', 'status']);
        if ($request->password) { $data['password'] = Hash::make($request->password); }
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'User updated!');
    }
    public function quickStoreTeamLeader(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $role = Role::where('slug', 'team-leader')->first();
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Team Leader role not found.'], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $role->id,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'team_leader' => $user
        ]);
    }
    public function destroy(User $user) { if ($user->id === auth()->id()) return back()->with('error', 'Cannot delete yourself.'); $user->delete(); return redirect()->route('users.index')->with('success', 'User deleted.'); }

    public function emails(User $user)
    {
        $user->load('emails');
        return view('settings.users.emails', compact('user'));
    }

    public function storeEmail(Request $request, User $user)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'unique:user_emails,email',
            ]
        ], [
            'email.unique' => 'This email address is already in use.',
        ]);

        $user->emails()->create([
            'email' => $request->email,
        ]);

        return redirect()->back()->with('success', 'Email account added successfully.');
    }

    public function destroyEmail(User $user, UserEmail $email)
    {
        if ($email->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $email->delete();

        return redirect()->back()->with('success', 'Email account removed successfully.');
    }
}
