<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Requests\TableRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class AdminRoleController extends Controller implements HasMiddleware
{
    protected $title = "Role";

    // Middleware for role permissions
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:role', only: ['index']),
            new Middleware('permission:create:role', only: ['store']),
            new Middleware('permission:edit:role', only: ['update']),
            new Middleware('permission:delete:role', only: ['destroy']),
        ];
    }

    // Display a listing of the resource
    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);
        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        // Ambil roles dengan permissions
        $roles = Role::with('permissions')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->paginate($validPerPage);

        // Ambil semua permissions untuk create/edit modal
        $permissions = Permission::all()->map(function ($perm) {
            [$action, $entity] = explode(':', $perm->name) + [null, 'other'];

            $perm->action = $action;
            $perm->entity = $entity;
            $perm->badgeClass = match (true) {
                str_contains(strtolower($action), 'view') => 'badge-dark',
                str_contains(strtolower($action), 'create') => 'badge-primary',
                str_contains(strtolower($action), 'edit') => 'badge-warning',
                str_contains(strtolower($action), 'delete') => 'badge-danger',
                str_contains(strtolower($action), 'restore') => 'badge-secondary',
                str_contains(strtolower($action), 'import') => 'badge-info',
                str_contains(strtolower($action), 'export') => 'badge-success',
                default => 'badge-dark',
            };
            return $perm;
        });

        return view('admin.role.index', compact('roles', 'permissions', 'search', 'perPage'));
    }

    // Handle store data role
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->pluck('name')->toArray();
            $role->syncPermissions($permissions);
        }

        return back()->with('success', 'Successfully Create Data ' . $this->title . '!');
    }

    // Handle update data role
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->pluck('name')->toArray();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return back()->with('success', 'Successfully Edit Data ' . $this->title . '!');
    }

    // Handle delete data role
    public function destroy($id)
    {
        Role::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully Delete Data ' . $this->title . '!');
    }
}
