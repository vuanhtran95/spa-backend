<?php

namespace App\Repositories;

interface TaskAssignmentRepositoryInterface
{
    public function get();

    public function getReminder();

    public function createReminder(array $attributes = []);

    public function create(array $attributes = []);

    public function save($data, $task_assignment_id);

    public function delete($task_assignment_id);
}
