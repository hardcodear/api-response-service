<?php

namespace Hardcodear\ApiResponseService\Tests\Regression;

use Hardcodear\ApiResponseService\ApiResponseService;
use Hardcodear\ApiResponseService\Tests\TestCase;

class NullOmissionTest extends TestCase
{
    public function test_data_key_is_omitted_when_null(): void
    {
        $service = new ApiResponseService();
        $response = $service->successResponse(200, 'OK', null);

        $json = $response->getData(true);

        $this->assertArrayNotHasKey('data', $json);
    }

    public function test_errors_key_is_omitted_when_null(): void
    {
        $service = new ApiResponseService();
        $response = $service->errorResponse(500, 'Error', null);

        $json = $response->getData(true);

        $this->assertArrayNotHasKey('errors', $json);
    }
}
