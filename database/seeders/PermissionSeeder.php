<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $allRoles = Role::all()->keyBy('id');
 
        $permissions = [
            'super-admin-manage' => [Role::ROLE_SUPERADMIN],
            'admin-manage' => [Role::ROLE_SUPERADMIN],
            'hospital-manage' => [Role::ADMIN],
            'doctor-manage' => [Role::HOSPITAL],
            'nurse-manage' => [Role::HOSPITAL],
            'accountant-manage' => [Role::HOSPITAL],
            'staff-manage' => [Role::HOSPITAL],
            'employee-manage' => [Role::HOSPITAL],
            'patient-manage' => [Role::HOSPITAL],
        ];
 
        foreach ($permissions as $key => $roles) {
            $permission = Permission::create(['name' => $key]);
            foreach ($roles as $role) {
                $allRoles[$role]->permissions()->attach($permission->id);
            }
        }
    }
}
