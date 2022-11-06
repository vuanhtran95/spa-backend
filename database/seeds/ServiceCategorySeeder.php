<?php

use Illuminate\Database\Seeder;
use App\ServiceCategory;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'id' => 1,
                'name' => 'Chưa phân loại',
                'descriptions' => 'Những dịch vụ chưa được phân loại'
            ],
        ];
        foreach ($categories as $key => $category) {
            ServiceCategory::create($category);
        }
    }
}
