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
class Rank extends BaseModel
{
    protected $table = 'ranks';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function discounts()
    {
        return $this->hasMany('App\Discount');
    }
}
