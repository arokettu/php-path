<?php

declare(strict_types=1);

namespace Arokettu\Path;

use Arokettu\Path\Exceptions\PathWentBeyondRootException;
use ValueError;

final readonly class StreamPath extends AbstractAbsolutePath
{
    public static function parse(string $path, bool $strict = false): self
    {
        return new self($path, $strict);
    }

    protected function parsePath(string $path, bool $strict): void
    {
        if (!preg_match('@^[-.+a-zA-Z0-9]+://@', $path, $matches)) {
            throw new ValueError('The path does not appear to be a PHP stream path');
        }

        $prefix = $matches[0];
        $restOfPath = substr($path, \strlen($prefix));
        $components = explode('/', $restOfPath);

        $parsedComponents = $this->normalize($components);

        if ($parsedComponents !== [] && $parsedComponents[0] === '..') {
            if ($strict) {
                throw new PathWentBeyondRootException('Path went beyond root');
            }

            do {
                array_shift($parsedComponents);
            } while ($parsedComponents[0] === '..');
        }

        $this->prefix = $prefix;
        $this->components = $parsedComponents;
    }
}
