<?php

declare(strict_types=1);

namespace Arokettu\Path;

interface PathInterface extends \Stringable
{
    public function getComponents(): array;
    public function toString(): string;

    /**
     * @param RelativePathInterface|string $path
     * @return static
     */
    public function resolveRelative($path, bool $strict = false): self;
}
