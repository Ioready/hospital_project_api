<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Hospital']);
        Role::create(['name' => 'Doctor']);
        Role::create(['name' => 'Nurses']);
        Role::create(['name' => 'Accountant']);
        Role::create(['name' => 'Employee']);
        Role::create(['name' => 'Staff']);
        Role::create(['name' => 'Patient']);
    }
}
