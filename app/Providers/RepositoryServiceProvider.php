<?php

namespace App\Providers;

use App\Repositories\ComboRepository;
use App\Repositories\ComboRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\IntakeRepository;
use App\Repositories\IntakeRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\ReviewFormRepository;
use App\Repositories\ReviewFormRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\RoleRepositoryInterface;
use App\Repositories\ServiceCategoryRepository;
use App\Repositories\ServiceCategoryRepositoryInterface;
use App\Repositories\ServiceRepository;
use App\Repositories\ServiceRepositoryInterface;
use App\Repositories\EmployeeRepository;
use App\Repositories\EmployeeRepositoryInterface;
use App\Repositories\StatisticRepository;
use App\Repositories\StatisticRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\VariantRepository;
use App\Repositories\VariantRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(ServiceCategoryRepositoryInterface::class, ServiceCategoryRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(IntakeRepositoryInterface::class, IntakeRepository::class);
        $this->app->bind(ComboRepositoryInterface::class, ComboRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ReviewFormRepositoryInterface::class, ReviewFormRepository::class);
        $this->app->bind(VariantRepositoryInterface::class, VariantRepository::class);
        $this->app->bind(StatisticRepositoryInterface::class, StatisticRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}
