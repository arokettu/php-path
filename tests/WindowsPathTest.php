<?php

declare(strict_types=1);

namespace Arokettu\Path\Tests;

use Arokettu\Path\RelativePath;
use Arokettu\Path\UnixPath;
use Arokettu\Path\WindowsPath;
use PHPUnit\Framework\TestCase;

final class WindowsPathTest extends TestCase
{
    public function testCreate(): void
    {
        // simple dos path
        $path = WindowsPath::parse('C:\\I\\Am\\Windows\\Path');
        self::assertEquals('C:\\I\\Am\\Windows\\Path', $path->toString());
        self::assertEquals('C:\\', $path->getPrefix());
        self::assertEquals(['I', 'Am', 'Windows', 'Path'], $path->getComponents());

        // dos path to be normalized
        $path = WindowsPath::parse('z:/home/arokettu/wine/./path\\Games\\Something/../../Tools/file.exe');
        self::assertEquals('Z:\\home\\arokettu\\wine\\path\\Tools\\file.exe', $path->toString());
        self::assertEquals('Z:\\', $path->getPrefix());
        self::assertEquals(['home', 'arokettu', 'wine', 'path', 'Tools', 'file.exe'], $path->getComponents());

        // non strictly valid dos path
        $path = WindowsPath::parse('C:\\..\\..\\I\\Am\\Windows\\Path');
        self::assertEquals('C:\\I\\Am\\Windows\\Path', $path->toString());
        self::assertEquals('C:\\', $path->getPrefix());
        self::assertEquals(['I', 'Am', 'Windows', 'Path'], $path->getComponents());

        // local unc path (drive letter)
        $path = WindowsPath::parse('\\\\.\\c:\\windows\\win.ini');
        self::assertEquals('\\\\.\\C:\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\.\\C:\\', $path->getPrefix());
        self::assertEquals(['windows', 'win.ini'], $path->getComponents());

        // local unc path (guid)
        $path = WindowsPath::parse('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\windows\\win.ini');
        self::assertEquals('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\', $path->getPrefix());
        self::assertEquals(['windows', 'win.ini'], $path->getComponents());

        // remote share
        $path = WindowsPath::parse('\\\\MYPC\\c$\\windows\\win.ini');
        self::assertEquals('\\\\MYPC\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\MYPC\\', $path->getPrefix());
        self::assertEquals(['c$', 'windows', 'win.ini'], $path->getComponents());

        // remote share single char
        $path = WindowsPath::parse('\\\\M\\c$\\windows\\win.ini');
        self::assertEquals('\\\\M\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\M\\', $path->getPrefix());
        self::assertEquals(['c$', 'windows', 'win.ini'], $path->getComponents());

        // remote share starts with dot
        $path = WindowsPath::parse('\\\\.MYPC\\c$\\windows\\win.ini');
        self::assertEquals('\\\\.MYPC\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\.MYPC\\', $path->getPrefix());
        self::assertEquals(['c$', 'windows', 'win.ini'], $path->getComponents());

        // root path
        $path = WindowsPath::parse('X:\\');
        self::assertEquals('X:\\', $path->toString());
        self::assertEquals('X:\\', $path->getPrefix());
        self::assertEquals([], $path->getComponents());
    }

    public function testCreateInvalidNotAWinPath(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Unrecognized Windows path');

        WindowsPath::parse('/home/arokettu');
    }

    public function testCreateInvalidRelativeWithALetter(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Unrecognized Windows path');

        // technically valid but usually useless
        WindowsPath::parse('c:windows\win.ini');
    }

    public function testCreateInvalidRoot(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Unrecognized Windows path');

        WindowsPath::parse('X:');
    }

    public function testCreateInvalidUNCWithSlash(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Slashes are not allowed in UNC paths');

        // technically valid but usually useless
        WindowsPath::parse('\\\\MYPC\\c$/Windows');
    }

    public function testCreateInvalidUNCWithDots(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('. and .. are not allowed in UNC paths');

        // technically valid but usually useless
        WindowsPath::parse('\\\\MYPC\\c$\\Windows\\..\\Users\\');
    }

    public function testCreateStrict(): void
    {
        // simple dos path
        $path = WindowsPath::parse('C:\\I\\Am\\Windows\\Path', true);
        self::assertEquals('C:\\I\\Am\\Windows\\Path', $path->toString());
        self::assertEquals('C:\\', $path->getPrefix());

        // dos path to be normalized
        $path = WindowsPath::parse('z:/home/arokettu/wine/./path\\Games\\Something/../../Tools/file.exe', true);
        self::assertEquals('Z:\\home\\arokettu\\wine\\path\\Tools\\file.exe', $path->toString());
        self::assertEquals('Z:\\', $path->getPrefix());

        // local unc path (drive letter)
        $path = WindowsPath::parse('\\\\.\\c:\\windows\\win.ini', true);
        self::assertEquals('\\\\.\\C:\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\.\\C:\\', $path->getPrefix());

        // local unc path (guid)
        $path = WindowsPath::parse('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\windows\\win.ini', true);
        self::assertEquals('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\?\\Volume{D4AF2203-A75B-4CB1-9B93-AE78EB9A50A5}\\', $path->getPrefix());

        // remote share
        $path = WindowsPath::parse('\\\\MYPC\\c$\\windows\\win.ini', true);
        self::assertEquals('\\\\MYPC\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\MYPC\\', $path->getPrefix());

        // remote share single char
        $path = WindowsPath::parse('\\\\M\\c$\\windows\\win.ini', true);
        self::assertEquals('\\\\M\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\M\\', $path->getPrefix());

        // remote share starts with dot
        $path = WindowsPath::parse('\\\\.MYPC\\c$\\windows\\win.ini', true);
        self::assertEquals('\\\\.MYPC\\c$\\windows\\win.ini', $path->toString());
        self::assertEquals('\\\\.MYPC\\', $path->getPrefix());
    }

    public function testCreateStrictInvalid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Path went beyond root');

        $path = WindowsPath::parse('C:\\..\\..\\I\\Am\\Windows\\Path', true);
        self::assertEquals('C:\\I\\Am\\Windows\\Path', $path->toString());
        self::assertEquals('C:\\', $path->getPrefix());
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
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Relative path went beyond root');

        $path = WindowsPath::parse('c:\\i\\am\\test\\windows\\path');

        $rp = RelativePath::windows('..\\..\\..\\..\\..\\..\\..\\..\\i\\am\\test\\relative\\path');
        self::assertEquals(
            'C:\\i\\am\\test\\relative\\path',
            $path->resolveRelative($rp, true)->toString(),
        );
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only make relative path from paths of same type and same prefix');

        WindowsPath::parse('C:\\Windows')->makeRelative(UnixPath::parse('/i/am/test/unix/path'));
    }

    public function testMakeRelativeWrongPrefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
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
