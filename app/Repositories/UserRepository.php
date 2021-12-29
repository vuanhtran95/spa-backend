<?php

namespace App\Repositories;

use App\Helper\Translation;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
	public function updatePassword($id, array $attributes = [])
	{
		DB::beginTransaction();
		try {
			$user = User::find($id);
			if ($user) {
				$user->password = Hash::make($attributes['newPassword']);
				$user->save();
			} else {
				throw new \Exception(Translation::$NO_USER_FOUND);
			}
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			throw new \Exception($e->getMessage());
		}
	}
}
