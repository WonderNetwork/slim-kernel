<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\StartupHook;

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use WonderNetwork\SlimKernel\KernelBuilder;

class RoutesStartupHookTest extends TestCase {
    public function test(): void {
        /** @var App $app */
        $app = KernelBuilder::start(__DIR__.'/../Resources/App')
            ->glob('app/services/*.php')
            ->onStartup(
                new RoutesStartupHook('app/routes/*.php'),
                new RoutesStartupHook('app/middlewares/*.php'),
            )
            ->build()
            ->get(App::class);
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/hello/world');
        $response = $app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello world', (string) $response->getBody());
        self::assertSame('true', $response->getHeaderLine('X-Middleware'));
    }
}
