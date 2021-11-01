<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\RelativePath;
use Arokettu\Path\UnixPath;
use Arokettu\Path\WindowsPath;
use PHPUnit\Framework\TestCase;

class UnixPathTest extends TestCase
{
    public function testCreate(): void
    {
        $path = UnixPath::parse('/i/./am/./skipme/./.././test/./unix/path');
        self::assertEquals('/i/am/test/unix/path', $path->toString());

        $path = UnixPath::parse('/invalid/level/of/nesting/../../../../../../../../../../i/am/test/unix/path');
        self::assertEquals('/i/am/test/unix/path', $path->toString());

        $path = UnixPath::parse('/i/./am/./skipme/./.././test/./unix/path', true);
        self::assertEquals('/i/am/test/unix/path', $path->toString());

        // root path
        $path = UnixPath::parse('/', true);
        self::assertEquals('/', $path->toString());
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

    public function testMakeRelative(): void
    {
        $paths = [
            UnixPath::parse('/i/am/test/unix/path'),
            UnixPath::parse('/i/am/another/unix/test/path'),
            UnixPath::parse('/i/am'),
            UnixPath::parse('/i/am/test/unix/path'), // different instance, same path
        ];

        $matrix = [
            [
                '.',
                '../../../another/unix/test/path',
                '../../..',
                '.',
            ],
            [
                '../../../../test/unix/path',
                '.',
                '../../../..',
                '../../../../test/unix/path',
            ],
            [
                'test/unix/path',
                'another/unix/test/path',
                '.',
                'test/unix/path',
            ],
            [
                '.',
                '../../../another/unix/test/path',
                '../../..',
                '.',
            ],
        ];

        foreach ($paths as $bpi => $bp) {
            foreach ($paths as $tpi => $tp) {
                $result = $matrix[$bpi][$tpi];

                self::assertEquals($result, $bp->makeRelative($tp));
            }
        }
    }

    public function testMakeRelativeWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only make relative path from paths of same type and same prefix');

        UnixPath::parse('/i/am/test/unix/path')->makeRelative(WindowsPath::parse('C:\\Windows'));
    }
}
