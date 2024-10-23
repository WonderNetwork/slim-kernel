<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

interface ServiceFactory {
    /**
     * @param ServicesBuilder $builder
     * @return iterable<string,mixed>
     */
    public function __invoke(ServicesBuilder $builder): iterable;
}
