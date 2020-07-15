<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 * @package App
 * @mixin Builder
 */
class Intake extends Model
{
    protected $table = 'intakes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id', 'user_id', 'is_valid'
    ];

    public function orders() {
        return $this->hasMany('App\Order');
    }
}
