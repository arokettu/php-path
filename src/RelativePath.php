<?php

declare(strict_types=1);

namespace Arokettu\Path;

final class RelativePath extends AbstractPath
{
    private bool $windows;

    public function __construct(string $path, bool $windows = false)
    {
        $this->windows = $windows;

        parent::__construct($path);
    }

    public static function unix(string $path): self
    {
        return new self($path, false);
    }

    public static function windows(string $path): self
    {
        return new self($path, true);
    }

    protected function parsePath(string $path): \SplDoublyLinkedList
    {
        $components = explode('/', $path);

        // forward slash is also a valid path separator on Windows
        // just also parse backslashes
        if ($this->windows) {
            $components = array_merge(
                ...array_map(fn ($a) => explode('\\', $a), $components)
            );
        }

        $parsedComponents = $this->normalize($components);

        // absolute-ish relative path
        $isRoot = $path[0] === '/' || $this->windows && $path[0] === '\\';
        if (!$isRoot) {
            $parsedComponents->unshift('.');
        }

        return $parsedComponents;
    }

    protected function buildRelative(string $path): RelativePath
    {
        return new RelativePath($path, $this->windows);
    }

    public function isRoot(): bool
    {
        return $this->components[0] !== '.' && $this->components[0] !== '..';
    }

    public function toString(): string
    {
        $directorySeparator = $this->windows ? '\\' : '/';
        $components = $this->components;

        if ($components[0] === '.') {
            $components = clone $components;
            $components->shift();
        }

        $path = \iter\join($directorySeparator, $components);

        if ($this->isRoot()) {
            $path = $directorySeparator . $path;
        }

        return $path;
    }
}
