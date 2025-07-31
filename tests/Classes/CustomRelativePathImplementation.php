<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests\Classes;

use Arokettu\Path\PathInterface;
use Arokettu\Path\RelativePathInterface;

final class CustomRelativePathImplementation implements RelativePathInterface
{
    public string $prefix {
        get => '';
    }
    public readonly array $components;
    public readonly bool $isRoot;

    public function __construct(array $components, bool $isRoot)
    {
        $this->components = $components;
        $this->isRoot = $isRoot;
    }

    public function isAbsolute(): bool
    {
        return false;
    }

    public function isRelative(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return '';
    }

    public function toString(): string
    {
        throw new \BadMethodCallException('Irrelevant');
    }

    public function resolveRelative(RelativePathInterface $path, bool $strict = false): PathInterface
    {
        throw new \BadMethodCallException('Irrelevant');
    }

    public function isRoot(): bool
    {
        return $this->isRoot;
    }
}
