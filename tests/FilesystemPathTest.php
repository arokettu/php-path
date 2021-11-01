<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\FilesystemPath;
use PHPUnit\Framework\TestCase;

class FilesystemPathTest extends TestCase
{
    public function testDoNotAllowExtending(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The class is not meant to be extended externally');

        new class ('') extends FilesystemPath {
            protected function parsePath(string $path, bool $strict): void
            {
                // whatever
            }
        };
    }
}
