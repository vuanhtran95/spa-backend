<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;


class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        if ($this->userRepository->create($params)) {
            return Response::HTTP_CREATED;
        } else {
            return Response::HTTP_BAD_REQUEST;
        }
    }

    public function update($id)
    {
    }

    public function delete($id)
    {
    }
}
