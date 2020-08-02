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
        ];
        foreach ($users as $key => $user) {
            \App\Employee::create($user);
        }
    }
}
