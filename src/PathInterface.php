<?php

/**
 * @copyright 2021 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\Path;

interface PathInterface extends \Stringable
{
    public string $prefix { get; }
    /** @var list<string> */
    public array $components { get; }

    public function isAbsolute(): bool;
    public function isRelative(): bool;

    public function toString(): string;

    /**
     * @return static
     */
    public function resolveRelative(RelativePathInterface $path, bool $strict = false): self;
}
