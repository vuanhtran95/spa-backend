<?php

namespace App\Repositories;

use App\Package;
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
        $from =  Carbon::createFromFormat('Y-m-d H:i:s', $query_params['from'], 'Asia/Ho_Chi_Minh')->setTimezone('UTC')->toDateTimeString();
        $to =  Carbon::createFromFormat('Y-m-d H:i:s', $query_params['to'], 'Asia/Ho_Chi_Minh')->setTimezone('UTC')->toDateTimeString();
        // $intakes = Intake::->whereBetween(DB::raw('DATE(updated_at)'), array($from, $to))->get();
        $intakes = Intake::where('is_valid', '=', 1)->whereBetween('updated_at', [$from, $to])->get();
        $combos = Package::where('is_valid', '=', 1)->whereBetween('created_at', [$from, $to])->get();
        $employees = $this->getEmployeeCommission($from, $to);
        return [
            "intakes" => $intakes,
            "combos" => $combos,
            "employees" => $employees,
        ];
        die(var_dump($to));
        return $intakes->toArray();
    }

    public function get()
    {
        $current_date = Carbon::now();
        $total_revenue = Intake::where('is_valid', '=', 1)
                                ->get()
                                ->sum('final_price')
                    +   Package::where('is_valid', '=', 1)
                                ->get()
                                ->sum('total_price');
        // get Total revenue by year
        $current_year = Carbon::now()->year;
        $last_year =  Carbon::now()->subYear()->year;

        $current_year_revenue = Intake::where('is_valid', '=', 1)->whereYear('updated_at', $current_year)
                                ->get()
                                ->sum('final_price')
                    +   Package::where('is_valid', '=', 1)->whereYear('created_at', $current_year)
                                ->get()
                                ->sum('total_price');
        $last_year_revenue = Intake::where('is_valid', '=', 1)->whereYear('updated_at', $last_year)
                                ->get()
                                ->sum('final_price')
                    +   Package::where('is_valid', '=', 1)->whereYear('created_at', $last_year)
                                ->get()
                                ->sum('total_price');

        // get Total revenue by Month
        $current_month = Carbon::now()->month;
        $last_month = Carbon::now()->subMonth()->month;
        $previous_month = Carbon::now()->subMonth()->subMonth()->month;

        $current_month_revenue = Intake::where('is_valid', '=', 1)
                                        ->whereYear('updated_at', $current_year)
                                        ->whereMonth('updated_at', $current_month)
                                        ->get()
                                        ->sum('final_price')
                            +   Package::where('is_valid', '=', 1)
                                        ->whereYear('created_at', $current_year)
                                        ->whereMonth('created_at', $current_month)
                                        ->get()
                                        ->sum('total_price');
        $last_month_revenue = Intake::where('is_valid', '=', 1)
                                        ->whereYear('updated_at', $current_year)
                                        ->whereMonth('updated_at', $last_month)
                                        ->get()
                                        ->sum('final_price')
                            +   Package::where('is_valid', '=', 1)
                                        ->whereYear('created_at', $current_year)
                                        ->whereMonth('created_at', $last_month)
                                        ->get()
                                        ->sum('total_price');
        $previous_month_revenue = Intake::where('is_valid', '=', 1)
                                        ->whereYear('updated_at', $current_year)
                                        ->whereMonth('updated_at', $previous_month)
                                        ->get()
                                        ->sum('final_price')
                            +   Package::where('is_valid', '=', 1)
                                        ->whereYear('created_at', $current_year)
                                        ->whereMonth('created_at', $previous_month)
                                        ->get()
                                        ->sum('total_price');
        // get Total revenue by Day
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $previous_date = Carbon::now()->subDays(2)->toDateString();

        $today_revenue = Intake::where('is_valid', '=', 1)
                                ->whereDate('updated_at', '=', $today)
                                ->get()
                                ->sum('final_price')
                    +   Package::where('is_valid', '=', 1)
                                ->whereDate('created_at', '=', $today)
                                ->get()
                                ->sum('total_price');
        $yesterday_revenue = Intake::where('is_valid', '=', 1)
                                ->whereDate('updated_at', '=', $yesterday)
                                ->get()
                                ->sum('final_price')
                    +   Package::where('is_valid', '=', 1)
                                ->whereDate('created_at', '=', $yesterday)
                                ->get()
                                ->sum('total_price');
        $previous_date_revenue = Intake::where('is_valid', '=', 1)
                                ->whereDate('updated_at', '=', $previous_date)
                                ->get()
                                ->sum('final_price')
                    +   Package::where('is_valid', '=', 1)
                                ->whereDate('created_at', '=', $previous_date)
                                ->get()
                                ->sum('total_price');
        $customerSatisfy = ReviewForm::avg('customer_satisfy');
        $facility = ReviewForm::avg('facility');

        return [
            "total_revenue" => $total_revenue,
            "by_year" => [
                "current" => $current_year_revenue,
                "last" => $last_year_revenue,
            ],
            "by_month" => [
                "current" => $current_month_revenue,
                "last" => $last_month_revenue,
                "previous" => $previous_month_revenue,
            ],
            "by_date" => [
                "current" => $today_revenue,
                "last" => $yesterday_revenue,
                "previous" => $previous_date_revenue,
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
            $query->whereBetween('updated_at', [$from, $to])
                ->select(DB::raw("SUM(working_commission)"));
        }]);

        $query->withCount(['package AS sale_commission' => function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to])
                ->select(DB::raw("SUM(sale_commission)"));
        }]);

        $employees = $query->orderBy('id', 'desc')
                ->get()
                ->toArray();
        return $employees;
    }
}
