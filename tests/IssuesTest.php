<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\WindowsPath;
use PHPUnit\Framework\TestCase;

// minor tests for minor issues
class IssuesTest extends TestCase
{
    public function testCallable(): void
    {
        // AbsolutePathInterface::makeRelative should accept any callable, not just closure
        // the change was made in 2.0.0 but after revert in 2.0.1 it was also reverted to Closure

        // make invokable object
        $comparer = new class () {
            public function __invoke(string $a, string $b): bool
            {
                return strtoupper($a) === strtoupper($b);
            }
        };

        $path1 = WindowsPath::parse('C:\\Test\\Test1');
        $path2 = WindowsPath::parse('C:\\test\\test2');

        self::assertEquals('..\\Test1', $path2->makeRelative($path1, $comparer)->toString());
    }
}
