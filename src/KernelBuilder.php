<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use function WonderNetwork\SlimKernel\Collection\toArray;

final class KernelBuilder {
    private ServicesBuilder $servicesBuilder;
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
        $this->onStartup(new StartupHook\ErrorHandlingHook());
    }

    /** @param array<string|mixed> $definitions */
    public function add(array $definitions): self {
        $this->builder->addDefinitions($definitions);
        return $this;
    }

    public function register(ServiceFactory $serviceFactory): self {
        $this->builder->addDefinitions(toArray($serviceFactory($this->servicesBuilder)));
        return $this;
    }

    public function onStartup(StartupHook $hook): self {
        $this->startupHook->add($hook);
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
        return $this->startupHook->boot($this->builder->build());
    }
}
