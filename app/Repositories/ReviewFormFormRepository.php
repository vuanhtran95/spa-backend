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

class ReviewFormFormRepository implements ReviewFormRepositoryInterface
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
            return false;
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

                $order = Order::with('variant')->find($reviewOrder['order_id']);
                $employee = Employee::find($order->employee_id);

                $percentCommission = Common::calCommissionPercent($reviewOrder['skill'], $reviewOrder['attitude']);

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


                } else {
                    // Case order doesn't use combo
                    // Collect commission for employee in money pay case
                    /* Deprecated : Store commission in order instead of employee entity*

                    $employee->working_commission =
                        $employee->working_commission +
                        ($order->service->order_commission / 100) * $order->service->price * $percentCommission;
                    $employee->save();
                    */
                }
                $review = new Review();
                $review->order_id = $order->id;
                $review->skill = $reviewOrder['skill'];
                $review->attitude = $reviewOrder['attitude'];
                $review->service_point = $reviewOrder['service_point'];
                $review->review_form_id = $reviewForm->id;
                $review->save();
            }
            return true;
        }
    }
}
