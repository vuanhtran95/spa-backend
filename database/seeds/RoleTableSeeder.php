<?php

use Illuminate\Database\Seeder;
use App\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'admin',
                'descriptions' => 'Administrator'
            ],
            [
                'name' => 'customer',
                'descriptions' => 'Customer'
            ],
            [
                'name' => 'Employee',
                'descriptions' => 'employee'
            ],
        ];
        foreach ($roles as $key => $role) {
            Role::create($role);
        }
    }
}
