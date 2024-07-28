<?php

declare(strict_types=1);

namespace Arokettu\Path;

final class StreamPath extends AbstractAbsolutePath
{
    public static function parse(string $path, bool $strict = false): self
    {
        return new self($path, $strict);
    }

    protected function parsePath(string $path, bool $strict): void
    {
        if (!preg_match('@^[-.+a-zA-Z0-9]+://@', $path, $matches)) {
            throw new \UnexpectedValueException('The path does not appear to be a PHP stream path');
        }

        $prefix = $matches[0];
        $restOfPath = substr($path, \strlen($prefix));
        $components = explode('/', $restOfPath);

        $parsedComponents = $this->normalize($components);

        if ($parsedComponents->count() > 0 && $parsedComponents[0] === '..') {
            if ($strict) {
                throw new \UnexpectedValueException('Path went beyond root');
            }

            do {
                $parsedComponents->shift();
            } while ($parsedComponents[0] === '..');
        }

        $this->prefix = $prefix;
        $this->components = $parsedComponents;
    }
}
