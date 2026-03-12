<?php

namespace Hardcodear\ApiResponseService\Tests\Unit;

use Hardcodear\ApiResponseService\ApiResponseService;
use Hardcodear\ApiResponseService\Tests\TestCase;
use Illuminate\Http\JsonResponse;

class ApiResponseServiceTest extends TestCase
{
    private ApiResponseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ApiResponseService();
    }

    public function test_success_response_includes_data_when_present(): void
    {
        $response = $this->service->successResponse(201, 'Creado', ['id' => 1]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(201, $response->getStatusCode());

        $json = $response->getData(true);
        $this->assertSame(201, $json['status']);
        $this->assertSame('Creado', $json['message']);
        $this->assertSame(['id' => 1], $json['data']);
        $this->assertArrayNotHasKey('errors', $json);
    }

    public function test_success_response_omits_data_when_null(): void
    {
        $response = $this->service->successResponse();

        $json = $response->getData(true);
        $this->assertSame(ApiResponseService::HTTP_OK, $response->getStatusCode());
        $this->assertArrayNotHasKey('data', $json);
        $this->assertArrayNotHasKey('errors', $json);
    }

    public function test_error_response_wraps_object_errors_in_array(): void
    {
        $errors = (object) ['field' => 'name'];
        $response = $this->service->errorResponse(500, 'Error', $errors);

        $json = $response->getData(true);
        $this->assertCount(1, $json['errors']);
        $this->assertSame(['field' => 'name'], (array) $json['errors'][0]);
    }

    public function test_error_response_wraps_associative_array_errors_in_array(): void
    {
        $response = $this->service->errorResponse(422, 'Invalid', ['name' => ['required']]);

        $json = $response->getData(true);
        $this->assertSame([['name' => ['required']]], $json['errors']);
    }

    public function test_error_response_keeps_indexed_array_errors_as_is(): void
    {
        $response = $this->service->errorResponse(422, 'Invalid', ['required', 'email']);

        $json = $response->getData(true);
        $this->assertSame(['required', 'email'], $json['errors']);
    }

    public function test_error_response_omits_errors_when_null(): void
    {
        $response = $this->service->errorResponse();

        $json = $response->getData(true);
        $this->assertSame(ApiResponseService::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertArrayNotHasKey('errors', $json);
        $this->assertArrayNotHasKey('data', $json);
    }

    public function test_success_method_uses_200_status(): void
    {
        $response = $this->service->success('OK', ['ok' => true]);

        $this->assertSame(ApiResponseService::HTTP_OK, $response->getStatusCode());
        $this->assertSame(ApiResponseService::HTTP_OK, $response->getData(true)['status']);
    }

    public function test_error_method_uses_500_status(): void
    {
        $response = $this->service->error('Error');

        $this->assertSame(ApiResponseService::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function test_not_found_method_uses_404_status(): void
    {
        $response = $this->service->notFound('No encontrado');

        $this->assertSame(ApiResponseService::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_validation_method_uses_422_status(): void
    {
        $response = $this->service->validation('Validacion');

        $this->assertSame(ApiResponseService::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function test_forbidden_method_uses_403_status(): void
    {
        $response = $this->service->forbidden('Forbidden');

        $this->assertSame(ApiResponseService::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function test_unauthorized_method_uses_401_status(): void
    {
        $response = $this->service->unauthorized('Unauthorized');

        $this->assertSame(ApiResponseService::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function test_server_error_method_uses_500_status(): void
    {
        $response = $this->service->serverError('Server error');

        $this->assertSame(ApiResponseService::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }
}
