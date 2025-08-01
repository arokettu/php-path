<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests\Classes;

use Arokettu\Path\PathInterface;
use Arokettu\Path\RelativePathInterface;

final class BrokenRelativeImplementation implements RelativePathInterface
{
    public string $prefix {
        get => '';
    }
    public array $components {
        get => [
            0 => '123',
            2 => '234',
            3 => '345',
        ];
    }

    public function __toString(): string
    {
        return '';
    }

    public function isAbsolute(): bool
    {
        return false;
    }

    public function isRelative(): bool
    {
        return true;
    }

    public function toString(): string
    {
        return '';
    }

    public function resolveRelative(RelativePathInterface $path, bool $strict = false): PathInterface
    {
        throw new \LogicException('Not implemented');
    }

    public function isRoot(): bool
    {
        return false;
    }
}
