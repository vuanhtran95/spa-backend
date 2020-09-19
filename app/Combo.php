<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Combo
 * @package App
 * @mixin Builder
 */
class Combo extends Model
{
    protected $table = 'combos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expiry_date',
        'variant_id',
        'customer_id',
        'amount',
        'user_id',
        'is_valid',
        'price',
    ];

    public function variant() {
        return $this->belongsTo('App\Variant');
    }

    public function customer() {
        return $this->belongsTo('App\Customer');
    }

    public function orders() {
        return $this->hasMany('App\Order');
    }
}
