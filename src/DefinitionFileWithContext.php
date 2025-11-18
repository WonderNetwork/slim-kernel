<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use DI\Definition\Definition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\ReflectionBasedAutowiring;
use Exception;
use Generator;
use Iterator;
use RuntimeException;

final class DefinitionFileWithContext implements DefinitionSource {
    /** @var string[] */
    private array $files;
    private ServicesBuilder $builder;
    private DefinitionArray $array;

    /**
     * @return Generator<self>
     */
    public static function fromManyPatterns(ServicesBuilder $builder, string ...$patterns): Generator {
        foreach ($patterns as $pattern) {
            yield new self($builder, ...$builder->glob($pattern));
        }
    }

    public function __construct(ServicesBuilder $builder, string ...$files) {
        $this->files = $files;
        $this->builder = $builder;
    }

    /**
     * @throws InvalidDefinition
     * @throws Exception
     */
    public function getDefinition(string $name): ?Definition {
        return $this->getArray()->getDefinition($name);
    }

    /**
     * @throws Exception
     */
    public function getDefinitions(): array {
        return $this->getArray()->getDefinitions();
    }

    /**
     * @throws Exception
     */
    private function getArray(): DefinitionArray {
        if (isset($this->array)) {
            return $this->array;
        }

        $result = [];

        foreach ($this->files as $file) {
            $definitions = require $file;

            if ($definitions instanceof ServiceFactory) {
                $definitions = $definitions($this->builder);
            }

            // support for yielding closures:
            if ($definitions instanceof Iterator) {
                $definitions = iterator_to_array($definitions);
            }

            if (false === is_array($definitions)) {
                throw new RuntimeException(
                    sprintf(
                        '"%s" did not return proper service definitions',
                        $file,
                    ),
                );
            }

            $result += $definitions;
        }

        return $this->array = new DefinitionArray($result, new ReflectionBasedAutowiring());
    }
}
