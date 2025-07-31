<?php

declare(strict_types=1);

namespace Arokettu\Path;

use Error;

final class PathUtils
{
    public static function resolveRelativePath(
        string|PathInterface $basePath,
        string|PathInterface $relativePath,
    ): string {
        if (\is_string($basePath)) {
            $basePath = PathFactory::parse($basePath);
        }

        if (\is_string($relativePath)) {
            $relativePath = PathFactory::parse($relativePath);
        }

        if ($relativePath instanceof RelativePathInterface) {
            return $basePath->resolveRelative($relativePath)->toString();
        } elseif ($relativePath instanceof AbsolutePathInterface) {
            return $relativePath->toString();
        }

        throw new Error('PathInterface object must be either AbsolutePathInterface or RelativePathInterface');
    }

    public static function makeRelativePath(
        string|AbsolutePathInterface $basePath,
        string|AbsolutePathInterface $targetPath,
    ): string {
        if (\is_string($basePath)) {
            $basePath = PathFactory::parse($basePath);
        }

        if (\is_string($targetPath)) {
            $targetPath = PathFactory::parse($targetPath);
        }

        return $basePath->makeRelative($targetPath)->toString();
    }
}
