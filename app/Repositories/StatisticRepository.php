<?php

namespace App\Repositories;

use App\Package;
use App\Invoice;
use App\Intake;
use App\ReviewForm;
use Carbon\Carbon;
use App\Employee;
use Illuminate\Support\Facades\DB;

class StatisticRepository implements StatisticRepositoryInterface
{
	public function summary_details(array $query_params = [])
	{
		if (empty($query_params['from']) || empty($query_params['to'])) {
			throw new \Exception("please select start date and end date");
		}
		$from =  Carbon::createFromFormat('Y-m-d', $query_params['from'], 'Asia/Ho_Chi_Minh')->startOfDay()->setTimezone('UTC')->toDateTimeString();
		$to =  Carbon::createFromFormat('Y-m-d', $query_params['to'], 'Asia/Ho_Chi_Minh')->endOfDay()->setTimezone('UTC')->toDateTimeString();
		// $intakes = Intake::->whereBetween(DB::raw('DATE(updated_at)'), array($from, $to))->get();
		$intakes = Intake::where('is_valid', '=', 1)->whereBetween('created_at', [$from, $to])->get();
		$combos = Package::where('is_valid', '=', 1)->whereBetween('created_at', [$from, $to])->with(['employee'])->get();
		$invoices = Invoice::where('status', '=', 'paid')->where('type', '=', 'deposit')->whereBetween('created_at', [$from, $to])->with(['employee'])->get();
		$employees = $this->getEmployeeCommission($from, $to);
		return [
			"intakes" => $intakes,
			"combos" => $combos,
			"invoices" => $invoices,
			"employees" => $employees,
		];
		die(var_dump($to));
		return $intakes->toArray();
	}

	public function get_revenue_between_date($from, $to)
	{
		$revenue = Intake::where('is_valid', '=', 1)
			->where('payment_type', '=', 'cash')
			->whereBetween('created_at', [$from, $to])
			->get()
			->sum('final_price')
			+ Invoice::where('status', '=', 'paid')
			->where('type', '=', 'deposit')
			->whereBetween('created_at', [$from, $to])
			->get()
			->sum('amount')
			+   Package::where('is_valid', '=', 1)
			->whereBetween('created_at', [$from, $to])
			->get()
			->sum('total_price');
		return $revenue;
	}

	public function get()
	{
		$date = Carbon::now()->setTimezone('Asia/Ho_Chi_Minh');
		$date->settings([
			'monthOverflow' => false,
		]);
		$total_revenue = Intake::where('is_valid', '=', 1)
			->where('payment_type', '=', 'cash')
			->get()
			->sum('final_price')
			+ Invoice::where('status', '=', 'paid')
			->where('type', '=', 'deposit')
			->get()
			->sum('amount')
			+   Package::where('is_valid', '=', 1)
			->get()
			->sum('total_price');
		// Get today revenue
		$today_from = $date->copy()
			->startOfDay()
			->setTimezone('UTC')
			->toDateTimeString();
		$today_to = $date->copy()
			->endOfDay()
			->setTimezone('UTC')
			->toDateTimeString();
		$today_revenue = $this->get_revenue_between_date($today_from, $today_to);

		// Get yesterday revenue
		$yesterday_from =  $date->copy()
			->subDays(1)
			->startOfDay()
			->setTimezone('UTC')
			->toDateTimeString();
		$yesterday_to = $date->copy()
			->subDays(1)
			->endOfDay()
			->setTimezone('UTC')
			->toDateTimeString();
		$yesterday_revenue = $this->get_revenue_between_date($yesterday_from, $yesterday_to);

		// Get previous day revenue
		$previous_from =  $date->copy()
			->subDays(2)
			->startOfDay()
			->setTimezone('UTC')
			->toDateTimeString();
		$previous_to = $date->copy()
			->subDays(2)
			->endOfDay()
			->setTimezone('UTC')
			->toDateTimeString();
		$previous_day_revenue = $this->get_revenue_between_date($previous_from, $previous_to);

		// Get this month revenue
		$this_month_from =  $date->copy()
			->startOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();
		$this_month_to = $date->copy()
			->endOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();
		$this_month_revenue = $this->get_revenue_between_date($this_month_from, $this_month_to);

		// Get last month revenue
		$last_month_from =  $date->copy()
			->subMonths(1)
			->startOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();
		$last_month_to = $date->copy()
			->subMonths(1)
			->endOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();
		$last_month_revenue = $this->get_revenue_between_date($last_month_from, $last_month_to);

		// Get previous month revenue
		$previous_month_from =  $date->copy()
			->subMonths(2)
			->startOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();
		$previous_month_to = $date->copy()
			->subMonths(2)
			->endOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();
		$previous_month_revenue = $this->get_revenue_between_date($previous_month_from, $previous_month_to);

		// Get this year revenue
		$this_year_from =  $date->copy()
			->startOfYear()
			->setTimezone('UTC')
			->toDateTimeString();
		$this_year_to = $date->copy()
			->endOfYear()
			->setTimezone('UTC')
			->toDateTimeString();
		$this_year_revenue = $this->get_revenue_between_date($this_year_from, $this_year_to);


		// Get last year revenue
		$last_year_from =  $date->copy()
			->subYears(1)
			->startOfYear()
			->setTimezone('UTC')
			->toDateTimeString();
		$last_year_to = $date->copy()
			->subYears(1)
			->endOfYear()
			->setTimezone('UTC')
			->toDateTimeString();
		$last_year_revenue = $this->get_revenue_between_date($last_year_from, $last_year_to);

		// Summary Points
		$customerSatisfy = ReviewForm::avg('customer_satisfy');
		$facility = ReviewForm::avg('facility');

		return [
			"total_revenue" => $total_revenue,
			"by_date" => [
				"current" => $today_revenue,
				"last" => $yesterday_revenue,
				"previous" => $previous_day_revenue,
			],
			"by_month" => [
				"current" => $this_month_revenue,
				"last" => $last_month_revenue,
				"previous" => $previous_month_revenue,
			],
			"by_year" => [
				"current" => $this_year_revenue,
				"last" => $last_year_revenue,
			],
			"customer_satisfy" => $customerSatisfy,
			"facility" => $facility
		];
	}

	public function getEmployeeCommission($from, $to)
	{
		$query = new Employee();

		$query = $query::where('role_id', 2);
		// With commissions
		$query->withCount(['order AS working_commission' => function ($query) use ($from, $to) {
			$query->whereBetween('created_at', [$from, $to])
				->select(DB::raw("SUM(working_commission)"));
		}]);

		$query->withCount(['package AS sale_commission' => function ($query) use ($from, $to) {
			$query->whereBetween('created_at', [$from, $to])
				->select(DB::raw("SUM(sale_commission)"));
		}]);

		$query->withCount(['invoice AS topup_commission' =>  function ($query) use ($from, $to) {
			$query->whereBetween('created_at', [$from, $to])
				->select(DB::raw("SUM(topup_commission)"));
		}]);

		$employees = $query->orderBy('id', 'desc')
			->get()
			->toArray();
		return $employees;
	}
}
