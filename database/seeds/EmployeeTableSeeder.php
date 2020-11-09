<?php

use Illuminate\Database\Seeder;

class EmployeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => "Victor",
                'user_id' => 1,
                'role_id' => 1
            ],
            [
                'name' => "Reviewer",
                'user_id' => 2,
                'role_id' => 3
            ],
            [
                'name' => "Cashier",
                'user_id' => 3,
                'role_id' => 4
            ],
        ];
        foreach ($users as $key => $user) {
            \App\Employee::create($user);
        }
    }
}
