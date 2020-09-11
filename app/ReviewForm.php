<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewForm extends Model
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
}
