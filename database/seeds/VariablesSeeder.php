<?php

use Illuminate\Database\Seeder;
use App\Variable;
use Illuminate\Support\Facades\DB;

class VariablesSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$variables = array(
			[
				'id' => 'DIAMOND_EXTRA_DISCOUNT',
				'description' => 'Extra discount(%) cho Diamond member',
				'value' => 4
			],
			[
				'id' => 'GOLD_EXTRA_DISCOUNT',
				'description' => 'Extra discount(%) cho Gold member',
				'value' => 3
			],
			[
				'id' => 'SILVER_EXTRA_DISCOUNT',
				'description' => 'Extra discount(%) cho Silver member',
				'value' => 2
			],
			[
				'id' => 'POINT_RATE',
				'description' => '(%) tích điểm cho khách non-member',
				'value' => 2
			],
			[
				'id' => 'POINT_RATE_DIAMOND',
				'description' => '(%) tích điểm cho khách Diamond',
				'value' => 7
			],
			[
				'id' => 'POINT_RATE_GOLD',
				'description' => '(%) tích điểm cho khách Gold',
				'value' => 5
			],
			[
				'id' => 'POINT_RATE_SILVER',
				'description' => '(%) tích điểm cho khách Silver',
				'value' => 3
			],
			[
				'id' => 'RANK_EXTRA_DISCOUNT_ACTIVE',
				'description' => 'Kích hoạt extra discount cho member',
				'value' => 1
			],
			[
				'id' => 'OVERTIME_COMMISSION',
				'description' => 'Giờ bắt đầu tính commission cho nhân viên ( theo giờ VN )',
				'value' => '20:00:00'
			],
			[
				'id' => 'OVERTIME_COMMISSION_RATE',
				'description' => 'Tỉ lệ tính overtime commission cho KTV',
				'value' => 1.5
			],
		);
		DB::beginTransaction();
		try {
			foreach ($variables as $variable) {
				$record = Variable::find($variable['id']);
				if (empty($record)) {
					Variable::create($variable);
				}
			}
			DB::commit();
		} catch (\Exception $exception) {
			DB::rollBack();
			throw new \Exception($exception->getMessage());
		}
		var_dump('done !');
	}
}
