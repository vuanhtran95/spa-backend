<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

/**
 * Class Combo
 * @package App
 * @mixin Builder
 */
class Variable extends BaseModel
{
    public $incrementing = false;
    protected $table = 'variables';

}
