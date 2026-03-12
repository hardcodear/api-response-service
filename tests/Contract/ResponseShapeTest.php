<?php

namespace Hardcodear\ApiResponseService\Tests\Contract;

use Hardcodear\ApiResponseService\ApiResponseService;
use Hardcodear\ApiResponseService\Tests\TestCase;

class ResponseShapeTest extends TestCase
{
    public function test_success_contract_shape_is_stable(): void
    {
        $service = new ApiResponseService();
        $response = $service->success('OK', ['id' => 123]);

        $json = $response->getData(true);

        $this->assertSame(['status', 'message', 'data'], array_keys($json));
        $this->assertSame(200, $json['status']);
    }

    public function test_error_contract_shape_is_stable(): void
    {
        $service = new ApiResponseService();
        $response = $service->error('Fail', ['detail' => 'x']);

        $json = $response->getData(true);

        $this->assertSame(['status', 'message', 'errors'], array_keys($json));
        $this->assertSame(500, $json['status']);
    }
}
