<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    protected $table = 'combos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'expiry_date',
        'service_id',
        'customer_id',
        'amount',
        'user_id',
        'is_active'
    ];
}
