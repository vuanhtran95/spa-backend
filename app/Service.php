<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'descriptions'
    ];

    public function serviceCategory() {
        return $this->belongsTo('App\ServiceCategory', 'service_category_id', 'id');
    }
}
