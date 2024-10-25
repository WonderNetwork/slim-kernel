<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Accessor;

final class StringValue {
    private string $value;
    /** @var mixed */
    private $raw;

    /**
     * @param mixed $value
     * @throws StringValueException
     */
    public static function of($value): self {
        if (false === is_scalar($value)) {
            throw new StringValueException('Can’t convert a non scalar value to string');
        }

        return new self((string) $value, $value);
    }

    /**
     * @param string $value
     * @param mixed $raw
     */
    private function __construct(string $value, $raw) {
        $this->value = $value;
        $this->raw = $raw;
    }

    public function toString(): string {
        return $this->value;
    }

    /**
     * @throws StringValueException
     */
    public function toInt(): int {
        if (is_int($this->raw)) {
            return $this->raw;
        }

        if (false === is_numeric($this->value)) {
            throw new StringValueException('Can’t convert non-numeric value to an integer');
        }

        return (int) $this->value;
    }

    /**
     * @throws StringValueException
     */
    public function toBool(): bool {
        if (is_bool($this->raw)) {
            return $this->raw;
        }

        if (false === in_array($this->value, ['0', '1', 0, 1], true)) {
            throw new StringValueException('Can’t convert value to a boolean');
        }

        return (bool) $this->value;
    }
}
