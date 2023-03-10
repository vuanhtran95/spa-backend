<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Order;
use App\Variant;
use Illuminate\Support\Facades\DB;
class UpdateUnitPriceColumnToOrdersTable extends Migration
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
        while($current_page < $total_page) {
            $current_page += 1;
            $orders = Order::paginate($per_page,['*'],'page',$current_page);
            foreach ($orders as $order) {
                $variant = Variant::find($order->variant_id);
                $order->unit_price = $variant->sale_price;
                $order->name = $variant->name;
                DB::beginTransaction();
                try {
                    $order->unit_price = $variant->sale_price;
                    $order->save();
                    DB::commit();
                    var_dump('Update order '.$order->id.' with unit_price='.$order->unit_price);
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
