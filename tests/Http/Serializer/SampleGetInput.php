<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

final class SampleGetInput {
    public int $page = 1;
    public int $perPage = 100;
    /**
     * @var string[]
     */
    public array $lists;
    /**
     * @var array<string, string>
     */
    public array $arrays;
    public BooleansInput $booleans;
}
