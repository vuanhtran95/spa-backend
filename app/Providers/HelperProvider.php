<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helper\RewardRuleHelper;

class HelperProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RewardRuleHelper::class, function($app) {
            return new RewardRuleHelper();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
