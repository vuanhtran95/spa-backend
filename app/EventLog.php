<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    protected $fillable = [
        'entity_id',
        'event_type',
        'message'
    ];
}
