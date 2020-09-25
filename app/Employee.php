<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 * @package App
 * @mixin Builder
 */
class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'name', 'phone', 'email', 'user_id', 'role_id', 'is_active', 'sale_commission', 'working_commission', 'gender'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo('App\Role', 'role_id', 'id');
    }

    public function combos()
    {
        return $this->hasMany('App\Combo');
    }

    public function order() {
        return $this->hasMany('App\Order');
    }
}
