<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskHistory extends Model
{
    protected $table = 'task_history';

    public function task()
    {
        return $this->belongsTo('App\Task');
    }

    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }
}
