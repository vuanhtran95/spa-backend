<?php

namespace App\Repositories;

use App\Combo;
use App\Intake;
use App\ReviewForm;
use Carbon\Carbon;

class StatisticRepository implements StatisticRepositoryInterface
{
    public function get($params)
    {
        // Get total revenue current month
        $totalRevenue = Intake::where('is_valid', '=', 1)->get()->sum('final_price')
            + Combo::where('is_valid', '=', 1)->get()->sum('total_price');
        $currentMonthRevenue = Intake::whereMonth('created_at', Carbon::now()->month)
            ->sum('final_price') + Combo::whereMonth('created_at', Carbon::now()->month)
                ->sum('total_price');
        $customerSatisfy = ReviewForm::avg('customer_satisfy');
        $facility = ReviewForm::avg('facility');

        return [
            "total_revenue" => $totalRevenue,
            "current_month_revenue" => $currentMonthRevenue,
            "customer_satisfy" => $customerSatisfy,
            "facility" => $facility
        ];
    }
}
