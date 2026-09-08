<?php

namespace Hardcodear\ApiResponseService\Tests\Unit;

use Hardcodear\ApiResponseService\ApiResponseService;
use Hardcodear\ApiResponseService\Providers\ApiResponseServiceProvider;
use Hardcodear\ApiResponseService\Tests\TestCase;
use Illuminate\Support\ServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_service_is_registered_in_container_as_singleton(): void
    {
        $first = $this->app->make('apiresponse');
        $second = $this->app->make('apiresponse');

        $this->assertInstanceOf(ApiResponseService::class, $first);
        $this->assertSame($first, $second);
    }

    public function test_default_configuration_is_merged(): void
    {
        $this->assertSame(['api', 'api/*'], config('apiresponse.api_patterns'));
        $this->assertSame('Error interno del servidor', config('apiresponse.messages.server_error'));
    }

    public function test_configuration_can_be_published(): void
    {
        $paths = ServiceProvider::pathsToPublish(ApiResponseServiceProvider::class, 'apiresponse-config');

        $this->assertNotEmpty($paths);
        $this->assertContains(config_path('apiresponse.php'), array_values($paths));
    }
}
