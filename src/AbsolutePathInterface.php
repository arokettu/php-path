<?php

declare(strict_types=1);

namespace Arokettu\Path;

interface AbsolutePathInterface extends PathInterface
{
    /**
     * @param static $targetPath
     * @param callable|null $equals (string $a, string $b): bool
     */
    public function makeRelative(self $targetPath, callable|null $equals = null): RelativePathInterface;
}
