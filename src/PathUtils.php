<?php

declare(strict_types=1);

namespace Arokettu\Path;

final class PathUtils
{
    /**
     * @param string|PathInterface $basePath
     * @param string|PathInterface $relativePath
     * @return string
     */
    public static function resolveRelativePath($basePath, $relativePath): string
    {
        if (\is_string($basePath)) {
            $basePath = PathFactory::parse($basePath);
        }

        if (\is_string($relativePath)) {
            $relativePath = PathFactory::parse($relativePath);
        }

        if (!($basePath instanceof PathInterface)) {
            throw new \InvalidArgumentException('basePath must be a string or an instance of PathInterface');
        }

        if ($relativePath instanceof RelativePathInterface) {
            return $basePath->resolveRelative($relativePath)->toString();
        } elseif ($relativePath instanceof AbsolutePathInterface) {
            return $relativePath->toString();
        }

        throw new \InvalidArgumentException('relativePath must be a string or an instance of PathInterface');
    }

    /**
     * @param string|AbsolutePathInterface $basePath
     * @param string|AbsolutePathInterface $targetPath
     * @return string
     */
    public static function makeRelativePath($basePath, $targetPath): string
    {
        if (\is_string($basePath)) {
            $basePath = PathFactory::parse($basePath);
        }

        if (\is_string($targetPath)) {
            $targetPath = PathFactory::parse($targetPath);
        }

        if (!($basePath instanceof AbsolutePathInterface) || !$basePath->isAbsolute()) {
            throw new \InvalidArgumentException(
                'basePath must be a string containing absolute path or an instance of AbsolutePathInterface'
            );
        }

        if (!($targetPath instanceof AbsolutePathInterface) || !$targetPath->isAbsolute()) {
            throw new \InvalidArgumentException(
                'targetPath must be a string containing absolute path or an instance of AbsolutePathInterface'
            );
        }

        return $basePath->makeRelative($targetPath)->toString();
    }
}
