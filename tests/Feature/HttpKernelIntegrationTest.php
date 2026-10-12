<?php

namespace Hardcodear\ApiResponseService\Tests\Feature;

use Hardcodear\ApiResponseService\ExceptionApiRegistrar;
use Hardcodear\ApiResponseService\Tests\TestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class HttpKernelIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ExceptionApiRegistrar::bind(new Exceptions($this->app->make(ExceptionHandler::class)));
    }

    public function test_helper_response_is_sent_through_the_http_kernel(): void
    {
        $this->app['router']->get('/api/users', fn () => apiresponse()->success('Usuarios', [['id' => 1]]));

        $this->getJson('/api/users')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['status' => 200, 'message' => 'Usuarios', 'data' => [['id' => 1]]]);
    }

    public function test_router_not_found_uses_the_configured_message(): void
    {
        config()->set('apiresponse.messages.not_found', 'No disponible');

        $this->getJson('/api/missing')
            ->assertNotFound()
            ->assertExactJson(['status' => 404, 'message' => 'No disponible']);
    }

    public function test_router_method_not_allowed_preserves_allow_header(): void
    {
        $this->app['router']->get('/api/users', fn () => apiresponse()->success());

        $this->postJson('/api/users')
            ->assertStatus(405)
            ->assertHeader('Allow', 'GET, HEAD')
            ->assertExactJson(['status' => 405, 'message' => config('apiresponse.messages.method_not_allowed')]);
    }

    public function test_request_validation_uses_the_package_contract(): void
    {
        $this->app['router']->post('/api/users', function (Request $request) {
            $request->validate(['email' => ['required', 'email']]);
        });

        $response = $this->postJson('/api/users', []);

        $response->assertUnprocessable()
            ->assertJsonPath('status', 422)
            ->assertJsonPath('message', config('apiresponse.messages.validation'))
            ->assertJsonStructure(['errors' => [['email']]])
            ->assertJsonMissingPath('data');
    }

    public function test_exception_headers_survive_the_real_handler(): void
    {
        $this->app['router']->get('/api/limited', function () {
            throw new TooManyRequestsHttpException(60);
        });

        $this->getJson('/api/limited')
            ->assertStatus(429)
            ->assertHeader('Retry-After', '60')
            ->assertExactJson(['status' => 429, 'message' => config('apiresponse.messages.too_many_requests')]);
    }

    public function test_non_api_requests_retain_laravel_error_rendering(): void
    {
        $response = $this->getJson('/web/missing');

        $response->assertNotFound()->assertJsonMissingPath('status');
    }
}
