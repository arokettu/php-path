<?php

declare(strict_types=1);

namespace Arokettu\Path;

interface RelativePathInterface extends PathInterface
{
    public function isRoot(): bool;
}
