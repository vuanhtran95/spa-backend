<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Variant extends BaseModel
{
    protected $table = 'variants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price', 'service_id'
    ];

    public function service()
    {
        return $this->belongsTo('App\Service');
    }
}
