<?php

namespace App\Repositories;

use App\Combo;
use App\Employee;
use App\Helper\Common;
use App\Helper\Translation;
use App\Intake;
use App\Order;
use App\Review;
use App\ReviewForm;
use Illuminate\Support\Facades\DB;

class ReviewFormRepository implements ReviewFormRepositoryInterface
{

    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $review = $this->save($attributes, false);
            DB::commit();
            return $review;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            // TODO: no need to do
            throw new \Exception("Function has not been implemented");
        } else {
            // Create
            $intake_id = $data['intake_id'];
            $intake = Intake::find($intake_id);

            // Check has intake ?
            if (!$intake) {
                throw new \Exception(Translation::$NO_INTAKE_FOUND);
            }

            // Check is valid ?
            if (!$intake->is_valid) {
                throw new \Exception(Translation::$INTAKE_NOT_APPROVE);
            }

            // Check already reviewed ?
            $hasReviewForm = ReviewForm::where('intake_id', $intake_id)->first();
            if ($hasReviewForm) {
                throw new \Exception(Translation::$INTAKE_ALREADY_REVIEWED);
            }

            $reviewForm = new ReviewForm();
            $reviewForm->intake_id = $intake_id;
            $reviewForm->facility = $data['facility'];
            $reviewForm->customer_satisfy = $data['customer_satisfy'];
            $reviewForm->note = $data['note'];

            $reviewForm->save();

            $reviews = $data['reviews'];

            foreach ($reviews as $reviewOrder) {

                $order = Order::with(['variant' => function($query) {$query->with('service');}])->find($reviewOrder['order_id']);
                $percentCommission = Common::calCommissionPercent($reviewOrder['skill'], $reviewOrder['attitude']);

                // Depend on order gender then get the commission rate by gender
                switch ($order->variant->gender) {
                    case 'male':
                        $commission = ($order->variant->service->commission_rate_male / 100);
                        break;
                    case 'female':
                        $commission = ($order->variant->service->commission_rate_female / 100);
                        break;
                    default:
                        // for both
                        $commission = ($order->variant->service->commission_rate_both / 100);
                        break;
                }

                // Calculate commission base on review star
                $commission = $commission * $percentCommission;

                // Calculate commission base on combo used or not
                if ($order->combo_id) {
                    // Case order use combo

                    $combo = Combo::find($order->combo_id);

                    // Collect commission for employee in combo used case
                    /* Deprecated : Store commission in order instead of employee entity*
                    $employee->working_commission =
                        $employee->working_commission +
                        ($order->service->order_commission / 100) * ($combo->total_price / $combo->amount) * $percentCommission;
                    $employee->save();
                     */

                    $commission = $commission * $combo->total_price / $combo->amount;


                } else {
                    // Case order doesn't use combo
                    // Collect commission for employee in money pay case
                    /* Deprecated : Store commission in order instead of employee entity*

                    $employee->working_commission =
                        $employee->working_commission +
                        ($order->service->order_commission / 100) * $order->service->price * $percentCommission;
                    $employee->save();
                    */

                    $commission = $commission * $order->price;
                }

                $order->working_commission = $commission;
                $order->save();


                $review = new Review();
                $review->order_id = $order->id;
                $review->skill = $reviewOrder['skill'];
                $review->attitude = $reviewOrder['attitude'];
                $review->service_point = $reviewOrder['service_point'];
                $review->review_form_id = $reviewForm->id;
                $review->save();
            }

            return ReviewForm::with('review')->find($reviewForm->id);
        }
    }
}
