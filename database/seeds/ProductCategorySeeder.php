<?php

use Illuminate\Database\Seeder;
use App\ServiceCategory;

class ProductCategorySeeder extends Seeder
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
                'name' => 'goods',
                'descriptions' => 'Sản phẩm',
                'is_active' => true,
            ],
        ];
        foreach ($categories as $key => $category) {
            ServiceCategory::create($category);
        }
    }
}
