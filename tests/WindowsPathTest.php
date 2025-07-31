<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\Exceptions\PathWentBeyondRootException;
use Arokettu\Path\RelativePath;
use Arokettu\Path\UnixPath;
use Arokettu\Path\WindowsPath;
use PHPUnit\Framework\TestCase;
use ValueError;

final class WindowsPathTest extends TestCase
{
    public function testCreate(): void
    {
        // simple dos path
        $path = WindowsPath::parse('C:\\I\\Am\\Windows\\Path');
        self::assertEquals('C:\\I\\Am\\Windows\\Path', $path->toString());
        self::assertEquals('C:\\', $path->prefix);
        self::assertEquals(['I', 'Am', 'Windows', 'Path'], $path->components);

        // dos path to be normalized
        $path = WindowsPath::parse('z:/home/arokettu/wine/./path\\Games\\Something/../../Tools/file.exe');
        self::assertEquals('Z:\\home\\arokettu\\wine\\path\\Tools\\file.exe', $path->toString());
        self::assertEquals('Z:\\', $path->prefix);
        self::assertEquals(['home', 'arokettu', 'wine', 'path', 'Tools', 'file.exe'], $path->components);

        // non strictly valid dos path
        $path = WindowsPath::parse('C:\\..\\..\\I\\Am\\Windows\\Path');
        self::assertEquals('C:\\I\\Am\\Windows\\Path', $path->toString());
        self::assertEquals('C:\\', $path->prefix);
        self::assertEquals(['I', 'Am', 'Windows', 'Path'], $path->components);

        // local unc path (drive letter)
        $path = WindowsPath::parse('\\\\.\\c:\\windows\\win.ini');
        self::assertEquals('\\\\.\\C:\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\.\\C:\\', $path->prefix);
        self::assertEquals(['windows', 'win.ini'], $path->components);

        // local unc path (guid)
        $path = WindowsPath::parse('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\windows\\win.ini');
        self::assertEquals('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\', $path->prefix);
        self::assertEquals(['windows', 'win.ini'], $path->components);

        // remote share
        $path = WindowsPath::parse('\\\\MYPC\\c$\\windows\\win.ini');
        self::assertEquals('\\\\MYPC\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\MYPC\\', $path->prefix);
        self::assertEquals(['c$', 'windows', 'win.ini'], $path->components);

        // remote share single char
        $path = WindowsPath::parse('\\\\M\\c$\\windows\\win.ini');
        self::assertEquals('\\\\M\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\M\\', $path->prefix);
        self::assertEquals(['c$', 'windows', 'win.ini'], $path->components);

        // remote share starts with dot
        $path = WindowsPath::parse('\\\\.MYPC\\c$\\windows\\win.ini');
        self::assertEquals('\\\\.MYPC\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\.MYPC\\', $path->prefix);
        self::assertEquals(['c$', 'windows', 'win.ini'], $path->components);

        // root path
        $path = WindowsPath::parse('X:\\');
        self::assertEquals('X:\\', $path->toString());
        self::assertEquals('X:\\', $path->prefix);
        self::assertEquals([], $path->components);
    }

    public function testCreateInvalidNotAWinPath(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Unrecognized Windows path');

        WindowsPath::parse('/home/arokettu');
    }

    public function testCreateInvalidRelativeWithALetter(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Unrecognized Windows path');

        // technically valid but usually useless
        WindowsPath::parse('c:windows\win.ini');
    }

    public function testCreateInvalidRoot(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Unrecognized Windows path');

        WindowsPath::parse('X:');
    }

    public function testCreateInvalidUNCWithSlash(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Slashes are not allowed in UNC paths');

        // technically valid but usually useless
        WindowsPath::parse('\\\\MYPC\\c$/Windows');
    }

    public function testCreateInvalidUNCWithDots(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('. and .. are not allowed in UNC paths');

        // technically valid but usually useless
        WindowsPath::parse('\\\\MYPC\\c$\\Windows\\..\\Users\\');
    }

    public function testCreateStrict(): void
    {
        // simple dos path
        $path = WindowsPath::parse('C:\\I\\Am\\Windows\\Path', true);
        self::assertEquals('C:\\I\\Am\\Windows\\Path', $path->toString());
        self::assertEquals('C:\\', $path->prefix);

        // dos path to be normalized
        $path = WindowsPath::parse('z:/home/arokettu/wine/./path\\Games\\Something/../../Tools/file.exe', true);
        self::assertEquals('Z:\\home\\arokettu\\wine\\path\\Tools\\file.exe', $path->toString());
        self::assertEquals('Z:\\', $path->prefix);

        // local unc path (drive letter)
        $path = WindowsPath::parse('\\\\.\\c:\\windows\\win.ini', true);
        self::assertEquals('\\\\.\\C:\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\.\\C:\\', $path->prefix);

        // local unc path (guid)
        $path = WindowsPath::parse('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\windows\\win.ini', true);
        self::assertEquals('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\', $path->prefix);

        // remote share
        $path = WindowsPath::parse('\\\\MYPC\\c$\\windows\\win.ini', true);
        self::assertEquals('\\\\MYPC\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\MYPC\\', $path->prefix);

        // remote share single char
        $path = WindowsPath::parse('\\\\M\\c$\\windows\\win.ini', true);
        self::assertEquals('\\\\M\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\M\\', $path->prefix);

        // remote share starts with dot
        $path = WindowsPath::parse('\\\\.MYPC\\c$\\windows\\win.ini', true);
        self::assertEquals('\\\\.MYPC\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\.MYPC\\', $path->prefix);
    }

    public function testCreateStrictInvalid(): void
    {
        $this->expectException(PathWentBeyondRootException::class);
        $this->expectExceptionMessage('Path went beyond root');

        WindowsPath::parse('C:\\..\\..\\I\\Am\\Windows\\Path', true);
    }

    public function testResolveRelative(): void
    {
        $path = WindowsPath::parse('c:\\i\\am\\test\\windows\\path');

        $rp = RelativePath::windows('\\i\\am\\test\\relative\\path');
        self::assertEquals(
            'C:\\i\\am\\test\\relative\\path',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = RelativePath::windows('i\\am\\test\\relative\\path');
        self::assertEquals(
            'C:\\i\\am\\test\\windows\\path\\i\\am\\test\\relative\\path',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = RelativePath::windows('..\\..\\i\\am\\test\\relative\\path');
        self::assertEquals(
            'C:\\i\\am\\test\\i\\am\\test\\relative\\path',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = RelativePath::windows('..\\..\\..\\..\\..\\..\\..\\..\\i\\am\\test\\relative\\path');
        self::assertEquals(
            'C:\\i\\am\\test\\relative\\path',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = RelativePath::windows('..');
        self::assertEquals(
            'C:\\i\\am\\test\\windows',
            $path->resolveRelative($rp)->toString(),
        );

        $rp = RelativePath::windows('.');
        self::assertEquals(
            'C:\\i\\am\\test\\windows\\path',
            $path->resolveRelative($rp)->toString(),
        );
    }

    public function testResolveRelativeStrict(): void
    {
        $path = WindowsPath::parse('c:\\i\\am\\test\\windows\\path');

        $rp = RelativePath::windows('\\i\\am\\test\\relative\\path');
        self::assertEquals(
            'C:\\i\\am\\test\\relative\\path',
            $path->resolveRelative($rp, true)->toString(),
        );

        $rp = RelativePath::windows('i\\am\\test\\relative\\path');
        self::assertEquals(
            'C:\\i\\am\\test\\windows\\path\\i\\am\\test\\relative\\path',
            $path->resolveRelative($rp, true)->toString(),
        );

        $rp = RelativePath::windows('..\\..\\i\\am\\test\\relative\\path');
        self::assertEquals(
            'C:\\i\\am\\test\\i\\am\\test\\relative\\path',
            $path->resolveRelative($rp, true)->toString(),
        );

        $rp = RelativePath::windows('..');
        self::assertEquals(
            'C:\\i\\am\\test\\windows',
            $path->resolveRelative($rp, true)->toString(),
        );

        $rp = RelativePath::windows('.');
        self::assertEquals(
            'C:\\i\\am\\test\\windows\\path',
            $path->resolveRelative($rp, true)->toString(),
        );
    }

    public function testResolveRelativeStrictInvalid(): void
    {
        $path = WindowsPath::parse('c:\\i\\am\\test\\windows\\path');
        $rp = RelativePath::windows('..\\..\\..\\..\\..\\..\\..\\..\\i\\am\\test\\relative\\path');

        $this->expectException(PathWentBeyondRootException::class);
        $this->expectExceptionMessage('Relative path went beyond root');

        $path->resolveRelative($rp, true)->toString();
    }

    public function testMakeRelative(): void
    {
        $paths = [
            WindowsPath::parse('C:\\i\\am\\test\\windows\\path'),
            WindowsPath::parse('C:\\i\\aM\\anOther\\Windows\\TEST\\path'),
            WindowsPath::parse('C:\\i\\Am'),
            WindowsPath::parse('C:\\I\\AM\\TEST\\WINDOWS\\PATH'), // different case, same path
        ];

        // simple latin case insensitive
        $equalFunction = static fn ($a, $b) => strtoupper($a) === strtoupper($b);

        $matrix = [
            [
                '.',
                '..\\..\\..\\anOther\\Windows\\TEST\\path',
                '..\\..\\..',
                '.',
            ],
            [
                '..\\..\\..\\..\\test\\windows\\path',
                '.',
                '..\\..\\..\\..',
                '..\\..\\..\\..\\TEST\\WINDOWS\\PATH',
            ],
            [
                'test\\windows\\path',
                'anOther\\Windows\\TEST\\path',
                '.',
                'TEST\\WINDOWS\\PATH',
            ],
            [
                '.',
                '..\\..\\..\\anOther\\Windows\\TEST\\path',
                '..\\..\\..',
                '.',
            ],
        ];

        foreach ($paths as $bpi => $bp) {
            foreach ($paths as $tpi => $tp) {
                $result = $matrix[$bpi][$tpi];

                self::assertEquals($result, $bp->makeRelative($tp, $equalFunction)->toString());
            }
        }
    }

    public function testMakeRelativeWrongType(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('You can only make relative path from paths of same type and same prefix');

        WindowsPath::parse('C:\\Windows')->makeRelative(UnixPath::parse('/i/am/test/unix/path'));
    }

    public function testMakeRelativeWrongPrefix(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('You can only make relative path from paths of same type and same prefix');

        WindowsPath::parse('C:\\Windows')->makeRelative(WindowsPath::parse('D:\\Windows'));
    }

    public function testFlags(): void
    {
        $path = new WindowsPath('C:\\Test');

        self::assertTrue($path->isAbsolute());
        self::assertFalse($path->isRelative());
    }

    public function testSerialize(): void
    {
        $path = new WindowsPath('C:\\Test');

        self::assertEquals($path, unserialize(serialize($path)));
    }

    public function testDebugInfo(): void
    {
        $path = new WindowsPath('C:\\Test');

        self::assertEquals(['path' => 'C:\\Test'], $path->__debugInfo());
    }
}
