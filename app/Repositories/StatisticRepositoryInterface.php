<?php

namespace App\Repositories;

interface StatisticRepositoryInterface
{
    public function get();
    public function summary_details(array $query_params = []);
}
