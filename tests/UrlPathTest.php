<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\RelativePath;
use Arokettu\Path\UnixPath;
use Arokettu\Path\UrlPath;
use PHPUnit\Framework\TestCase;

class UrlPathTest extends TestCase
{
    public function testCreate(): void
    {
        $path = UrlPath::parse('https://example.com/i////./am/test/./skipme/../url');
        self::assertEquals('https://example.com/i/am/test/url', $path->toString());
        self::assertEquals('https://example.com/', $path->getPrefix());

        $path = UrlPath::parse('https://example.com/../../i/am/test/url');
        self::assertEquals('https://example.com/i/am/test/url', $path->toString());
        self::assertEquals('https://example.com/', $path->getPrefix());

        $path = UrlPath::parse('https://user:pass@example.com/i/am/test/url');
        self::assertEquals('https://user:pass@example.com/i/am/test/url', $path->toString());
        self::assertEquals('https://user:pass@example.com/', $path->getPrefix());

        // trailing slash
        $path = UrlPath::parse('https://example.com/i/./am/test/./skipme/../url/');
        self::assertEquals('https://example.com/i/am/test/url/', $path->toString());
        self::assertEquals('https://example.com/', $path->getPrefix());

        // no trailing slash after host: normalize to slash
        $path = UrlPath::parse('https://example.com');
        self::assertEquals('https://example.com/', $path->toString());
        self::assertEquals('https://example.com/', $path->getPrefix());

        // trailing slash after host
        $path = UrlPath::parse('https://example.com/');
        self::assertEquals('https://example.com/', $path->toString());
        self::assertEquals('https://example.com/', $path->getPrefix());
    }

    public function testCreateStrict(): void
    {
        $path = UrlPath::parse('https://example.com/i/./am/test/./skipme/../url', true);
        self::assertEquals('https://example.com/i/am/test/url', $path->toString());
        self::assertEquals('https://example.com/', $path->getPrefix());

        $path = UrlPath::parse('https://example.com/i/./am/test/./skipme/../url/', true);
        self::assertEquals('https://example.com/i/am/test/url/', $path->toString());
        self::assertEquals('https://example.com/', $path->getPrefix());

        $path = UrlPath::parse('https://user:pass@example.com/i/am/test/url', true);
        self::assertEquals('https://user:pass@example.com/i/am/test/url', $path->toString());
        self::assertEquals('https://user:pass@example.com/', $path->getPrefix());
    }

    public function testCreateInvalid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Path went beyond root');

        $path = UrlPath::parse('https://example.com/../../i/am/test/url', true);
        self::assertEquals('https://example.com/i/am/test/url', $path->toString());
        self::assertEquals('https://example.com/', $path->getPrefix());
    }

    public function testResolveRelative(): void
    {
        $path = UrlPath::parse('https://example.com/i/am/test/url');

        $rp = new RelativePath('/i/am/test/relative/path');
        self::assertEquals(
            'https://example.com/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('i/am/test/relative/path');
        self::assertEquals(
            'https://example.com/i/am/test/url/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('../../i/am/test/relative/path');
        self::assertEquals(
            'https://example.com/i/am/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('../../../../../../../../i/am/test/relative/path');
        self::assertEquals(
            'https://example.com/i/am/test/relative/path',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('..');
        self::assertEquals(
            'https://example.com/i/am/test',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('.');
        self::assertEquals(
            'https://example.com/i/am/test/url',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('../');
        self::assertEquals(
            'https://example.com/i/am/test/',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('./');
        self::assertEquals(
            'https://example.com/i/am/test/url/',
            $path->resolveRelative($rp)->toString()
        );
    }

    public function testResolveRelativeStrict(): void
    {
        $path = UrlPath::parse('https://example.com/i/am/test/url/');

        $rp = new RelativePath('/i/am/test/relative/path');
        self::assertEquals(
            'https://example.com/i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString()
        );

        $rp = new RelativePath('i/am/test/relative/path');
        self::assertEquals(
            'https://example.com/i/am/test/url/i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString()
        );

        $rp = new RelativePath('../../i/am/test/relative/path');
        self::assertEquals(
            'https://example.com/i/am/i/am/test/relative/path',
            $path->resolveRelative($rp, true)->toString()
        );

        $rp = new RelativePath('..');
        self::assertEquals(
            'https://example.com/i/am/test',
            $path->resolveRelative($rp)->toString()
        );

        $rp = new RelativePath('.');
        self::assertEquals(
            'https://example.com/i/am/test/url',
            $path->resolveRelative($rp)->toString()
        );
    }

    public function testResolveRelativeStrictInvalid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Relative path went beyond root');

        $path = UrlPath::parse('https://example.com/i/am/test/url/');
        $rp = new RelativePath('../../../../../../../../i/am/test/relative/path');
        $path->resolveRelative($rp, true);
    }

    public function testMakeRelative(): void
    {
        $paths = [
            UrlPath::parse('udp://example.net/i/am/test/url'),
            UrlPath::parse('udp://example.net/i/am/another/url/test'),
            UrlPath::parse('udp://example.net/i/am/'),
            UrlPath::parse('udp://example.net/i/am/test/url'), // different instance, same path
            UrlPath::parse('udp://example.net/i/am/'), // different instance, same path
        ];

        $matrix = [
            [
                '.',
                '../../another/url/test',
                '../../',
                '.',
                '../../',
            ],
            [
                '../../../test/url',
                '.',
                '../../../',
                '../../../test/url',
                '../../../',
            ],
            [
                'test/url',
                'another/url/test',
                './',
                'test/url',
                './',
            ],
            [
                '.',
                '../../another/url/test',
                '../../',
                '.',
                '../../',
            ],
            [
                'test/url',
                'another/url/test',
                './',
                'test/url',
                './',
            ],
        ];

        foreach ($paths as $bpi => $bp) {
            foreach ($paths as $tpi => $tp) {
                $result = $matrix[$bpi][$tpi];

                self::assertEquals(
                    $result,
                    $bp->makeRelative($tp)->toString(),
                    sprintf('Unexpected relative of base %s and target %s', \strval($bp), \strval($tp)),
                );
            }
        }
    }

    public function testMakeRelativeWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only make relative path from paths of same type and same prefix');

        UrlPath::parse('https://example.com/')->makeRelative(UnixPath::parse('/i/am/test/unix/path'));
    }
}
