<?php

namespace App\Repositories;

use App\Service;
use App\Variant;
use Illuminate\Support\Facades\DB;

class VariantRepository implements VariantRepositoryInterface
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
            $variant = Variant::find($id);
        } else {
            $variant = new Variant();
        }

        foreach ($data as $key => $value) {
            $variant->$key = $value;
        }

        $variant->save();
        return $variant;

    }

    public function get()
    {
        return Variant::with(['service' => function ($query) {
            $query->with('serviceCategory');
        }])->get()->toArray();
    }


    public function getOneBy($by, $value)
    {
        return Variant::where($by, '=', $value)->with(['service' => function ($query) {
            $query->with('serviceCategory');
        }])->first();
    }

    public function update($id, array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $variant = $this->save($attributes, true, $id);
            DB::commit();
            return $variant;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $count = Variant::destroy($id);
            DB::commit();
            if ($count === 0) {
                throw new \Exception("Variant not found");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
