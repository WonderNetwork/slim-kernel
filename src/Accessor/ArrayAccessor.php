<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Accessor;

use Throwable;
use function WonderNetwork\SlimKernel\Collection\map;

final class ArrayAccessor {
    /** @var array<mixed,mixed> */
    private array $payload;
    /**
     * @var callable
     */
    private $exceptionFactory;

    public static function of(mixed $input, callable $factory = null): self {
        $factory ??= fn (string $message) => new ArrayAccessorException($message);

        if (is_array($input)) {
            return new self($input, $factory);
        }

        return self::empty($factory);
    }

    private static function empty(callable $exceptionFactory): self {
        return new self([], $exceptionFactory);
    }

    /**
     * @param array<mixed,mixed> $payload
     * @param callable(string):Throwable $exceptionFactory
     */
    private function __construct(array $payload, callable $exceptionFactory) {
        $this->payload = $payload;
        $this->exceptionFactory = $exceptionFactory;
    }

    public function at(string $index): self {
        return $this->maybeAt($index) ?? self::empty($this->exceptionFactory);
    }

    public function maybeAt(string $index): ?self {
        $raw = $this->maybeArray($index);

        if (null === $raw) {
            return null;
        }

        return new self($raw, $this->exceptionFactory);
    }

    public function tryAtAny(string ...$keys): self {
        foreach ($keys as $key) {
            $accessor = $this->maybeAt($key);

            if ($accessor) {
                return $accessor;
            }
        }

        $this->throw(
            sprintf(
                'Could not find array at either of keys: %s',
                implode(', ', $keys),
            ),
        );
    }

    /**
     * @return string[]
     */
    public function allString(): array {
        return map(
            $this->payload,
            function ($value, $key) {
                try {
                    return StringValue::of($value)->toString();
                } catch (StringValueException $e) {
                    $this->throw("Value at $key is not a string");
                }
            },
        );
    }

    /**
     * @return int[]
     */
    public function allInt(): array {
        return map(
            $this->payload,
            function ($value, $key) {
                try {
                    return StringValue::of($value)->toInt();
                } catch (StringValueException $e) {
                    $this->throw("Value at $key is not an int");
                }
            },
        );
    }

    /**
     * @return float[]
     */
    public function allFloat(): array {
        return map(
            $this->payload,
            function ($value, $key) {
                try {
                    return StringValue::of($value)->toFloat();
                } catch (StringValueException $e) {
                    $this->throw("Value at $key is not a float");
                }
            },
        );
    }

    /**
     * @return bool[]
     */
    public function allBool(): array {
        return map(
            $this->payload,
            function ($value, $key) {
                try {
                    return StringValue::of($value)->toBool();
                } catch (StringValueException $e) {
                    $this->throw("Value at $key is not a boolean");
                }
            },
        );
    }

    public function string(string $name, string $default = ''): string {
        return $this->maybeString($name) ?? $default;
    }

    public function requireString(string $name): string {
        $raw = $this->maybeString($name);

        if (null === $raw) {
            $this->throw("Required field named $name not found");
        }

        return $raw;
    }

    public function maybeString(string $name): ?string {
        $raw = $this->payload[$name] ?? null;

        if (null === $raw) {
            return null;
        }

        try {
            return StringValue::of($raw)->toString();
        } catch (StringValueException $e) {
            $this->throw("Required field named $name is not a string");
        }
    }

    public function int(string $name, int $default = 0): int {
        return $this->maybeInt($name) ?? $default;
    }

    public function requireInt(string $name): int {
        $raw = $this->maybeInt($name);

        if (null === $raw) {
            $this->throw("Required field named $name not found");
        }

        return $raw;
    }

    public function maybeInt(string $name): ?int {
        $raw = $this->payload[$name] ?? null;

        if (null === $raw) {
            return null;
        }

        try {
            return StringValue::of($raw)->toInt();
        } catch (StringValueException $e) {
            $this->throw("Required field named $name is not an int");
        }
    }

    public function float(string $name, float $default = 0.0): float {
        return $this->maybeFloat($name) ?? $default;
    }

    public function requireFloat(string $name): float {
        $raw = $this->maybeFloat($name);

        if (null === $raw) {
            $this->throw("Required field named $name not found");
        }

        return $raw;
    }

    public function maybeFloat(string $name): ?float {
        $raw = $this->payload[$name] ?? null;

        if (null === $raw) {
            return null;
        }

        try {
            return StringValue::of($raw)->toFloat();
        } catch (StringValueException $e) {
            $this->throw("Required field named $name is not a float");
        }
    }

    public function bool(string $name, bool $default = false): bool {
        return $this->maybeBool($name) ?? $default;
    }

    public function requireBool(string $name): bool {
        $raw = $this->maybeBool($name);

        if (null === $raw) {
            $this->throw("Required field named $name not found");
        }

        return $raw;
    }

    public function maybeBool(string $name): ?bool {
        $raw = $this->payload[$name] ?? null;

        if (null === $raw) {
            return null;
        }

        try {
            return StringValue::of($raw)->toBool();
        } catch (StringValueException $e) {
            $this->throw("Required field named $name is not a boolean");
        }
    }

    /** @return array<mixed,mixed> */
    public function array(string $name): array {
        return $this->maybeArray($name) ?? [];
    }

    /** @return ?array<mixed,mixed> */
    public function maybeArray(string $name): ?array {
        $raw = $this->payload[$name] ?? null;

        if (null === $raw) {
            return null;
        }

        if (false === is_array($raw)) {
            $this->throw("Required field named $name is not an array");
        }

        return $raw;
    }

    /**
     * @return never
     */
    private function throw(string $message) {
        throw ($this->exceptionFactory)($message);
    }
}
