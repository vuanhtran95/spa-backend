<?php

namespace App\Repositories;

use App\Service;

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
        $service->is_combo_sold = $data['is_combo_sold'];
        $service->order_commission = $data['order_commission'];
        $service->combo_commission = $data['combo_commission'];
        $service->combo_ratio = $data['combo_ratio'];
        $service->service_category_id = $data['service_category_id'];


        if ($service->save()) {
            return $service;
        } else {
            return false;
        }
    }

    public function get()
    {
        return Service::with('serviceCategory')->get()->toArray();
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
