<?php

namespace Hardcodear\ApiResponseService\Tests\Unit;

use Hardcodear\ApiResponseService\Facades\ApiResponse;
use Hardcodear\ApiResponseService\Tests\TestCase;
use Illuminate\Http\JsonResponse;

class FacadeTest extends TestCase
{
    public function test_facade_resolves_accessor_and_returns_json_response(): void
    {
        $response = ApiResponse::success('OK', ['id' => 10]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $json = $response->getData(true);
        $this->assertSame(200, $json['status']);
        $this->assertSame('OK', $json['message']);
        $this->assertSame(['id' => 10], $json['data']);
    }
}
