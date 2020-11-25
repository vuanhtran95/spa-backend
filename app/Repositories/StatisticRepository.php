<?php

namespace App\Repositories;

use App\Package;
use App\Intake;
use App\ReviewForm;
use Carbon\Carbon;

class StatisticRepository implements StatisticRepositoryInterface
{
    public function get($params)
    {
        // Get total revenue current month
        $totalRevenue = Intake::where('is_valid', '=', 1)
                                ->get()
                                ->sum('final_price')
                    +   Package::where('is_valid', '=', 1)
                                ->get()
                                ->sum('total_price');
        $currentMonthRevenue = Intake::where('is_valid', '=', 1)
                                        ->whereMonth('updated_at', Carbon::now()->month)
                                        ->get()
                                        ->sum('final_price')
                            +   Package::where('is_valid', '=', 1)
                                        ->whereMonth('created_at', Carbon::now()->month)
                                        ->get()
                                        ->sum('total_price');
        $todayRevenue = Intake::where('is_valid', '=', 1)
                                ->whereDate('updated_at', '=', Carbon::today()->toDateString())
                                ->get()
                                ->sum('final_price')
                    +   Package::where('is_valid', '=', 1)
                                ->whereDate('created_at', '=', Carbon::today()->toDateString())
                                ->get()
                                ->sum('total_price');
        $yesterdayRevenue = Intake::where('is_valid', '=', 1)
                                    ->whereDate('updated_at', '=', Carbon::yesterday()->toDateString())
                                    ->get()
                                    ->sum('final_price')
                        +   Package::where('is_valid', '=', 1)
                                    ->whereDate('created_at', '=', Carbon::yesterday()->toDateString())
                                    ->get()
                                    ->sum('total_price');
        $customerSatisfy = ReviewForm::avg('customer_satisfy');
        $facility = ReviewForm::avg('facility');

        return [
            "total_revenue" => $totalRevenue,
            "current_month_revenue" => $currentMonthRevenue,
            "today_revenue" => $todayRevenue,
            "yesterday_revenue" => $yesterdayRevenue,
            "customer_satisfy" => $customerSatisfy,
            "facility" => $facility
        ];
    }
}
