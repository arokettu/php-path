<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\RelativePath;
use PHPUnit\Framework\TestCase;

class RelativePathTest extends TestCase
{
    public function testCreate(): void
    {
        // 'absolute' relative path
        $path = RelativePath::unix('/i/am/./skipme/../test/./path');
        self::assertEquals('/i/am/test/path', $path->toString());

        // relative path from the current directory
        $path = RelativePath::unix('./i/am/./skipme/../test/./path');
        self::assertEquals('i/am/test/path', $path->toString());

        // relative path from the parent directory
        $path = RelativePath::unix('.././../i/am/./skipme/../test/./path');
        self::assertEquals('../../i/am/test/path', $path->toString());
    }

    public function testCreateWindows(): void
    {
        // 'absolute' relative path
        $path = RelativePath::windows('\i\am\./skipme\..\test\.\path');
        self::assertEquals('\i\am\test\path', $path->toString());

        // relative path from the current directory
        $path = RelativePath::windows('.\i\am/.\skipme\..\test\.\path');
        self::assertEquals('i\am\test\path', $path->toString());

        // relative path from the parent directory
        $path = RelativePath::windows('..\.\..\i\am\.\skipme\../test\.\path');
        self::assertEquals('..\..\i\am\test\path', $path->toString());
    }

    public function testResolveRelative(): void
    {
        $paths = [
            new RelativePath('/i/am/test/path'),
            new RelativePath('i/am/test/path'),
            new RelativePath('../../i/am/test/path'),
            new RelativePath('../../../../../../../../i/am/test/path'),
        ];
        $relativePaths = $paths;

        $matrix = [
            [
                '/i/am/test/path',
                '/i/am/test/path/i/am/test/path',
                '/i/am/i/am/test/path',
                '/i/am/test/path',
            ],
            [
                '/i/am/test/path',
                'i/am/test/path/i/am/test/path',
                'i/am/i/am/test/path',
                '../../../../i/am/test/path',
            ],
            [
                '/i/am/test/path',
                '../../i/am/test/path/i/am/test/path',
                '../../i/am/i/am/test/path',
                '../../../../../../i/am/test/path',
            ],
            [
                '/i/am/test/path',
                '../../../../../../../../i/am/test/path/i/am/test/path',
                '../../../../../../../../i/am/i/am/test/path',
                '../../../../../../../../../../../../i/am/test/path',
            ],
        ];

        foreach ($paths as $pi => $p) {
            foreach ($relativePaths as $rpi => $rp) {
                $matrixResult = $matrix[$pi][$rpi];

                self::assertEquals($matrixResult, $p->resolveRelative($rp)->toString());
            }
        }
    }
}
