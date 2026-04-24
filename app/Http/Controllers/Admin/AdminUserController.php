<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller implements HasMiddleware
{
    protected $title = "User";
    protected $permissionName = 'user';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view:user', only: ['index']),
            new Middleware('permission:create:user', only: ['store']),
            new Middleware('permission:edit:user', only: ['update']),
            new Middleware('permission:delete:user', only: ['destroy']),
        ];
    }

    public function index(TableRequest $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('perPage', 10);

        $validPerPage = in_array($perPage, [10, 50, 100]) ? $perPage : 10;

        $roles = Role::all();

        if ($search) {
            $users = User::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->paginate($validPerPage);
        } else {
            $users = User::withTrashed()->paginate($validPerPage);
        }

        $permission = $this->permissionName;

        return view('admin.user.index', compact('users', 'roles', 'search', 'perPage', 'permission'));
    }

    public function store(UserStoreRequest $request)
    {
        $userData = $request->validated();

        if ($request->email_verified) {
            $userData->email_verified_at = now();
        } else {
            $userData->email_verified_at = null;
        }

        if (!empty($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        $role = $userData['role'];
        unset($userData['role']);

        $user = User::create($userData);

        $user->assignRole($role);

        return back()->with('success', 'Successfully Create ' . $this->title . '!');
    }

    public function update(UserUpdateRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $userData = $request->validated();

        if ($request->email_verified == '1') {
            $user->email_verified_at = now();
        } else {
            $user->email_verified_at = null;
        }

        if (!empty($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        } else {
            unset($userData['password']);
        }

        $role = $userData['role'];
        unset($userData['role']);

        $user->update($userData);

        $user->syncRoles($role);

        return back()->with('success', 'Successfully Edit ' . $this->title . '!');
    }


    public function destroy(string $id)
    {
        User::findOrFail($id)->forceDelete();
        return back()->with('success', 'Successfully Delete ' . $this->title . '!');
    }
}
