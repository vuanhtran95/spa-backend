<?php

namespace App\Repositories;

use App\Service;
use Illuminate\Database\QueryException;
use Mockery\Exception;

class ServiceRepository implements ServiceRepositoryInterface
{

    public function create(array $attributes = [])
    {
        return $this->save($attributes, false);
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $service = Service::find($id);
        } else {
            $service = new Service();
        }
        $service->name = $data['name'];
        $service->descriptions = $data['descriptions'];
        $service->price = $data['price'];


        if ($service->save()) {
            return $service;
        } else {
            return false;
        }
    }

    public function get()
    {
        return Service::with('service_category')->get()->toArray();
    }


    public function getOneBy($by, $value)
    {
        return Service::where($by, '=', $value)->first();
    }

    public function update($id, array $attributes = [])
    {
        return $this->save($attributes, true, $id);
    }

    public function delete($id)
    {
        return Service::destroy($id);
    }
}
