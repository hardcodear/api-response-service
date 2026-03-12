<?php

namespace Hardcodear\ApiResponseService\Tests\Unit;

use Hardcodear\ApiResponseService\ApiResponseService;
use Hardcodear\ApiResponseService\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_service_is_registered_in_container_as_singleton(): void
    {
        $first = $this->app->make('apiresponse');
        $second = $this->app->make('apiresponse');

        $this->assertInstanceOf(ApiResponseService::class, $first);
        $this->assertSame($first, $second);
    }
}
