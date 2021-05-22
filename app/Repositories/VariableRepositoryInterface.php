<?php

namespace App\Repositories;

interface VariableRepositoryInterface
{

    public function get();

    public function update(array $attributes = []);


}
