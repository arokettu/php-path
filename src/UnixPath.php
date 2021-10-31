<?php

declare(strict_types=1);

namespace Arokettu\Path;

final class UnixPath extends FilesystemPath
{
    public static function parse(string $path, bool $strict = false): self
    {
        return new self($path, $strict);
    }

    protected function parsePath(string $path, bool $strict): void
    {
        if ($path[0] !== '/') {
            throw new \InvalidArgumentException('Valid unix path must begin with a slash');
        }

        $components = explode('/', $path);

        $parsedComponents = $this->normalize($components);

        if ($parsedComponents[0] === '..') {
            if ($strict) {
                throw new \InvalidArgumentException('Path went beyond root');
            }

            do {
                $parsedComponents->shift();
            } while ($parsedComponents[0] === '..');
        }

        $this->prefix = '/';
        $this->components = $parsedComponents;
    }
}
