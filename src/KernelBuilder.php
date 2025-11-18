<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use function WonderNetwork\SlimKernel\Collection\map;
use function WonderNetwork\SlimKernel\Collection\toArray;

final class KernelBuilder {
    private ServicesBuilder $servicesBuilder;
    /** @var ContainerBuilder<Container> */
    private ContainerBuilder $builder;
    private HookCollection $startupHook;

    public static function start(string $rootPath): self {
        return new self(new ServicesBuilder($rootPath));
    }

    private function __construct(ServicesBuilder $servicesBuilder) {
        $this->servicesBuilder = $servicesBuilder;
        $this->builder = new ContainerBuilder();
        $this->startupHook = new HookCollection();
        $this->register(new ServiceFactory\SlimServiceFactory());
    }

    /** @param array<string|mixed> $definitions */
    public function add(array $definitions): self {
        $this->builder->addDefinitions($definitions);

        return $this;
    }

    public function register(ServiceFactory ...$serviceFactories): self {
        $this->builder->addDefinitions(
            ...
            map(
                $serviceFactories,
                fn (ServiceFactory $serviceFactory) => toArray($serviceFactory($this->servicesBuilder)),
            ),
        );

        return $this;
    }

    public function onStartup(StartupHook ...$hooks): self {
        $this->startupHook->add(...$hooks);

        return $this;
    }

    public function glob(string ...$patterns): self {
        $this->builder->addDefinitions(
            ...DefinitionFileWithContext::fromManyPatterns($this->servicesBuilder, ...$patterns),
        );

        return $this;
    }

    public function useCache(?string $path): self {
        if ($path) {
            $this->builder->enableCompilation($path);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function build(): ContainerInterface {
        $this->onStartup(new StartupHook\ErrorHandlingHook());

        return $this->startupHook->boot($this->servicesBuilder, $this->builder->build());
    }
}
