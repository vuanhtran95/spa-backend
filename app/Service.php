<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Service extends BaseModel
{
    protected $table = 'services';
    public static $ORDER = 'order';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'descriptions',
        'combo_ratio',
        'price',
        'is_combo_sold',
        'combo_commission',
        'service_category_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'service_category_id',
    ];

    public function serviceCategory()
    {
        return $this->belongsTo('App\ServiceCategory', 'service_category_id', 'id');
    }
}
