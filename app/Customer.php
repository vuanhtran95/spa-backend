<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class Address
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Customer extends Model
{
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','phone', 'email', 'points', 'is_active', 'gender'
    ];

    public function combos() {
        return $this->hasMany('App\Combo');
    }

    public function invoice() 
    {
        return $this->hasMany('App\Invoice');
    }
}
