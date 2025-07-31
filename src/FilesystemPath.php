<?php

declare(strict_types=1);

namespace Arokettu\Path;

abstract readonly class FilesystemPath extends AbstractAbsolutePath
{
    /**
     * @codeCoverageIgnore OS specific
     */
    public static function parse(string $path, bool $strict = false): self
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return new WindowsPath($path, $strict);
        }

        if (DIRECTORY_SEPARATOR === '/') {
            return new UnixPath($path, $strict);
        }

        throw new \LogicException('Unknown directory separator: ' . DIRECTORY_SEPARATOR);
    }
}
