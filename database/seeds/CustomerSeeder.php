<?php

use Illuminate\Database\Seeder;
use App\Customer;
use App\Helper\Common;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $query = new Customer();

        $customers = $query::get();
        foreach ($customers as $customer) {
            Common::upRank($customer);
        }
        die(var_dump($customer));
    }
}
