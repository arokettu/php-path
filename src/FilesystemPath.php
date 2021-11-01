<?php

declare(strict_types=1);

namespace Arokettu\Path;

abstract class FilesystemPath extends AbstractAbsolutePath
{
    public function __construct(string $path, bool $strict = false)
    {
        if ($this instanceof WindowsPath || $this instanceof UnixPath) {
            parent::__construct($path, $strict);
            return;
        }

        throw new \LogicException('The class is not meant to be extended externally');
    }

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
