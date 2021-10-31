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
        $relativePaths = $paths;

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
            foreach ($relativePaths as $rpi => $rp) {
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
        $relativePaths = $paths;

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
            foreach ($relativePaths as $rpi => $rp) {
                $matrixResult = $matrix[$pi][$rpi];

                if ($matrixResult === null) {
                    continue;
                }

                self::assertEquals($matrixResult, $p->resolveRelative($rp, true)->toString());
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

        $rp1 = new class implements RelativePathInterface {
            public function __toString(): string {
                return '';
            }

            public function getPrefix(): string
            {
                return '';
            }

            public function getComponents(): array
            {
                return explode('/', '../../i/am/test/relative/path');
            }

            public function toString(): string
            {
                throw new \BadMethodCallException('Irrelevant');
            }

            public function resolveRelative(RelativePathInterface $path, bool $strict = false): PathInterface
            {
                throw new \BadMethodCallException('Irrelevant');
            }

            public function isRoot(): bool
            {
                return false;
            }
        };

        $rp2 = new class implements RelativePathInterface {
            public function __toString(): string
            {
                return '';
            }

            public function getPrefix(): string
            {
                return '';
            }

            public function getComponents(): array
            {
                return explode('/', 'i/am/test/relative/path');
            }

            public function toString(): string
            {
                throw new \BadMethodCallException('Irrelevant');
            }

            public function resolveRelative(RelativePathInterface $path, bool $strict = false): PathInterface
            {
                throw new \BadMethodCallException('Irrelevant');
            }

            public function isRoot(): bool
            {
                return true;
            }
        };

        self::assertEquals('i/am/test/i/am/test/relative/path', $p->resolveRelative($rp1)->toString());
        self::assertEquals('/i/am/test/relative/path', $p->resolveRelative($rp2)->toString());
    }
}
