<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Review extends BaseModel
{
    protected $table = 'reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'skill', 'attitude', 'order_id', 'review_form_id'
    ];

    public function order() {
        return $this->belongsTo('App\Order');
    }
}

