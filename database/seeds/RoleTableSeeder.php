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
            ],
            [
                'name' => 'ktv',
            ],
            [
                'name' => 'reviewer'
            ]
        ];
        foreach ($roles as $key => $role) {
            Role::create($role);
        }
    }
}
