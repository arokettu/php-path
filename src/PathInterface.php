<?php

declare(strict_types=1);

namespace Arokettu\Path;

interface PathInterface extends \Stringable
{
    public function isAbsolute(): bool;
    public function isRelative(): bool;

    public function getPrefix(): string;
    public function getComponents(): array;
    public function toString(): string;

    /**
     * @return static
     */
    public function resolveRelative(RelativePathInterface $path, bool $strict = false): self;
}
