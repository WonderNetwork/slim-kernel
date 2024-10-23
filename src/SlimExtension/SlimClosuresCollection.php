<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\SlimExtension;

use Closure;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use RuntimeException;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;
use function WonderNetwork\SlimKernel\Collection\collection;

final class SlimClosuresCollection {
    private const ALLOWED_TYPES = [App::class, RouteCollectorProxyInterface::class];

    /** @var Closure[] */
    private array $closures;

    public static function of(Closure ...$closures): self {
        return new self(...$closures);
    }

    private function __construct(Closure ...$closures) {
        collection($closures)
            ->map(static fn (Closure $closure) => new ReflectionClosure($closure))
            ->map(static fn (ReflectionClosure $closure) => $closure->getParameters()[0])
            ->filter()
            ->map(static fn (ReflectionParameter $parameter) => $parameter->getType())
            ->filter(static fn (ReflectionType $type) => $type instanceof ReflectionNamedType)
            ->map(static fn (ReflectionNamedType $type) => $type->getName())
            ->filter(static fn (string $name) => false === in_array($name, self::ALLOWED_TYPES, true))
            ->each(
                static function (string $name) {
                    throw new RuntimeException(
                        "Closure takes an invalid type for its first parameter: $name",
                    );
                });
        $this->closures = $closures;
    }

    /**
     * @template App of RouteCollectorProxyInterface
     * @param App $app
     * @return App
     */
    public function applyTo(RouteCollectorProxyInterface $app): RouteCollectorProxyInterface {
        foreach ($this->closures as $closure) {
            $closure($app);
        }
        return $app;
    }
}
