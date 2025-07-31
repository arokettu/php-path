<?php

declare(strict_types=1);

namespace Arokettu\Path;

abstract readonly class AbstractAbsolutePath extends AbstractPath implements AbsolutePathInterface
{
    public function isAbsolute(): bool
    {
        return true;
    }

    public function isRelative(): bool
    {
        return false;
    }

    /**
     * @param static $targetPath
     * @param callable|null $equals(string $a, string $b): bool
     */
    public function makeRelative(AbsolutePathInterface $targetPath, callable|null $equals = null): RelativePathInterface
    {
        if ($this::class !== $targetPath::class || $this->prefix !== $targetPath->prefix) {
            throw new \InvalidArgumentException(
                'You can only make relative path from paths of same type and same prefix',
            );
        }

        // optimize if the same instance
        if ($this === $targetPath || $this->components === $targetPath->components) {
            return $this->buildRelative(
                $this->components !== [] && array_last($this->components) === '' ? ['.', ''] : ['.'],
            );
        }

        // strip trailing slash
        $baseComponents = $this->components;
        if ($baseComponents !== [] && array_last($baseComponents) === '') {
            array_pop($baseComponents);
        }

        // strip trailing slash
        $targetComponents = $targetPath->components;
        if ($targetComponents !== [] && array_last($targetComponents) === '') {
            array_pop($targetComponents);
        }

        $length = min(\count($baseComponents), \count($targetComponents));
        $equals ??= static fn ($a, $b) => $a === $b;

        for ($i = 0; $i < $length; $i++) {
            if (!$equals($baseComponents[$i], $targetComponents[$i])) {
                break;
            }
        }

        // delete $i components from the beginning (common prefix)
        for ($j = 0; $j < $i; $j++) {
            array_shift($targetComponents);
        }

        // add (baseLen - $i) .. elements
        $numBaseDiff = \count($baseComponents) - $i;
        for ($j = 0; $j < $numBaseDiff; $j++) {
            array_unshift($targetComponents, '..');
        }

        // relative marker
        array_unshift($targetComponents, '.');
        // trailing slash
        if ($targetPath->components !== [] && array_last($targetPath->components) === '') {
            $targetComponents[] = '';
        }

        return $this->buildRelative($targetComponents);
    }

    protected function buildRelative(array $components): RelativePathInterface
    {
        $path = new RelativePath('.');
        $path = clone($path, ['components' => $components]);

        return $path;
    }
}
