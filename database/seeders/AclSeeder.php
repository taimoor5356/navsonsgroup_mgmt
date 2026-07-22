<?php

namespace Database\Seeders;

use App\Models\PermissionModule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $namespace = 'App\\Models';
        $modelsPath = app_path('Models');
        $modelFiles = File::files($modelsPath);

        // Create Permission Modules dynamically
        foreach ($modelFiles as $modelFile) {
            $fileName = pathinfo($modelFile, PATHINFO_FILENAME);
            $className = $namespace . '\\' . $fileName;
            $camelCaseString = $fileName;
            $wordsWithSpaces = $this->camelCaseToWords($camelCaseString);

            if (class_exists($className)) {
                if (!PermissionModule::where('name', $wordsWithSpaces)->exists()) {
                    PermissionModule::create([
                        'name' => strtolower($wordsWithSpaces)
                    ]);
                }
            }
        }

        // Create permissions for each module
        $permissionModules = PermissionModule::get();
        $permissionActions = ['view', 'create', 'update', 'delete'];

        foreach ($permissionModules as $permissionModule) {
            foreach ($permissionActions as $action) {
                $permissionName = strtolower($permissionModule->name . '_' . $action);
                if (!Permission::where('name', $permissionName)->exists()) {
                    Permission::create([
                        'module_id' => $permissionModule->id,
                        'name' => $permissionName,
                        'guard_name' => 'web'
                    ]);
                }
            }
        }

        $permissions = Permission::get();

        // Filter permissions for each role
        $managerPermissions = $permissions->filter(function ($perm) {
            return str_contains($perm->name, '_view') || str_contains($perm->name, '_create');
        });

        $userPermissions = $permissions->filter(function ($perm) {
            return str_contains($perm->name, '_view');
        });

        // Create Admin (All permissions)
        $this->createUserWithRole('Admin', 'admin@navsonsgroup.com', '12345678', 'admin', $permissions, 1);

        // Create Manager (View + Create only)
        $this->createUserWithRole('Manager', 'manager@navsonsgroup.com', '12345678', 'manager', $managerPermissions, 2);

        // Create Normal User (View only)
        $this->createUserWithRole('User', 'user@navsonsgroup.com', '12345678', 'user', $userPermissions, 3);
    }

    /**
     * Convert camelCase to snake_case
     */
    function camelCaseToWords($input)
    {
        return preg_replace('/(?<=\\w)(?=[A-Z])/', '_', $input);
    }

    /**
     * Helper to create user and assign role
     */
    function createUserWithRole($name, $email, $password, $roleName, $permissions = null, $userType = 2)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->user_type = $userType;
            $user->save();
        }

        $role = Role::updateOrCreate([
            'name' => $roleName,
            'guard_name' => 'web'
        ]);

        $user->assignRole($role);

        if ($permissions) {
            $role->syncPermissions($permissions);
        }

        return $user;
    }
}
