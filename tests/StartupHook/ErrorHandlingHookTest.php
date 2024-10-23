<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\StartupHook;

use Acme\ErrorHandlingSpy;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Throwable;
use WonderNetwork\SlimKernel\KernelBuilder;
use WonderNetwork\SlimKernel\ServiceFactory\RoutesServiceFactory;

class ErrorHandlingHookTest extends TestCase {
    public function testCustomMiddlewaresTakePrecedence() {
        $container = KernelBuilder::start(__DIR__.'/../Resources/ErrorMiddleware')
            ->glob('app/services/*.php')
            ->register(new RoutesServiceFactory('app/middlewares/*.php'))
            ->register(new RoutesServiceFactory('app/routes/*.php'))
            ->build();

        /** @var App $app */
        $app = $container->get(App::class);
        /** @var ErrorHandlingSpy $spy */
        $spy = $container->get(ErrorHandlingSpy::class);

        $factory = new ServerRequestFactory();
        $response = $app->handle($factory->createServerRequest('GET', '/'));
        self::assertStringContainsString('Slim Application Error', (string) $response->getBody());
        self::assertInstanceOf(Throwable::class, $spy->error);
    }
}
