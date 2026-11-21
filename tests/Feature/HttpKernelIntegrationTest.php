<?php

namespace Hardcodear\ApiResponseService\Tests\Feature;

use Hardcodear\ApiResponseService\ExceptionApiRegistrar;
use Hardcodear\ApiResponseService\Tests\TestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public function test_not_found_exception_preserves_cache_control_header(): void
    {
        $this->app['router']->get('/api/removed', function () {
            throw new NotFoundHttpException(headers: ['Cache-Control' => 'no-store']);
        });

        $response = $this->getJson('/api/removed')
            ->assertNotFound()
            ->assertExactJson(['status' => 404, 'message' => config('apiresponse.messages.not_found')]);

        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
    }

    public function test_head_request_has_no_error_body(): void
    {
        $this->head('/api/missing')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertContent('');
    }

    public function test_gate_denial_uses_the_configured_authorization_message(): void
    {
        config()->set('apiresponse.messages.authorization', 'Operación no autorizada');
        Gate::define('view-private', fn (?object $user) => false);

        $this->app['router']->get('/api/private', function () {
            Gate::authorize('view-private');
        });

        $this->getJson('/api/private')
            ->assertForbidden()
            ->assertExactJson(['status' => 403, 'message' => 'Operación no autorizada']);
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

    public function test_validation_exception_preserves_its_configured_status(): void
    {
        $this->app['router']->post('/api/conflict', function () {
            throw ValidationException::withMessages(['email' => 'El email ya está en uso.'])
                ->status(409);
        });

        $this->postJson('/api/conflict')
            ->assertStatus(409)
            ->assertExactJson([
                'status' => 409,
                'message' => config('apiresponse.messages.validation'),
                'errors' => [['email' => ['El email ya está en uso.']]],
            ]);
    }

    public function test_validation_exception_preserves_its_explicit_response(): void
    {
        $this->app['router']->post('/api/custom-validation', function () {
            $validator = $this->app['validator']->make([], ['email' => ['required']]);
            $response = response()->json(['message' => 'Entrada inválida'], 400, ['X-Validation' => 'custom']);

            throw new ValidationException($validator, $response);
        });

        $this->postJson('/api/custom-validation')
            ->assertStatus(400)
            ->assertHeader('X-Validation', 'custom')
            ->assertExactJson(['message' => 'Entrada inválida']);
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
