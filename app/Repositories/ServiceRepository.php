<?php

namespace App\Repositories;

use App\Service;
use Illuminate\Support\Facades\DB;

class ServiceRepository implements ServiceRepositoryInterface
{

    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $service = $this->save($attributes, false);
            DB::commit();
            return $service;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $service = Service::find($id);
        } else {
            $service = new Service();
        }

        foreach ($data as $key => $value) {
            $service->$key = $value;
        }

        $service->save();
        return $service;

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
        DB::beginTransaction();
        try {
            $service = $this->save($attributes, true, $id);
            DB::commit();
            return $service;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $count = Service::destroy($id);
            DB::commit();
            if ($count === 0) {
                throw new \Exception("Service not found");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
