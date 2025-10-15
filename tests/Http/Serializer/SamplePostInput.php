<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

final class SamplePostInput {
    public string $name;
    public int $value;
    /** @var TagInput[] */
    public array $tags;
    public TagInput $tag;
}
