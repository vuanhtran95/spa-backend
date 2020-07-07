<?php

namespace App\Repositories;

use App\ServiceCategory;
use App\User;
use Illuminate\Database\QueryException;
use Mockery\Exception;

class ServiceCategoryRepository implements ServiceCategoryInterface
{

    public function create(array $attributes = [])
    {
        return $this->save($attributes, false);
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $serviceCategory = ServiceCategory::find($id);
        } else {
            $serviceCategory = new ServiceCategory();
        }
        $serviceCategory->name = $data['name'];
        $serviceCategory->descriptions = $data['descriptions'];


        if ($serviceCategory->save()) {
            return $serviceCategory;
        } else {
            return false;
        }
    }

    public function get()
    {
        return ServiceCategory::all();
    }


    public function getOneBy($by, $value)
    {
        return ServiceCategory::where($by, '=', $value)->first();
    }

    public function update($id, array $attributes = [])
    {
        return $this->save($attributes, true, $id);
    }

    public function delete($id)
    {
        return ServiceCategory::destroy($id);
    }
}
