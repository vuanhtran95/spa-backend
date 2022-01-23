<?php

use Illuminate\Database\Seeder;
use App\Intake;
use Illuminate\Support\Facades\DB;

class IntakeApprovedDateSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Intake::where('is_valid', '=', 1)
			->update([
				'approved_date' => DB::raw("`created_at`")
			]);
		var_dump('done !');
	}
}
