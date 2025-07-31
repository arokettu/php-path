<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests\Classes;

use Arokettu\Path\PathInterface;
use Arokettu\Path\RelativePathInterface;

final class NotARelativeNotAnAbsolute implements PathInterface
{
    public string $prefix {
        get => throw new \LogicException();
    }
    public array $components {
        get => throw new \LogicException();
    }

    public function __toString(): string
    {
        throw new \LogicException();
    }

    public function isAbsolute(): bool
    {
        throw new \LogicException();
    }

    public function isRelative(): bool
    {
        throw new \LogicException();
    }

    public function toString(): string
    {
        throw new \LogicException();
    }

    public function resolveRelative(RelativePathInterface $path, bool $strict = false): PathInterface
    {
        throw new \LogicException();
    }
}
