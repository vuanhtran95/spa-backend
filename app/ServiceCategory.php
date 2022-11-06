<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class ServiceCategory extends BaseModel
{
	protected $table = 'service_categories';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'description'
	];

	public function service()
	{
		return $this->hasMany('App\Service');
	}
	public static $ORDER = 'id';

	protected static function boot()
	{
		parent::boot();

		// Order by name ASC
		static::addGlobalScope('order', function (Builder $builder) {
			$builder->orderBy(self::$ORDER, 'asc');
		});
	}
}
