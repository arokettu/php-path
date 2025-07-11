<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\PathUtils;
use Arokettu\Path\Tests\Classes\NotARelativeNotAnAbsolute;
use PHPUnit\Framework\TestCase;

final class PathUtilsTest extends TestCase
{
    public function testMakeRelative(): void
    {
        self::assertEquals(
            '../../.config/composer',
            PathUtils::makeRelativePath(
                '/home/arokettu/tmp/test',
                '/home/arokettu/.config/composer',
            ),
        );

        self::assertEquals(
            '..\..\AppData\Roaming',
            PathUtils::makeRelativePath(
                'C:\Users\Arokettu\tmp\test',
                'C:\Users\Arokettu\AppData\Roaming',
            ),
        );
    }

    public function testResolveRelative(): void
    {
        // any, absolute
        self::assertEquals(
            '/home/arokettu/.config/composer',
            PathUtils::resolveRelativePath(
                '/home/arokettu/tmp/test',
                '/home/arokettu/.config/composer',
            ),
        );

        // absolute, relative
        self::assertEquals(
            '/home/arokettu/.config/composer',
            PathUtils::resolveRelativePath(
                '/home/arokettu/tmp/test',
                '../../.config/composer',
            ),
        );

        // relative, relative
        self::assertEquals(
            '.config/composer',
            PathUtils::resolveRelativePath(
                './tmp/test',
                '../../.config/composer',
            ),
        );
    }

    public function testResolveRelativeBroken(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage(
            'PathInterface object must be either AbsolutePathInterface or RelativePathInterface',
        );

        PathUtils::resolveRelativePath('/', new NotARelativeNotAnAbsolute());
    }
}
