<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\SlimExtension;

final class ErrorMiddlewareConfiguration {
    private bool $displayErrors = false;
    private bool $logErrors = false;

    public static function silent(): self {
        return self::create()
            ->withLogging()
            ->withoutDisplay();
    }

    public static function verbose(): self {
        return self::create()
            ->withoutLogging()
            ->withDisplay();
    }
    public static function create(): self {
        return new self();
    }

    public function isDisplayErrors(): bool {
        return $this->displayErrors;
    }

    public function isLogErrors(): bool {
        return $this->logErrors;
    }

    private function __construct() {
    }

    public function withLogging(): self {
        $result = new self();
        $result->displayErrors = $this->displayErrors;
        $result->logErrors = true;
        return $result;
    }

    public function withoutLogging(): self {
        $result = new self();
        $result->displayErrors = $this->displayErrors;
        $result->logErrors = false;
        return $result;
    }

    public function withDisplay(): self {
        $result = new self();
        $result->displayErrors = true;
        $result->logErrors = $this->logErrors;
        return $result;
    }

    public function withoutDisplay(): self {
        $result = new self();
        $result->displayErrors = false;
        $result->logErrors = $this->logErrors;
        return $result;
    }
}
