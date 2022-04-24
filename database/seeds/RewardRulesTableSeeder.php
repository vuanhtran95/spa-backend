<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use Carbon\Carbon;
use App\Constants\Common;

class RewardRulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $console = new ConsoleOutput();
        $seederClassName = self::class;
        Log::info("Running Seeder: $seederClassName");
        $console->writeln("<fg=yellow>Running Seeder: <fg=white>$seederClassName");

        // Seed data for reward_rules table
        /**
         * If you need to update the timezone, refer to this official docs for list of supported timezone from PHP site
         * https://www.php.net/manual/en/timezones.asia.php
         */
        $currentDate = Carbon::now(Common::SYSTEM_TIMEZONE);
        $currentYear = $currentDate->year;
        $startOfYear = $currentDate->copy()->startOfYear()->toDateTime();
        $endOfYear = $currentDate->copy()->endOfYear()->toDateTime();

        $leftOverPointExpiredDate = Carbon::createFromFormat('Y-m-d', "$currentYear-03-31", Common::SYSTEM_TIMEZONE)
            ->modify('next year')
            ->endOfDay()
            ->toDateTime();

        DB::table('reward_rules')->insert([
            'start_date' => $startOfYear,
            'end_date' => $endOfYear,
            'left_over_point_expired_date' => $leftOverPointExpiredDate,
            'created_at' => now(Common::SYSTEM_TIMEZONE)
        ]);
        $console->writeln('<fg=yellow>Finish!');
        Log::info('Finish!');
    }
}
