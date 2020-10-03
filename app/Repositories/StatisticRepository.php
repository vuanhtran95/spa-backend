<?php

namespace App\Repositories;

use App\Intake;
use App\ReviewForm;
use Carbon\Carbon;

class StatisticRepository implements StatisticRepositoryInterface
{
    public function get($params)
    {
        // Get total revenue current month
        $totalRevenue = Intake::get()->sum('final_price');
        $currentMonthRevenue = Intake::whereMonth('updated_at', Carbon::now()->month)
            ->sum('final_price');
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
