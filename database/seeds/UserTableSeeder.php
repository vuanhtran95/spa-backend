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
                'name' => 'Victor',
                'email' => 'vuanhtran95',
                'password' => Hash::make('P2ssword1!'),
                'role_id' => 1
            ],
        ];
        foreach ($users as $key => $user) {
            User::create($user);
        }
    }
}
