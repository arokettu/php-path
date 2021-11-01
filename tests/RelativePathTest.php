<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\PathInterface;
use Arokettu\Path\RelativePath;
use Arokettu\Path\RelativePathInterface;
use PHPUnit\Framework\TestCase;

class RelativePathTest extends TestCase
{
    public function testCreate(): void
    {
        // 'absolute' relative path
        $path = RelativePath::unix('/i/am/./skipme/../test/./relative/path');
        self::assertEquals('/i/am/test/relative/path', $path->toString());

        // relative path from the current directory
        $path = RelativePath::unix('./i/am/./skipme/../test/./relative/path');
        self::assertEquals('i/am/test/relative/path', $path->toString());

        // relative path from the parent directory
        $path = RelativePath::unix('.././../i/am/./skipme/../test/./relative/path');
        self::assertEquals('../../i/am/test/relative/path', $path->toString());

        $path = RelativePath::unix('..');
        self::assertEquals('..', $path->toString());

        $path = RelativePath::unix('.');
        self::assertEquals('.', $path->toString());

        // test empty
        $path = RelativePath::unix('');
        self::assertEquals('.', $path->toString());

        // preserve trailing slash
        $path = RelativePath::unix('./');
        self::assertEquals('./', $path->toString());

        $path = RelativePath::unix('../');
        self::assertEquals('../', $path->toString());

        $path = RelativePath::unix('path/');
        self::assertEquals('path/', $path->toString());

        // root path
        $path = RelativePath::unix('/');
        self::assertEquals('/', $path->toString());
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
            new RelativePath('/i/am/test/relative/path'),
            new RelativePath('i/am/test/relative/path'),
            new RelativePath('../../i/am/test/relative/path'),
            new RelativePath('../../../../../../../../i/am/test/relative/path'),
            new RelativePath('..'),
            new RelativePath('.'),
        ];

        $matrix = [
            [
                '/i/am/test/relative/path',
                '/i/am/test/relative/path/i/am/test/relative/path',
                '/i/am/test/i/am/test/relative/path',
                '/i/am/test/relative/path',
                '/i/am/test/relative',
                '/i/am/test/relative/path',
            ],
            [
                '/i/am/test/relative/path',
                'i/am/test/relative/path/i/am/test/relative/path',
                'i/am/test/i/am/test/relative/path',
                '../../../i/am/test/relative/path',
                'i/am/test/relative',
                'i/am/test/relative/path',
            ],
            [
                '/i/am/test/relative/path',
                '../../i/am/test/relative/path/i/am/test/relative/path',
                '../../i/am/test/i/am/test/relative/path',
                '../../../../../i/am/test/relative/path',
                '../../i/am/test/relative',
                '../../i/am/test/relative/path'
            ],
            [
                '/i/am/test/relative/path',
                '../../../../../../../../i/am/test/relative/path/i/am/test/relative/path',
                '../../../../../../../../i/am/test/i/am/test/relative/path',
                '../../../../../../../../../../../i/am/test/relative/path',
                '../../../../../../../../i/am/test/relative',
                '../../../../../../../../i/am/test/relative/path',
            ],
            [
                '/i/am/test/relative/path',
                '../i/am/test/relative/path',
                '../../../i/am/test/relative/path',
                '../../../../../../../../../i/am/test/relative/path',
                '../..',
                '..',
            ],
            [
                '/i/am/test/relative/path',
                'i/am/test/relative/path',
                '../../i/am/test/relative/path',
                '../../../../../../../../i/am/test/relative/path',
                '..',
                '.',
            ],
        ];

        foreach ($paths as $pi => $p) {
            foreach ($paths as $rpi => $rp) {
                $matrixResult = $matrix[$pi][$rpi];

                self::assertEquals($matrixResult, $p->resolveRelative($rp)->toString());
            }
        }
    }

    public function testResolveRelativeStrict(): void
    {
        $paths = [
            new RelativePath('/i/am/test/relative/path'),
            new RelativePath('i/am/test/relative/path'),
            new RelativePath('../../i/am/test/relative/path'),
            new RelativePath('../../../../../../../../i/am/test/relative/path'),
        ];

        $matrix = [
            [
                '/i/am/test/relative/path',
                '/i/am/test/relative/path/i/am/test/relative/path',
                '/i/am/test/i/am/test/relative/path',
                null,
            ],
            [
                '/i/am/test/relative/path',
                'i/am/test/relative/path/i/am/test/relative/path',
                'i/am/test/i/am/test/relative/path',
                '../../../i/am/test/relative/path',
            ],
            [
                '/i/am/test/relative/path',
                '../../i/am/test/relative/path/i/am/test/relative/path',
                '../../i/am/test/i/am/test/relative/path',
                '../../../../../i/am/test/relative/path',
            ],
            [
                '/i/am/test/relative/path',
                '../../../../../../../../i/am/test/relative/path/i/am/test/relative/path',
                '../../../../../../../../i/am/test/i/am/test/relative/path',
                '../../../../../../../../../../../i/am/test/relative/path',
            ],
        ];

        foreach ($paths as $pi => $p) {
            foreach ($paths as $rpi => $rp) {
                $matrixResult = $matrix[$pi][$rpi];

                if ($matrixResult === null) {
                    continue;
                }

                self::assertEquals($matrixResult, $p->resolveRelative($rp, true)->toString());
            }
        }
    }

    public function testResolveRelativeTrailingSlash(): void
    {
        $paths = [
            new RelativePath('../path/path1'),
            new RelativePath('../path/path1/'),
            new RelativePath('../path/path2'),
            new RelativePath('../path/path2/'),
        ];

        $matrix = [
            [
                '../path/path/path1',
                '../path/path/path1/',
                '../path/path/path2',
                '../path/path/path2/',
            ],
            [
                '../path/path/path1',
                '../path/path/path1/',
                '../path/path/path2',
                '../path/path/path2/',
            ],
            [
                '../path/path/path1',
                '../path/path/path1/',
                '../path/path/path2',
                '../path/path/path2/',
            ],
            [
                '../path/path/path1',
                '../path/path/path1/',
                '../path/path/path2',
                '../path/path/path2/',
            ],
        ];

        foreach ($paths as $pi => $p) {
            foreach ($paths as $rpi => $rp) {
                $matrixResult = $matrix[$pi][$rpi];

                self::assertEquals($matrixResult, $p->resolveRelative($rp)->toString());
            }
        }
    }

    public function testResolveRelativeStrictAssertion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Relative path went beyond root');

        $p = new RelativePath('/i/am/test/relative/path');
        $rp = new RelativePath('../../../../../../../../i/am/test/relative/path');

        $p->resolveRelative($rp, true);
    }

    public function testExternalRelativeImplementations(): void
    {
        $p = new RelativePath('i/am/test/relative/path');

        $rp1 = new Classes\CustomRelativePathImplementation(
            explode('/', '../../i/am/test/relative/path'),
            false,
        );

        $rp2 = new Classes\CustomRelativePathImplementation(
            explode('/', 'i/am/test/relative/path'),
            true,
        );

        self::assertEquals('i/am/test/i/am/test/relative/path', $p->resolveRelative($rp1)->toString());
        self::assertEquals('/i/am/test/relative/path', $p->resolveRelative($rp2)->toString());
    }
}
