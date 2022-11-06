<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name'=>'cash'],
            ['name'=>'momo'],
            ['name'=>'bank_transfer'],
            ['name'=>'credit'],
            ['name'=>'card'],
        ];
        DB::table('payment_methods')->insert($data); // Query Builder approach
    }
}
