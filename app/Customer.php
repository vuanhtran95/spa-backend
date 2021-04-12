<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class Address
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Customer extends Model
{
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','phone', 'email', 'points', 'is_active', 'gender'
    ];

    public function package()
    {
        return $this->hasMany('App\Package');
    }

    public function invoice()
    {
        return $this->hasMany('App\Invoice');
    }
    public function intakes()
    {
        return $this->hasMany('App\Intake');
    }
    public function calculate_spending($id)
    {
        $customer = $this->where('id', $id)
        ->withCount([
            'package AS packages_spend'=> function ($query) {
                $query->where('is_valid', '=', 1)
                    ->select(DB::raw("SUM(total_price)"));
            }])

        ->withCount([
            'invoice AS coin_spend'=> function ($query) {
                $query->where('type', '=', 'topup')->where('status', '=', 'paid')
                    ->select(DB::raw("SUM(amount)"));
            }])

        ->withCount([
            'intakes AS intakes_spend'=> function ($query) {
                $query->where('is_valid', '=', 1)->where('payment_type', '=', 'cash')
                    ->select(DB::raw("SUM(final_price)"));
            },])->first();
        return $customer->packages_spend + $customer->coin_spend + $customer->intakes_spend;
    }
}
