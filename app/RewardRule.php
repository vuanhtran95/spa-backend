<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RewardRule extends Model
{
    protected $fillable = [
        'start_date',
        'end_date',
        'left_over_point_expired_date',
        'status'
    ];

    public function customers() {
        return $this->hasMany(Customer::class);
    }
}
