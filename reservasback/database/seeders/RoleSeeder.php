<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos de ejemplo
        $permissions = [
            'view_dashboard',
            'manage_users',
            'edit_profile',
            'delete_users',
        ];

        // Crear permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Verificar si el rol 'super-admin' ya existe, si no, crearlo
        if (!Role::where('name', 'super-admin')->exists()) {
            $superAdminRole = Role::create(['name' => 'super-admin']);
            // Asignar permisos al super-admin
            $superAdminRole->givePermissionTo(Permission::all());
        }

        // Verificar si el rol 'emprendedor' ya existe, si no, crearlo
        if (!Role::where('name', 'emprendedor')->exists()) {
            $emprendedorRole = Role::create(['name' => 'emprendedor']);
            // Asignar permisos específicos al emprendedor
            $emprendedorRole->givePermissionTo(['view_dashboard', 'edit_profile']);
        }

        // Verificar si el rol 'cliente' ya existe, si no, crearlo
        if (!Role::where('name', 'cliente')->exists()) {
            $clienteRole = Role::create(['name' => 'cliente']);
            // Asignar permisos específicos al cliente
            $clienteRole->givePermissionTo(['view_dashboard']);
        }
    }
}
