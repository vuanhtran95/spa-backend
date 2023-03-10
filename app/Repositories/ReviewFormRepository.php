<?php

namespace App\Repositories;

use App\Combo;
use App\Variable;
use App\Helper\Common;
use App\Helper\Translation;
use App\Intake;
use App\Order;
use App\Review;
use App\ReviewForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReviewFormRepository implements ReviewFormRepositoryInterface
{
	public static $OVERTIME_COMMISSION = 'OVERTIME_COMMISSION';
	public static $OVERTIME_COMMISSION_RATE = 'OVERTIME_COMMISSION_RATE';

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
			$is_overtime = false;
			$overtime = Variable::find(self::$OVERTIME_COMMISSION);
			$overtime_rate = Variable::find(self::$OVERTIME_COMMISSION_RATE);

			// Check if this is Overtime intake
			if (!empty($overtime) && !empty($overtime_rate)) {

				$approved_date = Carbon::parse($intake->approved_date, 'UTC')->setTimezone('Asia/Ho_Chi_Minh');
				$day = $approved_date->day;
				$month = $approved_date->month;
				$year = $approved_date->year;
				$over_time =  Carbon::createFromFormat('Y-m-d H:i:s', $year . '-' . $month . '-' . $day . ' ' . $overtime->value, 'Asia/Ho_Chi_Minh');
				$is_overtime = $approved_date->greaterThanOrEqualTo($over_time);
			}

			foreach ($reviews as $reviewOrder) {
				$order = Order::with(['variant' => function ($query) {
					$query->with('service');
				}])->find($reviewOrder['order_id']);
				$percentCommission = Common::calCommissionPercent($reviewOrder['skill'], $reviewOrder['attitude']);

				// Depend on order gender then get the commission rate by gender
				$commission = ($order->variant->commission_rate / 100);

				// Calculate commission base on review star
				$commission = $commission * $percentCommission;

				// Calculate commission base on combo used or not
				if ($order->combo_id) {
					// Update commission for combo
					$commission = $commission * $order->variant->sale_price;
				} else {

					$commission = $commission * $order->price;
				}

				if ($is_overtime) {
					$commission = $commission * $overtime_rate->value;
				}

				$order->working_commission = $commission;
				$order->is_overtime = $is_overtime;
				$order->approved_time = $intake->approved_date;
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
