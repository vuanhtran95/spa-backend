<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

/**
 * Class Address
 * @package App
 * @mixin Builder
 */
class Intake extends BaseModel
{
    protected $table = 'intakes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id', 'user_id', 'is_valid', 'price'
    ];

    protected $hidden = [
        'user_id',
    ];

    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    public function employee()
    {
        return $this->belongsTo('App\Employee', 'employee_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer', 'customer_id', 'id');
    }

    public function reviewForm()
    {
        return $this->hasOne('App\ReviewForm');
    }

    public function invoice()
    {
        return $this->hasOne('App\Invoice');
    }
}
