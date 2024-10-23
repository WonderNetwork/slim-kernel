<?php
declare(strict_types=1);

namespace Acme;

use Throwable;

final class ErrorHandlingSpy {
    public ?Throwable $error = null;

    public function handleError(Throwable $e): void {
        $this->error = $e;
    }
}
