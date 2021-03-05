<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Judgement extends Model
{
    protected $table = 'judgement';

    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }
}
