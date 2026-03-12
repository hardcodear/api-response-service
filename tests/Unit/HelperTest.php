<?php

namespace Hardcodear\ApiResponseService\Tests\Unit;

use Hardcodear\ApiResponseService\ApiResponseService;
use Hardcodear\ApiResponseService\Tests\TestCase;

class HelperTest extends TestCase
{
    public function test_apiresponse_helper_exists_and_resolves_service(): void
    {
        $this->assertTrue(function_exists('apiresponse'));

        $resolved = apiresponse();

        $this->assertInstanceOf(ApiResponseService::class, $resolved);
        $this->assertSame($this->app->make('apiresponse'), $resolved);
    }
}
