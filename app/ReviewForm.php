<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class ReviewForm extends BaseModel
{
    protected $table = 'review_forms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'facility', 'note', 'intake_id'
    ];

    public function review()
    {
        return $this->hasMany('App\Review');
    }
}
