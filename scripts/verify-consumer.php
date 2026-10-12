<?php

use Hardcodear\ApiResponseService\ApiResponseService;
use Hardcodear\ApiResponseService\ExceptionApiRegistrar;
use Hardcodear\ApiResponseService\Facades\ApiResponse;
use Hardcodear\ApiResponseService\Providers\ApiResponseServiceProvider;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;

$appDir = $argv[1] ?? throw new RuntimeException('Pass the Laravel application directory.');
require $appDir . '/vendor/autoload.php';
$app = require $appDir . '/bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

function verify(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

verify($app->getProvider(ApiResponseServiceProvider::class) !== null, 'Provider autodiscovery failed.');
verify(function_exists('apiresponse'), 'Composer did not autoload the helper.');
verify(apiresponse() instanceof ApiResponseService, 'Helper resolution failed.');
verify(apiresponse() === $app->make('apiresponse'), 'Singleton resolution failed.');
verify(ApiResponse::success('Facade')->getStatusCode() === 200, 'Facade resolution failed.');
verify(\apiresponse::success('Alias')->getStatusCode() === 200, 'Discovered facade alias failed.');
verify($app->configurationIsCached(), 'Configuration cache was not exercised.');
verify(config('apiresponse.api_patterns') === ['api', 'api/*'], 'Published configuration failed.');

ExceptionApiRegistrar::bind(new Exceptions($app->make(ExceptionHandler::class)));
$app['router']->get('/api/package-smoke', fn () => apiresponse()->success('Installed', ['installed' => true]));
$app['router']->post('/api/package-validation', function (Request $request) {
    $request->validate(['email' => ['required', 'email']]);
});

$kernel = $app->make(HttpKernel::class);
$cases = [
    ['GET', '/api/package-smoke', 200, ['status' => 200, 'message' => 'Installed', 'data' => ['installed' => true]]],
    ['GET', '/api/package-missing', 404, ['status' => 404, 'message' => config('apiresponse.messages.not_found')]],
    ['PUT', '/api/package-smoke', 405, null],
    ['POST', '/api/package-validation', 422, null],
];

foreach ($cases as [$method, $uri, $status, $expected]) {
    $request = Request::create($uri, $method, server: ['HTTP_ACCEPT' => 'application/json']);
    $response = $kernel->handle($request);
    $json = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    verify($response->getStatusCode() === $status, "Unexpected HTTP status for $method $uri.");
    verify($json['status'] === $status, "Unexpected JSON status for $method $uri.");
    verify(str_contains($response->headers->get('Content-Type', ''), 'application/json'), 'Expected JSON content type.');
    if ($expected !== null) {
        verify($json === $expected, "Unexpected payload for $method $uri.");
    }
    if ($status === 405) {
        verify(str_contains($response->headers->get('Allow', ''), 'GET'), 'Missing Allow header.');
    }
    if ($status === 422) {
        verify(isset($json['errors'][0]['email']), 'Missing validation errors.');
    }
    $kernel->terminate($request, $response);
}

echo "Consumer installation, autodiscovery, published/cached config and HTTP integration passed.\n";
