<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\NewsInterface;
use App\Services\NewsAPIService;
use App\Services\NYTService;
use App\Services\GuardianService;

class NewsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('news.fetchers', function ($app) {
            return [
                'newsapi' => new NewsAPIService(),
                'nyt' => new NYTService(),
                'guardian' => new GuardianService(),
            ];
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
