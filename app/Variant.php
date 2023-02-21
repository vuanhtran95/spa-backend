<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Variant extends BaseModel
{
    protected $table = 'variants';
    protected $casts = [
		'metadata' => 'array',
	];

    public function service()
    {
        return $this->belongsTo('App\Service');
    }
}
