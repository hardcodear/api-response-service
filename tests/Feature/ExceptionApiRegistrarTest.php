<?php

namespace Hardcodear\ApiResponseService\Tests\Feature;

use Hardcodear\ApiResponseService\ApiResponseService;
use Hardcodear\ApiResponseService\ExceptionApiRegistrar;
use Hardcodear\ApiResponseService\Tests\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class ExceptionApiRegistrarTest extends TestCase
{
    public function test_bind_registers_renderer_and_maps_known_api_exceptions(): void
    {
        $renderer = null;

        $exceptions = $this->getMockBuilder(Exceptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $exceptions->expects($this->once())
            ->method('render')
            ->willReturnCallback(function (callable $callback) use (&$renderer) {
                $renderer = $callback;

                return null;
            });

        ExceptionApiRegistrar::bind($exceptions);

        $cases = [
            [new AccessDeniedHttpException('denied'), ApiResponseService::HTTP_UNAUTHORIZED],
            [new NotFoundHttpException('not found'), ApiResponseService::HTTP_NOT_FOUND],
            [new TooManyRequestsHttpException(null, 'too many'), ApiResponseService::HTTP_TOO_MANY_REQUESTS],
            [new RouteNotFoundException('route missing'), ApiResponseService::HTTP_UNAUTHORIZED],
            [new AuthenticationException('auth'), ApiResponseService::HTTP_UNAUTHORIZED],
            [new MethodNotAllowedHttpException([], 'method'), ApiResponseService::HTTP_METHOD_NOT_ALLOWED],
        ];

        foreach ($cases as [$exception, $expectedStatus]) {
            $request = Request::create('/api/users', 'GET');
            $response = $renderer($exception, $request);

            $this->assertInstanceOf(JsonResponse::class, $response);
            $this->assertSame($expectedStatus, $response->getStatusCode());
            $this->assertSame($expectedStatus, $response->getData(true)['status']);
        }

        $rootApiRequest = Request::create('/api', 'GET');
        $rootApiResponse = $renderer(new NotFoundHttpException('not found'), $rootApiRequest);

        $this->assertInstanceOf(JsonResponse::class, $rootApiResponse);
        $this->assertSame(ApiResponseService::HTTP_NOT_FOUND, $rootApiResponse->getStatusCode());
    }

    public function test_bind_does_not_intercept_non_api_routes(): void
    {
        $renderer = null;

        $exceptions = $this->getMockBuilder(Exceptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $exceptions->expects($this->once())
            ->method('render')
            ->willReturnCallback(function (callable $callback) use (&$renderer) {
                $renderer = $callback;

                return null;
            });

        ExceptionApiRegistrar::bind($exceptions);

        $request = Request::create('/web/home', 'GET');
        $response = $renderer(new class('x') extends \Exception implements Throwable {}, $request);

        $this->assertNull($response);
    }

    public function test_bind_maps_validation_exception_to_422_with_errors(): void
    {
        $renderer = null;

        $exceptions = $this->getMockBuilder(Exceptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $exceptions->expects($this->once())
            ->method('render')
            ->willReturnCallback(function (callable $callback) use (&$renderer) {
                $renderer = $callback;

                return null;
            });

        ExceptionApiRegistrar::bind($exceptions);

        $validator = $this->app['validator']->make([], ['email' => ['required']]);
        $exception = new ValidationException($validator);
        $request = Request::create('/api/users', 'POST');

        $response = $renderer($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(ApiResponseService::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $json = $response->getData(true);
        $this->assertSame(ApiResponseService::HTTP_UNPROCESSABLE_ENTITY, $json['status']);
        $this->assertArrayHasKey('errors', $json);
    }

    public function test_bind_maps_authorization_exception_to_403(): void
    {
        $renderer = null;

        $exceptions = $this->getMockBuilder(Exceptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $exceptions->expects($this->once())
            ->method('render')
            ->willReturnCallback(function (callable $callback) use (&$renderer) {
                $renderer = $callback;

                return null;
            });

        ExceptionApiRegistrar::bind($exceptions);

        $request = Request::create('/api/users', 'GET');
        $response = $renderer(new AuthorizationException('forbidden'), $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(ApiResponseService::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function test_bind_maps_service_unavailable_exception_to_503(): void
    {
        $renderer = null;

        $exceptions = $this->getMockBuilder(Exceptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $exceptions->expects($this->once())
            ->method('render')
            ->willReturnCallback(function (callable $callback) use (&$renderer) {
                $renderer = $callback;

                return null;
            });

        ExceptionApiRegistrar::bind($exceptions);

        $request = Request::create('/api/status', 'GET');
        $response = $renderer(new ServiceUnavailableHttpException(null, 'maintenance'), $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(ApiResponseService::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertSame(ApiResponseService::HTTP_SERVICE_UNAVAILABLE, $response->getData(true)['status']);
    }

    public function test_bind_maps_generic_http_exception_status_for_api_routes(): void
    {
        $renderer = null;

        $exceptions = $this->getMockBuilder(Exceptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $exceptions->expects($this->once())
            ->method('render')
            ->willReturnCallback(function (callable $callback) use (&$renderer) {
                $renderer = $callback;

                return null;
            });

        ExceptionApiRegistrar::bind($exceptions);

        $request = Request::create('/api/teapot', 'GET');
        $response = $renderer(new HttpException(418, 'Teapot'), $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(418, $response->getStatusCode());
        $this->assertSame(418, $response->getData(true)['status']);
        $this->assertSame('Teapot', $response->getData(true)['message']);
    }
}
