<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\RelativePath;
use Arokettu\Path\UnixPath;
use PHPUnit\Framework\TestCase;

class UnixPathTest extends TestCase
{
    public function testCreate(): void
    {
        $path1 = UnixPath::parse('/i/./am/./skipme/./.././test/./unix/path');
        self::assertEquals('/i/am/test/unix/path', $path1->toString());

        $path2 = UnixPath::parse('/invalid/level/of/nesting/../../../../../../../../../../i/am/test/unix/path');
        self::assertEquals('/i/am/test/unix/path', $path2->toString());

        $path3 = UnixPath::parse('/i/./am/./skipme/./.././test/./unix/path', true);
        self::assertEquals('/i/am/test/unix/path', $path3->toString());
    }

    public function testCreateStrict(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Path went beyond root');

        UnixPath::parse('/invalid/level/of/nesting/../../../../../../../../../../i/am/test/unix/path', true);
    }

    public function testCreateInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Valid unix path must begin with a slash');

        UnixPath::parse('not/starting/with/slash', true);
    }

    public function testResolveRelative(): void
    {
        $path = UnixPath::parse('/i/am/test/unix/path');

        $rp = new RelativePath('/i/am/test/relative/path');
        self::assertEquals(
            '/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('i/am/test/relative/path');
        self::assertEquals(
            '/i/am/test/unix/path/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('../../i/am/test/relative/path');
        self::assertEquals(
            '/i/am/test/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('../../../../../../../../i/am/test/relative/path');
        self::assertEquals(
            '/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('..');
        self::assertEquals(
            '/i/am/test/unix',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('.');
        self::assertEquals(
            '/i/am/test/unix/path',
            $path->resolveRelative($rp)->toString()
        );
    }

    public function testResolveRelativeStrict(): void
    {
        $path = UnixPath::parse('/i/am/test/unix/path');

        $rp = new RelativePath('/i/am/test/relative/path');
        self::assertEquals(
            '/i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString()
        );

        $rp = new RelativePath('i/am/test/relative/path');
        self::assertEquals(
            '/i/am/test/unix/path/i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString()
        );

        $rp = new RelativePath('../../i/am/test/relative/path');
        self::assertEquals(
            '/i/am/test/i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString()
        );

        $rp = new RelativePath('..');
        self::assertEquals(
            '/i/am/test/unix',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('.');
        self::assertEquals(
            '/i/am/test/unix/path',
            $path->resolveRelative($rp)->toString()
        );
    }

    public function testResolveRelativeStrictInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Relative path went beyond root');

        $path = UnixPath::parse('/i/am/test/unix/path');
        $rp4 = new RelativePath('../../../../../../../../i/am/test/relative/path');
        $path->resolveRelative($rp4, true);
    }
}
