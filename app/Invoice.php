<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

class Invoice extends BaseModel
{
    public function customer()
    {
        return $this->belongsTo('App\Customer', 'customer_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo('App\Employee', 'employee_id', 'id');
    }

    public function intake()
    {
        return $this->belongsTo('App\Intake', 'intake_id', 'id');
    }
}
