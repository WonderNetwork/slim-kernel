<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\RouteContext;
use WonderNetwork\SlimKernel\Accessor\ArrayAccessor;

final class RouteArgument {
    public static function of(ServerRequestInterface $request): ArrayAccessor {
        $route = RouteContext::fromRequest($request)->getRoute();
        if (null === $route) {
            throw new HttpBadRequestException($request, "Route is not initialized on request");
        }

        return ArrayAccessor::of(
            $route->getArguments(),
            fn (string $message) => new HttpBadRequestException($request, $message),
        );
    }

    /**
     * @deprecated use self::of($request)->string($name)
     */
    public static function maybe(ServerRequestInterface $request, string $name): string {
        return self::of($request)->string($name);
    }

    /**
     * @deprecated use self::of($request)->maybeString($name)
     */
    public static function find(ServerRequestInterface $request, string $name): ?string {
        return self::of($request)->maybeString($name);
    }

    /**
     * @deprecated use self::of($request)->requireString($name)
     */
    public static function get(ServerRequestInterface $request, string $name): string {
        return self::of($request)->requireString($name);
    }
}
