<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

/**
 * Class Package
 * @package App
 * @mixin Builder
 */
class Package extends BaseModel
{
    protected $table = 'packages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expiry_date',
        'customer_id',
        'employee_id',
        'is_gift',
        'promotion_type',
        'is_valid',
        'total_price',
        'sale_commission',
    ];

    public function employee()
    {
        return $this->belongsTo('App\Employee', 'employee_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer', 'customer_id', 'id');
    }

    public function combos()
    {
        return $this->hasMany('App\Combo');
    }
}
