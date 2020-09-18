<?php

use Illuminate\Database\Seeder;

class ReviewerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reviewer = [
            [
                'name' => "Reviewer",
                'user_id' => 2,
                'role_id' => 3
            ],
        ];
        foreach ($users as $key => $user) {
            \App\Employee::create($user);
        }
    }
}
