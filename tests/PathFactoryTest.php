<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\PathFactory;
use Arokettu\Path\RelativePath;
use Arokettu\Path\StreamPath;
use Arokettu\Path\UnixPath;
use Arokettu\Path\UrlPath;
use Arokettu\Path\WindowsPath;
use PHPUnit\Framework\TestCase;

class PathFactoryTest extends TestCase
{
    public function testValid(): void
    {
        self::assertInstanceOf(UnixPath::class, PathFactory::parse('/unix/path'));
        self::assertInstanceOf(WindowsPath::class, PathFactory::parse('C:/unixlike/path'));
        self::assertInstanceOf(WindowsPath::class, PathFactory::parse('c:\win\path'));
        self::assertInstanceOf(WindowsPath::class, PathFactory::parse('\\\\unc\path'));
        self::assertInstanceOf(RelativePath::class, PathFactory::parse('../test/path'));
        self::assertInstanceOf(RelativePath::class, PathFactory::parse('./test/path'));
        self::assertInstanceOf(RelativePath::class, PathFactory::parse('test/path'));
    }

    public function testValidUrls(): void
    {
        self::assertInstanceOf(UrlPath::class, PathFactory::parse('https://example.com/test/test'));
        self::assertInstanceOf(UrlPath::class, PathFactory::parse('vfs://test/test'));

        $urlSchemes = ['http', 'https', 'ftp'];
        $streamSchemes = ['vfs', 'php'];
        self::assertInstanceOf(
            UrlPath::class,
            PathFactory::parse('https://example.com/test/test', $urlSchemes, $streamSchemes)
        );
        self::assertInstanceOf(
            StreamPath::class,
            PathFactory::parse('vfs://test/test', $urlSchemes, $streamSchemes)
        );
    }

    public function testUnknownScheme(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown scheme: unk');

        $urlSchemes = ['http', 'https', 'ftp'];
        $streamSchemes = ['vfs', 'php'];

        PathFactory::parse('unk://test/test', $urlSchemes, $streamSchemes);
    }
}
