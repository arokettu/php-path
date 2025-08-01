<?php

declare(strict_types=1);

namespace Arokettu\Path;

use Arokettu\Path\Exceptions\PathWentBeyondRootException;
use ValueError;

final readonly class UnixPath extends FilesystemPath
{
    public static function parse(string $path, bool $strict = false): self
    {
        return new self($path, $strict);
    }

    protected function parsePath(string $path, bool $strict): void
    {
        if ($path[0] !== '/') {
            throw new ValueError('Valid unix path must begin with a slash');
        }

        $components = explode('/', $path);

        $parsedComponents = $this->normalize($components);

        if ($parsedComponents !== [] && $parsedComponents[0] === '..') {
            if ($strict) {
                throw new PathWentBeyondRootException('Path went beyond root');
            }

            do {
                array_shift($parsedComponents);
            } while ($parsedComponents[0] === '..');
        }

        $this->prefix = '/';
        $this->components = $parsedComponents;
    }
}
