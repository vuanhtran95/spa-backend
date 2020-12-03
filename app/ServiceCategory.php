<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class ServiceCategory extends BaseModel
{
    protected $table = 'service_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description'
    ];

    public function service()
    {
        return $this->hasMany('App\Service');
    }
}
