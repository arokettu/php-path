<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests\Classes;

use Arokettu\Path\PathInterface;
use Arokettu\Path\RelativePathInterface;

class CustomRelativePathImplementation implements RelativePathInterface
{
    private array $components;
    private bool $isRoot;

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

    public function getPrefix(): string
    {
        return '';
    }

    public function getComponents(): array
    {
        return $this->components;
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
