<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\ProductRepository;
use App\Services\ProductService;
use App\Repositories\ProductVariantRepository;
use App\Services\ProductVariantService;
use App\Services\InventoryService;
use App\Repositories\InventoryRepository;
use App\Repositories\OrderRepository;
use App\Services\OrderService; 


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ProductRepository::class, ProductRepository::class);
        $this->app->bind(ProductVariantRepository::class, ProductVariantRepository::class);
        $this->app->bind(ProductVariantService::class, ProductVariantService::class);
        $this->app->bind(ProductService::class, ProductService::class);
        $this->app->singleton(InventoryRepository::class, function($app){
            return new InventoryRepository();
        });
        $this->app->singleton(InventoryService::class, function($app){
            return new InventoryService($app->make(InventoryRepository::class));
        });

        $this->app->bind(OrderRepository::class,OrderRepository::class);
        $this->app->bind(OrderService::class, function($app){
            return new OrderService(
                $app->make(OrderRepository::class),
                $app->make(InventoryService::class)
            );
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
