<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use function WonderNetwork\SlimKernel\Collection\findFiles;

final class ServicesBuilder {
    private string $rootPath;
    private Autowire $autowire;
    private ConfigurationFiles $files;

    public function __construct(string $rootPath) {
        $this->rootPath = $rootPath;
        $this->autowire = Autowire::fromRootPath($rootPath);
        $this->files = new ConfigurationFiles($rootPath);
    }

    /**
     * @param string $pattern
     * @return string[]
     */
    public function glob(string $pattern): iterable {
        return findFiles($this->rootPath, $pattern);
    }

    public function autowire(): Autowire {
        return $this->autowire;
    }

    public function files(): ConfigurationFiles {
        return $this->files;
    }
}
