<?php

namespace Hardcodear\ApiResponseService\Providers;

use Hardcodear\ApiResponseService\ApiResponseService;
use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/apiresponse.php', 'apiresponse');

        $this->app->singleton('apiresponse', function () {
            return new ApiResponseService();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/apiresponse.php' => config_path('apiresponse.php'),
        ], 'apiresponse-config');
    }
}
