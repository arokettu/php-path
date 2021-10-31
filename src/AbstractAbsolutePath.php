<?php

declare(strict_types=1);

namespace Arokettu\Path;

/**
 * @internal
 */
abstract class AbstractAbsolutePath extends AbstractPath implements AbsolutePathInterface
{
    /**
     * @param static|string $path
     * @param bool $strict
     * @return static
     */
    public function makeRelative($path, bool $strict = false): self
    {
        throw new \BadMethodCallException('not implemented');
    }
}
