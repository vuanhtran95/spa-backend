<?php

namespace App\Repositories;

interface TaskAssignmentRepositoryInterface
{
    public function get();

    public function create(array $attributes = []);

    public function save($data, $task_assignment_id);

    public function delete($task_assignment_id, $emp_id);
}
