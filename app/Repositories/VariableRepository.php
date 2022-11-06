<?php

namespace App\Repositories;

use App\Helper\Translation;
use App\Variable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class VariableRepository implements VariableRepositoryInterface
{
    public function get()
    {
        $perPage =  1000;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Variable();

        $variables = $query->get()->toArray();

        return [
            "Data" => $variables,
            "Pagination" => []
        ];
    }

    public function update( array $data = [])
    {
        DB::beginTransaction();
        try {
            foreach ($data as $variable) {
                if(empty($variable['id'])) break;
                $update_variable = Variable::find($variable['id']);
                foreach ($variable as $property => $value) {
                    if($property !== 'id') {
                        $update_variable->$property = $value;
                    }
                }
                $update_variable->save();
            }
            return true;
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }
}
