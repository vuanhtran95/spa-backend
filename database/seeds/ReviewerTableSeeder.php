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
        $reviewers = [
            [
                'name' => "Reviewer",
                'user_id' => 2,
                'role_id' => 3
            ],
        ];
        foreach ($reviewers as $key => $reviewer) {
            \App\Reviewer::create($reviewer);
        }
    }
}
