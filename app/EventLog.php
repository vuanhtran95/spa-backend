<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    protected $fillable = [
        'entity_id',
        'event_type',
        'message',
        'target_object_id',
        'target_object_type',
    ];
}
