<?php

declare(strict_types=1);

namespace Arokettu\Path;

use Arokettu\Path\Exceptions\PathWentBeyondRootException;
use ValueError;

final readonly class WindowsPath extends FilesystemPath
{
    public static function parse(string $path, bool $strict = false): self
    {
        return new self($path, $strict);
    }

    protected function parsePath(string $path, bool $strict): void
    {
        if (preg_match('@^[A-Za-z]:[\\\\/]@', $path, $matches)) {
            // DOS path
            $prefix = ucfirst($matches[0]); // uppercase drive letter
            $restOfPath = substr($path, \strlen($prefix));

            $this->parseDOS($prefix, $restOfPath, $strict);
        } elseif (preg_match('@^\\\\\\\\[.?]\\\\([^\\\\/]+)\\\\@', $path, $matches)) {
            // UNC local volume
            $prefix = $matches[0];

            // if the volume is a drive letter, uppercase it
            if (preg_match('/^[a-zA-Z]:$/', $matches[1])) {
                // \\?\C:\
                $prefix[4] = strtoupper($prefix[4]);
            }

            $restOfPath = substr($path, \strlen($prefix));

            $this->parseUNC($prefix, $restOfPath);
        } elseif (preg_match('@^\\\\\\\\[^.?\\\\/][^\\\\/]*\\\\|\\\\\\\\[.?][^\\\\/][^\\\\/]+\\\\@', $path, $matches)) {
            $prefix = $matches[0];
            $restOfPath = substr($path, \strlen($prefix));

            $this->parseUNC($prefix, $restOfPath);
        } else {
            throw new ValueError('Unrecognized Windows path');
        }
    }

    private function parseDOS(string $prefix, string $restOfPath, bool $strict): void
    {
        // forward slash is also a valid path separator in DOS paths
        // just also parse backslashes
        $components = explode('/', $restOfPath);
        $components = array_merge(
            ...array_map(static fn ($a) => explode('\\', $a), $components),
        );

        $parsedComponents = $this->normalize($components);

        if ($parsedComponents !== [] && $parsedComponents[0] === '..') {
            if ($strict) {
                throw new PathWentBeyondRootException('Path went beyond root');
            }

            do {
                array_shift($parsedComponents);
            } while ($parsedComponents[0] === '..');
        }

        // normalize prefix: use backslash
        $this->prefix = strtr($prefix, ['/' => '\\']);
        $this->components = $parsedComponents;
    }

    // no $strict param, UNC is always strict
    private function parseUNC(string $prefix, string $restOfPath): void
    {
        if (str_contains($restOfPath, '/')) {
            throw new ValueError('Slashes are not allowed in UNC paths');
        }

        $components = explode('\\', $restOfPath);

        foreach ($components as $component) {
            if ($component === '.' || $component === '..') {
                throw new ValueError('. and .. are not allowed in UNC paths');
            }
        }

        $this->prefix = $prefix;
        $this->components = $components;
    }

    public function toString(): string
    {
        return $this->prefix . implode('\\', $this->components);
    }

    protected function buildRelative(array $components): RelativePathInterface
    {
        $path = new RelativePath('.', true);
        $path = clone($path, ['components' => $components]);

        return $path;
    }
}
