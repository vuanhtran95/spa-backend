<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

/**
 * Class Combo
 * @package App
 * @mixin Builder
 */
class Discount extends BaseModel
{
    protected $table = 'discounts';

    public function rank()
    {
        return $this->belongsTo('App\Rank');
    }

    public function serviceCategory()
    {
        return $this->belongsTo('App\ServiceCategory');
    }
    
    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function variant()
    {
        return $this->belongsTo('App\Variant');
    }
}
