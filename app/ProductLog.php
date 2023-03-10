<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class ProductLog extends BaseModel
{
    protected $table = 'product_logs';

    public function created_by()
    {
        return $this->belongsTo('App\Employee', 'created_by', 'id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer', 'customer_id', 'id');
    }

    public function intake()
    {
        return $this->belongsTo('App\Intake', 'intake_id', 'id');
    }

    public function variant()
    {
        return $this->belongsTo('App\Variant', 'variant_id', 'id');
    }

    public function scopeType($query, $request_query) {
        if (isset($request_query['type'])) {
            $query->whereIn('type', $request_query['type']);
        }
    
        return $query;
    }
}
