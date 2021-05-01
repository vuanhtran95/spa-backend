<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Config extends BaseModel
{
    protected $table = 'configs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'descriptions',
        'value',
        'config_category_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'config_category_id',
    ];

    public function configCategory()
    {
        return $this->belongsTo('App\ConfigCategory', 'config_category_id', 'id');
    }
}
