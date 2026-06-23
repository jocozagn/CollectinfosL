<?php

namespace App\Providers;

use App\Models\Partner;
use App\Models\SiteProduct;
use App\Models\SiteSetting;
use App\Models\SiteStat;
use App\Models\Taxonomy;
use App\Services\CartService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('layouts.app', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
            $view->with('siteContact', SiteSetting::contact());

            try {
                if (Schema::hasTable('taxonomies')) {
                    $view->with('navCategories', Taxonomy::homeCategories());
                } else {
                    $view->with('navCategories', config('collectinfos.categories', []));
                }
            } catch (\Throwable) {
                $view->with('navCategories', config('collectinfos.categories', []));
            }
        });
    }
}
