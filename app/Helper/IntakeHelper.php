<?php

namespace App\Helper;

use App\Order;
use App\Variant;
use App\Discount;
use App\Variable;
use App\Repositories\TaskAssignmentRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IntakeHelper
{
    public $RANK_EXTRA_DISCOUNT_ACTIVE = 0;
    public $RANK_EXTRA_DISCOUNT = 0;
    public $rank = null;
    public $discounts = null;

    public $extra_rank_discount_variables = [
        'is_active' => 'RANK_EXTRA_DISCOUNT_ACTIVE',
        'diamond' => 'DIAMOND_EXTRA_DISCOUNT',
        'gold' => 'GOLD_EXTRA_DISCOUNT',
        'silver' => 'SILVER_EXTRA_DISCOUNT',
    ];

    public function __construct($rank = null)
    {
        if (!empty($rank) && $rank !== 'non-member') {
            $this->rank = $rank;
            $this->set_rank_discount_active();
            if (!empty($this->RANK_EXTRA_DISCOUNT_ACTIVE)) {
                $this->set_rank_extra_discount();
            }
        }
        $this->get_discounts();
    }

    public function set_rank_discount_active()
    {
        $id = $this->extra_rank_discount_variables['is_active'];
        $found = Variable::find($id);
        if (!empty($found)) {
            $this->RANK_EXTRA_DISCOUNT_ACTIVE = floatval($found->value);
        }
    }

    public function set_rank_extra_discount()
    {
        $id = $this->extra_rank_discount_variables[$this->rank];
        $found = Variable::find($id);
        if (!empty($found)) {
            $this->RANK_EXTRA_DISCOUNT = floatval($found->value);
        }
    }

    public function get_discounts()
    {
        $date = Carbon::now()->setTimezone('Asia/Ho_Chi_Minh');
        $day = strtolower($date->shortEnglishDayOfWeek);
        $query = new Discount();

        $this->discounts = $query->where('is_active', '=', 1)
                            ->where('from', '<=', $date->format('Y-m-d'))
                            ->where('to', '>=', $date->format('Y-m-d'))
                            ->where('rank_name', '=', $this->rank)->orWhere('rank_name', '=', null)
                            ->where($day, '=', 1)->get()->toArray();
    }

    public function apply_discount($discount, &$amount, &$percentage, &$discount_note)
    {
        ${$discount['type']} += $discount['value'];
        array_push($discount_note, $discount['name']);
        if ($this->rank
            && $discount['rank_name'] === null
            && $this->RANK_EXTRA_DISCOUNT_ACTIVE
            && $this->RANK_EXTRA_DISCOUNT) {
            $percentage += $this->RANK_EXTRA_DISCOUNT;
            array_push(
                $discount_note,
                'Extra discount ('.$this->rank.'): '.$this->RANK_EXTRA_DISCOUNT.'%'
            );
        }
    }

    public function calculateNormalOrderPrice($updateOrder, $variant)
    {
        $price = $variant->price;
        $amount = 0;
        $percentage = 0;
        $discount_note = array();
        if (!empty($this->discounts)) {
            foreach ($this->discounts as $discount) {
                if (
                    $discount['variant_id'] === null
                    && $discount['service_id'] === null
                    && $discount['service_category_id'] === null
                ) {
                    $this->apply_discount($discount, $amount, $percentage, $discount_note);
                    break;
                }
                
                if ($discount['variant_id'] === $variant->id) {
                    $this->apply_discount($discount, $amount, $percentage, $discount_note);
                    break;
                }
                if ($discount['service_id'] === $variant->service_id) {
                    $this->apply_discount($discount, $amount, $percentage, $discount_note);
                    break;
                }
                if ($discount['service_category_id'] === $variant->service->service_category_id) {
                    $this->apply_discount($discount, $amount, $percentage, $discount_note);
                    break;
                }
            }
        }
        if ($amount) {
            $price -= $amount;
        }
        if ($percentage) {
            $price = $price*((100 - $percentage)/100);
        }
        $updateOrder->price = $price;
        $updateOrder->discount_amount = $amount;
        $updateOrder->discount_percentage = $percentage;
        $updateOrder->discount_note = join("<br>", $discount_note);
        $updateOrder->save();
        return $price;
    }
    
    public function calculatePromotionOrderPrice($updateOrder, $variant)
    {
        $price = $variant->price;
        if (
            $this->rank
            && $this->RANK_EXTRA_DISCOUNT_ACTIVE
            && $this->RANK_EXTRA_DISCOUNT) {
                $price = $price*((100 -  $this->RANK_EXTRA_DISCOUNT)/100);
                $updateOrder->discount_note = 'Extra discount ('.$this->rank.'): '.$this->RANK_EXTRA_DISCOUNT.'%';
        }
        $updateOrder->price = $price;
        $updateOrder->save();
        return $price;
    }

    public function processOrderPrice($updateOrder,  $variant)
    {
        // Not Calculate the free variant
        if ($variant->is_free) {
            return 0;
        }
        // Handle Service Order
        if (!empty($updateOrder->promotion_hash)) {
            return $this->calculatePromotionOrderPrice($updateOrder, $variant);
        }
        return $this->calculateNormalOrderPrice($updateOrder, $variant);
    }

    public function order_pre_process($updateOrder,  $variant,$customer) {
        if($variant->service['serviceCategory']->name === 'facials') {
            $taskAssignmentRepository = new taskAssignmentRepository();
            $date = Carbon::createFromFormat('Y-m-d H:i:s',$updateOrder->created_at, 'Asia/Ho_Chi_Minh')->format('d/m/Y');
            
            $message = 'Nhắn tin hỏi thăm khách hàng<br><strong>'.$customer->name.'</strong><br>SĐT: <a href="tel:'.$customer->phone.'">'.$customer->phone.'</a><br>Đã làm dịch vụ <i>'.$variant->name.'</i><br>Ngày <u>'.$date.'</u>';
            $taskAssignmentRepository->createReminder([
                'title'=> $message,
                'employee_id'=>$updateOrder->employee_id,
            ]);
        }
    }
}
