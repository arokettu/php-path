<?php

declare(strict_types=1);

namespace Arokettu\Path;

interface AbsolutePathInterface extends PathInterface
{
    /**
     * @param static $targetPath
     */
    public function makeRelative(self $targetPath, ?\Closure $equals = null): RelativePathInterface;
}
