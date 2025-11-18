<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use WonderNetwork\SlimKernel\KernelBuilder;
use WonderNetwork\SlimKernel\SlimExtension\ErrorMiddlewareConfiguration;

final class DeserializeParameterResolverTest extends TestCase {
    public function testDeserialize(): void {
        $container = KernelBuilder::start(__DIR__.'/../../Resources/App')->build();

        /** @var App<ContainerInterface> $app */
        $app = $container->get(App::class);
        $app->get('/', EchoController::class);
        $post = [
            'name' => 'John Doe',
            'value' => 124,
            'tags' => [
                ['name' => 'cats'],
                ['name' => 'dogs'],
            ],
            'tag' => [
                'name' => 'alpha',
            ],
            'enum' => 'foo',
        ];
        $get = [
            'page' => 3,
            'lists' => ['alpha', 'bravo'],
            'arrays' => ['charlie' => 'delta'],
            'booleans' => [
                'true' => true,
                'false' => false,
                'one' => 1,
                'zero' => 0,
            ],
        ];

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/?'.http_build_query($get))
            ->withParsedBody($post);
        $response = $app->handle($request);

        $body = $response->getBody()->getContents();

        self::assertSame(
            200,
            $response->getStatusCode(),
            $body,
        );

        $actual = json_decode(
            $body,
            associative: true,
            depth: 12,
            flags: JSON_THROW_ON_ERROR,
        );
        $expected = ['post' => $post, 'get' => $get + ['perPage' => 100]];
        self::assertEquals($expected, $actual);
    }

    public function testDeserializeFailure(): void {
        $container = KernelBuilder::start(__DIR__.'/../../Resources/App')
            ->add([
                ErrorMiddlewareConfiguration::class => ErrorMiddlewareConfiguration::verbose(),
            ])
            ->build();

        /** @var App<ContainerInterface> $app */
        $app = $container->get(App::class);
        $app->get('/', EchoController::class);
        $post = [
            'name' => 'John Doe',
            'value' => 124,
            'tags' => [
                'invalid format',
            ],
        ];

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/')
            ->withParsedBody($post);

        $response = $app->handle($request);
        $actual = (string) $response->getBody();
        self::assertStringContainsString(
            'Failed to parse input because of missing fields: name',
            $actual,
        );
    }
}
