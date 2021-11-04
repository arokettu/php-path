<?php

declare(strict_types=1);

namespace Arokettu\Path;

final class PathFactory
{
    public static function parse(string $path, array $urlSchemes = [], array $streamSchemes = []): PathInterface
    {
        if ($path[0] === '/') {
            return UnixPath::parse($path);
        }

        if (preg_match('@^[a-zA-Z]:[\\\\/]@', $path) || str_starts_with($path, '\\\\')) {
            return new WindowsPath($path);
        }

        if (preg_match('@^([-.+a-zA-Z0-9]+)://@', $path, $matches)) {
            return self::parseUrlLike($path, $matches[1], $urlSchemes, $streamSchemes);
        }

        return DIRECTORY_SEPARATOR === '\\' ? RelativePath::windows($path) : RelativePath::unix($path);
    }

    private static function parseUrlLike(
        string $path,
        string $scheme,
        array $urlSchemes = [],
        array $streamSchemes = []
    ): PathInterface {
        if ($urlSchemes === [] && $streamSchemes === []) {
            return UrlPath::parse($path);
        }

        if (\in_array($scheme, $urlSchemes)) {
            return UrlPath::parse($path);
        }

        if (\in_array($scheme, $streamSchemes)) {
            return StreamPath::parse($path);
        }

        throw new \InvalidArgumentException('Unknown scheme: ' . $scheme);
    }
}
