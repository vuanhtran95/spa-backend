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
            ],
            [
                'email' => 'cashier',
                'password' => Hash::make('cashier'),
            ]
        ];
        foreach ($users as $key => $user) {
            User::create($user);
        }
    }
}
