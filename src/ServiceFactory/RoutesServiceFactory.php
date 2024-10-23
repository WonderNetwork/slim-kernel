<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ServiceFactory;

use Slim\App;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use WonderNetwork\SlimKernel\SlimExtension\SlimClosuresCollection;
use function DI\decorate;

final class RoutesServiceFactory implements ServiceFactory {
    private string $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function __invoke(ServicesBuilder $builder): iterable {
        $closures = SlimClosuresCollection::of(...$builder->files()->glob($this->path));

        yield App::class => decorate(static fn (App $previous) => $closures->applyTo($previous));
    }
}
