<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();


        $policies = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];
        $models = ['apps', 'oauth_clients', 'employee_types', 'employees', 'departments', 'designations', 'users', 'permissions', 'roles'];
        foreach ($policies as $policy) {
            foreach ($models as $model) {
                Permission::create([
                    'name' => "{$policy} {$model}",
                    'guard_name' => "web",
                ]);
            }
        }

        Permission::create([
            'name' => "manage settings",
            'guard_name' => "web",
        ]);

        // this can be done as separate statements
        Role::create(['name' => 'super-admin'])
            ->givePermissionTo(Permission::all());

        Role::create(['name' => 'hr-manager'])
            ->givePermissionTo([
                'viewAny employee_types',
                'view employee_types',
                'create employee_types',
                'update employee_types',
                'delete employee_types',
                'viewAny employees',
                'view employees',
                'create employees',
                'update employees',
                'delete employees',
                'viewAny departments',
                'view departments',
                'create departments',
                'update departments',
                'delete departments',
                'viewAny designations',
                'view designations',
                'create designations',
                'update departments',
                'delete departments',
            ]);

        Role::create(['name' => 'HR Admin'])
            ->givePermissionTo([
                'viewAny employees',
                'view employees',
                'create employees',
                'update employees',
                'delete employees',
            ]);

        Role::create(['name' => 'employee'])
            ->givePermissionTo([]);
    }
}
