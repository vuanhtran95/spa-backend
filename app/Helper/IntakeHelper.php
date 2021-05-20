<?php

namespace App\Helper;

use App\Order;
use App\Variant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IntakeHelper
{

    public $rank_name = null;

    public function __construct()
    {
        // $this->discounts = DB::table('configs')
        //     ->join('config_categories', 'configs.config_category_id', '=', 'config_categories.id')
        //     ->where('config_categories.name', '=', self::DISCOUNT_CATEGORY)
        //     ->select('configs.*', 'config_categories.name as category_name')
        //     ->get()
        //     ->toArray();
        $this->discounts = [];
    }

    public function getRankDiscountConfig($rank)
    {
        $this->rank_discounts =  array_filter($this->discounts, function ($config) use ($rank) {
            return $config->condition_type === self::RANK_CONDITION && $config->condition_value === $rank;
        });
    }
    public function getServiceRankDiscount()
    {
        if (!count($this->rank_discounts)) {
            return null;
        }
        $found = null;
        foreach ($this->rank_discounts as $discount) {
            if ($discount->name == 'service') {
                $found = $discount;
                break;
            }
        }
        return $found;
    }
    public function getPromotionRankDiscount()
    {
        if (!count($this->rank_discounts)) {
            return null;
        }
        $found = null;
        foreach ($this->rank_discounts as $discount) {
            if ($discount->name == 'promotion') {
                $found = $discount;
                break;
            }
        }
        return $found;
    }
    public function calculateNormalOrderPrice($updateOrder, $variant)
    {
        $price = 0;
        $discount = $this->getServiceRankDiscount();
        if (empty($discount)) {
            $price = $variant->price;
        } else {
            $price = $variant->price*(1 - $discount->value);
        }
        $updateOrder->price = $price;
        $updateOrder->save();
        return $price;
    }
    
    public function calculatePromotionOrderPrice($updateOrder, $variant)
    {
        $price = 0;
        $discount = $this->getPromotionRankDiscount();
        if (empty($discount)) {
            $price = $variant->price;
        } else {
            $price = $variant->price*(1 - $discount->value);
        }
        $updateOrder->price = $price;
        $updateOrder->save();
        return $price;
    }

    public function processOrderPrice($order_id)
    {
        $dt = Carbon::now()->setTimezone('Asia/Ho_Chi_Minh');
        $updateOrder = Order::find($order_id);
        $variant = Variant::find($updateOrder->variant_id);
        // Not Calculate the free variant
        if ($variant->is_free) {
            return 0;
        }
        // Handle Service Order
        if (empty($updateOrder->promotion_hash)) {
            return $this->calculatePromotionOrderPrice($updateOrder, $variant);
        }
        return $this->calculateNormalOrderPrice($updateOrder, $variant);
    }
}
