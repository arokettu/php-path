<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\RelativePath;
use Arokettu\Path\StreamPath;
use Arokettu\Path\UnixPath;
use PHPUnit\Framework\TestCase;

final class StreamPathTest extends TestCase
{
    public function testCreate(): void
    {
        $path = StreamPath::parse('vfs://i/./am/./skipme/./.././test/./unix/path');
        self::assertEquals('vfs://i/am/test/unix/path', $path->toString());

        $path = StreamPath::parse('vfs://invalid/level/of/nesting/../../../../../../../../../../i/am/test/unix/path');
        self::assertEquals('vfs://i/am/test/unix/path', $path->toString());

        $path = StreamPath::parse('vfs://i/./am/./skipme/./.././test/./unix/path', true);
        self::assertEquals('vfs://i/am/test/unix/path', $path->toString());

        // root path
        $path = StreamPath::parse('vfs://', true);
        self::assertEquals('vfs://', $path->toString());
    }

    public function testCreateStrict(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Path went beyond root');

        StreamPath::parse('vfs://invalid/level/of/nesting/../../../../../../../../../../i/am/test/unix/path', true);
    }

    public function testCreateInvalid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The path does not appear to be a PHP stream path');

        StreamPath::parse('not/starting/with/scheme', true);
    }

    public function testResolveRelative(): void
    {
        $path = StreamPath::parse('vfs://i/am/test/unix/path');

        $rp = new RelativePath('/i/am/test/relative/path');
        self::assertEquals(
            'vfs://i/am/test/relative/path',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = new RelativePath('i/am/test/relative/path');
        self::assertEquals(
            'vfs://i/am/test/unix/path/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = new RelativePath('../../i/am/test/relative/path');
        self::assertEquals(
            'vfs://i/am/test/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = new RelativePath('../../../../../../../../i/am/test/relative/path');
        self::assertEquals(
            'vfs://i/am/test/relative/path',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = new RelativePath('..');
        self::assertEquals(
            'vfs://i/am/test/unix',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = new RelativePath('.');
        self::assertEquals(
            'vfs://i/am/test/unix/path',
            $path->resolveRelative($rp)->toString(),
        );
    }

    public function testResolveRelativeStrict(): void
    {
        $path = StreamPath::parse('vfs://i/am/test/unix/path');

        $rp = new RelativePath('/i/am/test/relative/path');
        self::assertEquals(
            'vfs://i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString(),
        );

        $rp = new RelativePath('i/am/test/relative/path');
        self::assertEquals(
            'vfs://i/am/test/unix/path/i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString(),
        );

        $rp = new RelativePath('../../i/am/test/relative/path');
        self::assertEquals(
            'vfs://i/am/test/i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString(),
        );

        $rp = new RelativePath('..');
        self::assertEquals(
            'vfs://i/am/test/unix',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = new RelativePath('.');
        self::assertEquals(
            'vfs://i/am/test/unix/path',
            $path->resolveRelative($rp)->toString(),
        );
    }

    public function testResolveRelativeStrictInvalid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Relative path went beyond root');

        $path = StreamPath::parse('vfs://i/am/test/unix/path');
        $rp4 = new RelativePath('../../../../../../../../i/am/test/relative/path');
        $path->resolveRelative($rp4, true);
    }

    public function testMakeRelative(): void
    {
        $paths = [
            StreamPath::parse('vfs://i/am/test/unix/path'),
            StreamPath::parse('vfs://i/am/another/unix/test/path'),
            StreamPath::parse('vfs://i/am'),
            StreamPath::parse('vfs://i/am/test/unix/path'), // different instance, same path
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

                self::assertEquals($result, $bp->makeRelative($tp)->toString());
            }
        }
    }

    public function testMakeRelativeTrailingSlash(): void
    {
        $paths = [
            new StreamPath('vfs://path/path1'),
            new StreamPath('vfs://path/path1/'),
            new StreamPath('vfs://path/path2'),
            new StreamPath('vfs://path/path2/'),
        ];

        $matrix = [
            [
                '.',
                './',
                '../path2',
                '../path2/',
            ],
            [
                '.',
                './',
                '../path2',
                '../path2/',
            ],
            [
                '../path1',
                '../path1/',
                '.',
                './',
            ],
            [
                '../path1',
                '../path1/',
                '.',
                './',
            ],
        ];

        foreach ($paths as $bpi => $bp) {
            foreach ($paths as $tpi => $tp) {
                $result = $matrix[$bpi][$tpi];

                self::assertEquals(
                    $result,
                    $bp->makeRelative($tp)->toString(),
                    \sprintf('Unexpected relative of base %s and target %s', \strval($bp), \strval($tp)),
                );
            }
        }
    }

    public function testMakeRelativeRoot(): void
    {
        $paths = [
            new StreamPath('vfs://'),
            new StreamPath('vfs://'), // same path, different instance
            new StreamPath('vfs://path'),
            new StreamPath('vfs://path/'),
        ];

        $matrix = [
            [
                '.',
                '.',
                'path',
                'path/',
            ],
            [
                '.',
                '.',
                'path',
                'path/',
            ],
            [
                '..',
                '..',
                '.',
                './',
            ],
            [
                '..',
                '..',
                '.',
                './',
            ],
        ];

        foreach ($paths as $bpi => $bp) {
            foreach ($paths as $tpi => $tp) {
                $result = $matrix[$bpi][$tpi];

                self::assertEquals(
                    $result,
                    $bp->makeRelative($tp)->toString(),
                    \sprintf('Unexpected relative of base %s and target %s', \strval($bp), \strval($tp)),
                );
            }
        }
    }

    public function testMakeRelativeWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only make relative path from paths of same type and same prefix');

        StreamPath::parse('vfs://i/am/test/unix/path')->makeRelative(UnixPath::parse('/i/am/test/unix/path'));
    }

    public function testFlags(): void
    {
        $path = new StreamPath('vfs://i/test');

        self::assertTrue($path->isAbsolute());
        self::assertFalse($path->isRelative());
    }

    public function testSerialize(): void
    {
        $path = new StreamPath('vfs://i/test');

        self::assertEquals($path, unserialize(serialize($path)));
    }
}
