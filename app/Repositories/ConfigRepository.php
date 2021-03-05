<?php

namespace App\Repositories;

use App\Helper\Translation;
use Illuminate\Support\Facades\DB;
use App\Config;
use App\Employee;

class ConfigRepository implements ConfigRepositoryInterface
{
    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $entity = $this->save($attributes, false);

            DB::commit();
            return $entity;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function get(array $condition = [])
    {
        $category = isset($condition['category']) ? $condition['category'] : null;
    
        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Config();

        if ($category) {
            $query = $query->where('category', '=', $category);
        }

        $data = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return [
            "Data" => $data,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];
    }

    public function save($data, $is_update = true, $id = null)
    {
        $entity = null;

        if (!$is_update) {
            $entity = new Config();
        } else {
            $entity = Config::find($id);
        }

        foreach ($data as $property => $value) {
            $entity->$property = $value;
        }

        $entity->save();

        return $entity;
    }

    public function remove($id)
    {
        $task_history = Config::find($id);

        if (empty($task_history)) {
            throw new \Exception('Unable to find the task history.');
        }

        return $task_history->delete();
    }
}
