<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Laravel\Passport\Bridge\UserRepository;


class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function create(Request $request)
    {
        return $request->all();
    }

    public function update($id)
    {
    }

    public function delete($id)
    {
    }
}
