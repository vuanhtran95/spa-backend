<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Order;
use App\Intake;
use Illuminate\Support\Facades\DB;

class UpdateCustomerIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pagination = Order::paginate();
        // 149 pages
        $total_page = $pagination->lastPage();
        // 15 items perpage
        $per_page = $pagination->perPage();
        $current_page = 0;
        while ($current_page < $total_page) {
            $current_page += 1;
            $orders = Order::paginate($per_page, ['*'], 'page', $current_page);
            foreach ($orders as $order) {
                $intake = Intake::find($order->intake_id);
                DB::beginTransaction();
                try {
                    $order->customer_id = $intake->customer_id;
                    $order->save();
                    DB::commit();
                    var_dump('Update order ID ' . $order->id . ' with customer_id=' . $order->customer_id);
                } catch (\Exception $exception) {
                    DB::rollBack();
                    throw new \Exception($exception->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
}
