<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SiteSettings;

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
        $settings = SiteSettings::whereIn('key', [
            'site_title',
            'logo',
            'favicon',
            'meta_site_description',
            'meta_site_title',
        ])->pluck('value', 'key');

        // Share globally to all blade views
        view()->share('settings', $settings);
    }
}
