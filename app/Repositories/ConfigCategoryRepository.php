<?php

namespace App\Repositories;

use App\ConfigCategory;

class ConfigCategoryRepository implements ConfigCategoryRepositoryInterface
{
    public function create(array $attributes = [])
    {
        return $this->save($attributes, false);
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $configCategory = ConfigCategory::find($id);
        } else {
            $configCategory = new ConfigCategory();
        }
        $configCategory->name = $data['name'];
        $configCategory->descriptions = $data['descriptions'];


        if ($configCategory->save()) {
            return $configCategory;
        } else {
            return false;
        }
    }

    public function get()
    {
        return ConfigCategory::all();
    }


    public function getOneBy($by, $value)
    {
        return ConfigCategory::where($by, '=', $value)->first();
    }

    public function update($id, array $attributes = [])
    {
        return $this->save($attributes, true, $id);
    }

    public function delete($id)
    {
        return ConfigCategory::destroy($id);
    }
}
