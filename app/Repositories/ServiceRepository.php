<?php

namespace App\Repositories;

use App\Service;
use App\Variant;
use App\ServiceCategory;
use App\Repositories\VariantRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceRepository implements ServiceRepositoryInterface
{
    private $variantRepository;

    public function __construct(VariantRepository $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }
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
            if ($key !== 'variants') {
                $service->$key = $value;
            }
        }

        if ($service->save()) {
            $this->variantRepository->create(['variants' => $data['variants'], 'service_id' => $service->id], false);
            $query= new Variant();
            $created_variants = $query->where('service_id', $service->id)->get()->toArray();
            $service->variants = $created_variants;

            // foreach ($variants as $key => $variant) {
            //     $variants[$key]['service_id'] = $service->id;
            //     $variants[$key]['created_at'] = Carbon::now();
            //     $variants[$key]['updated_at'] = Carbon::now();
            //     $variants[$key]['price'] = isset($variants[$key]['price']) ? $variants[$key]['price'] : 0;
            //     $variants[$key]['sale_price'] = isset($variants[$key]['sale_price']) ? $variants[$key]['sale_price'] : 0;
            //     $variants[$key]['gender'] = isset($variants[$key]['gender']) ? $variants[$key]['gender'] : 'both';
            //     $variants[$key]['description'] = isset($variants[$key]['description']) ? $variants[$key]['description'] : null;
            //     $variants[$key]['name'] = isset($variants[$key]['name']) ? $variants[$key]['name'] : null;
            //     $variants[$key]['is_free'] = isset($variants[$key]['is_free']) ? $variants[$key]['is_free'] : 0;
            //     $variants[$key]['commission_rate'] = isset($variants[$key]['commission_rate']) ? $variants[$key]['commission_rate'] : 0;
            //     $variants[$key]['is_active'] = 1;
            //     $category = ServiceCategory::find($service->service_category_id);
            //     $variant_category = 'other';
            //     if ($category) {
            //         $variant_category = $category->name;
            //     }
            //     $variants[$key]['variant_category'] = isset($variants[$key]['variant_category']) ? $variants[$key]['variant_category'] : $variant_category;
            //     // New added properties 2023
            //     $variants[$key]['sale_price'] = isset($variants[$key]['sale_price']) ? $variants[$key]['sale_price'] : 0;
            //     $variants[$key]['stock'] =  isset($variants[$key]['stock']) ? $variants[$key]['stock'] : 0;
            //     $variants[$key]['product_line'] = isset($variants[$key]['product_line']) ? $variants[$key]['product_line'] : '';
            //     $variants[$key]['metadata'] = isset($variants[$key]['metadata']) ? $variants[$key]['metadata'] : null;
            // }
            // $variants_inserted = Variant::insert($variants);
            // Return Intake with order
        } else {
            return false;
        }
        return $service;
    }

    public function get(array $condition = [])
    {
        $category_name = isset($condition['category_name']) ? $condition['category_name'] : null;
        return Service::whereHas('serviceCategory', function ($sQuery) use ($category_name) {
            $sQuery->where('name', 'LIKE', '%' . $category_name . '%');

        })->get()->toArray();
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
