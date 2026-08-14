<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Agent;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditService;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'roleModel', 'agent'])->latest()->paginate(15);
        $roles = Role::all();
        $agents = Agent::where('status', 'Active')->get();

        return view('admin.users.index', compact('users', 'roles', 'agents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'slug' => \Str::slug($request->first_name . '-' . $request->last_name . '-' . rand(100, 999)),
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $role->name,
            'role_id' => $role->id,
            'agent_id' => $request->agent_id ?: null,
            'country' => 'India',
            'status' => 'active',
        ]);

        $user->roles()->sync([$role->id]);

        AuditService::log('create', 'users', (string)$user->id, null, ['name' => $user->full_name, 'role' => $role->name]);

        return back()->with('success', "User {$user->full_name} created successfully with role {$role->display_name}.");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $role->name,
            'role_id' => $role->id,
            'agent_id' => $request->agent_id ?: null,
            'status' => $request->status ?? 'active',
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        $user->roles()->sync([$role->id]);

        AuditService::log('update', 'users', (string)$user->id, null, ['name' => $user->full_name, 'role' => $role->name]);

        return back()->with('success', "User {$user->full_name} updated successfully.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        AuditService::log('delete', 'users', (string)$id);

        return back()->with('success', 'User deleted successfully.');
    }
}
