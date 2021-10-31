<?php

declare(strict_types=1);

namespace Arokettu\Path;

interface AbsolutePathInterface extends PathInterface
{
    /**
     * @param static|string $path
     * @return static
     */
    public function makeRelative($path, bool $strict = false): self;
}
