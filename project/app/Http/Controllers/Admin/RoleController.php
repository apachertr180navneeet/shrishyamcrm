<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Services\AuditService;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions', 'users')->get();
        $permissions = Permission::all()->groupBy('group');

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'display_name_hindi' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => \Str::slug($request->name, '_'),
            'display_name' => $request->display_name,
            'display_name_hindi' => $request->display_name_hindi,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        AuditService::log('create', 'roles', (string)$role->id, null, ['name' => $role->name]);

        return back()->with('success', "Role '{$role->display_name}' created successfully!");
    }

    public function show(Request $request, $id)
    {
        return $this->edit($request, $id);
    }

    public function edit(Request $request, $id)
    {
        $role = Role::with('permissions', 'users')->findOrFail($id);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($role);
        }

        $roles = Role::with('permissions', 'users')->get();
        $permissions = Permission::all()->groupBy('group');
        $editRole = $role;

        return view('admin.roles.index', compact('roles', 'permissions', 'editRole'));
    }

    public function update(Request $request, $id)
    {
        return $this->updatePermissions($request, $id);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if (in_array($role->name, ['admin', 'super_admin', 'agent', 'member'])) {
            return back()->with('error', "System default role '{$role->display_name}' cannot be deleted.");
        }

        $role->permissions()->detach();
        $role->delete();

        AuditService::log('delete', 'roles', (string)$id, null, ['name' => $role->name]);

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->display_name}' deleted successfully.");
    }

    public function updatePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'display_name' => 'required|string|max:100',
            'display_name_hindi' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'display_name' => $request->display_name,
            'display_name_hindi' => $request->display_name_hindi,
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        AuditService::log('update', 'roles', (string)$role->id, null, [
            'role' => $role->name,
            'permissions_count' => count($request->input('permissions', [])),
        ]);

        return back()->with('success', "Permissions for '{$role->display_name}' updated successfully!");
    }
}
