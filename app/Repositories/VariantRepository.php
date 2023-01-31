<?php

namespace App\Repositories;

use App\Service;
use App\Variant;
use App\ServiceCategory;
use Illuminate\Support\Carbon;
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
            foreach ($data as $key => $value) {
                $variant->$key = $value;
            }
    
            $variant->save();
            return $variant;
        } else {
            if (!empty($data['variants'])) {
                $variants = $data['variants'];
                foreach ($variants as $key => $variant) {
                    $variants[$key]['service_id'] = $data['service_id'];
                    $variants[$key]['created_at'] = Carbon::now();
                    $variants[$key]['updated_at'] = Carbon::now();
                    $variants[$key]['price'] = isset($variants[$key]['price']) ? $variants[$key]['price'] : 0;
                    $variants[$key]['sale_price'] = isset($variants[$key]['sale_price']) ? $variants[$key]['sale_price'] : 0;
                    $variants[$key]['gender'] = isset($variants[$key]['gender']) ? $variants[$key]['gender'] : 'both';
                    $variants[$key]['description'] = isset($variants[$key]['description']) ? $variants[$key]['description'] : null;
                    $variants[$key]['name'] = isset($variants[$key]['name']) ? $variants[$key]['name'] : null;
                    $variants[$key]['is_free'] = isset($variants[$key]['is_free']) ? $variants[$key]['is_free'] : 0;
                    $variants[$key]['commission_rate'] = isset($variants[$key]['commission_rate']) ? $variants[$key]['commission_rate'] : 0;
                    $variants[$key]['is_active'] = 1;
                    $variants[$key]['variant_category'] = isset($variants[$key]['variant_category']) ? $variants[$key]['variant_category'] : 'other';
                }
                $variants_inserted = Variant::insert($variants);
                return true;
            } else {
                throw new \Exception('Empty Array');
            }
        }
    }

    public function get(array $condition = [])
    {
        $isActive = isset($condition['is_active']) ? $condition['is_active'] : null;
        $service_id = isset($condition['service_id']) ? $condition['service_id'] : null;
        $query = new Variant();
        if($service_id) {
            $query = $query->whereHas('service', function ($query) use ($service_id) {
                $query->where('id', $service_id);
            });
        }
        if ($isActive !== null) {
            $query = $query->where('is_active', '=', $isActive);
            $query = $query->whereHas('service', function ($query) use ($isActive) {
                $query->where('is_active', $isActive);
            });
        }

        return $query->with(['service' => function ($sQuery) {
            $sQuery->with('serviceCategory');
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
