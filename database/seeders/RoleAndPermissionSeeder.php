<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        $entities = [
            'dashboard' => ['admin', 'user'],
            'user' => ['view', 'create', 'edit', 'delete'],
            'role' => ['view', 'create', 'edit', 'delete'],
            'domain' => ['view', 'create', 'edit', 'delete'],
            'subdomain' => ['view', 'create', 'edit', 'delete'],
            'server' => ['view', 'create', 'edit', 'delete'],
            'github-ssh' => ['view', 'create', 'edit', 'delete'],
            'server-task' => ['view', 'execute'],
        ];

        foreach ($entities as $entity => $actions) {
            foreach ($actions as $action) {
                Permission::updateOrCreate(['name' => "{$action}:{$entity}"]);
            }
        }

        $roles = [
            'super-admin' => Permission::all()
                ->pluck('name')
                ->toArray(),
            'admin' => Permission::where('name', 'not like', '%:role')
                ->where('name', 'not like', '%:user')
                ->where('name', 'not like', '%-all:%')
                ->pluck('name')
                ->toArray(),
            'user' => [
                'user:dashboard',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::updateOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }
    }
}
