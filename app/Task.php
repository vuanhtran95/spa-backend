<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    public function assignment()
    {
        return $this->hasMany('App\TaskAssignment');
    }

    public function taskHistory()
    {
        return $this->hasMany('App\TaskHistory');
    }
}
