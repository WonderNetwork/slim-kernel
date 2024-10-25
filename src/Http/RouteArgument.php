<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\RouteContext;

final class RouteArgument {
    public static function maybe(ServerRequestInterface $request, string $name): string {
        return self::find($request, $name) ?? '';
    }

    public static function find(ServerRequestInterface $request, string $name): ?string {
        $route = RouteContext::fromRequest($request)->getRoute();
        if (null === $route) {
            throw new HttpBadRequestException(
                $request,
                "Route is not initialized on request while accessing $name",
            );
        }
        return $route->getArgument($name);
    }

    public static function get(ServerRequestInterface $request, string $name): string {
        $value = self::find($request, $name);
        if (null === $value) {
            throw new HttpBadRequestException($request, "Route argument $name not found");
        }
        return $value;
    }
}
