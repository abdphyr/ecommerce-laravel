<?php

namespace App\Providers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        CategoryResource::withoutWrapping();
        UserResource::withoutWrapping();
        RoleResource::withoutWrapping();
        ProductResource::withoutWrapping();
        ImageResource::withoutWrapping();
    }
}
