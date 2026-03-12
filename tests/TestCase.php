<?php

namespace Hardcodear\ApiResponseService\Tests;

use Hardcodear\ApiResponseService\Providers\ApiResponseServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ApiResponseServiceProvider::class];
    }
}
