<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
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
                'email' => 'admin',
                'password' => Hash::make('admin'),
            ],
            [
                'email' => 'reviewer',
                'password' => Hash::make('reviewer'),
            ]
        ];
        foreach ($users as $key => $user) {
            User::create($user);
        }
    }
}
