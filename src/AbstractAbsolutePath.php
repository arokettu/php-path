<?php

declare(strict_types=1);

namespace Arokettu\Path;

use Arokettu\Path\Helpers\DataTypeHelper;
use SplDoublyLinkedList;

abstract class AbstractAbsolutePath extends AbstractPath implements AbsolutePathInterface
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
            return $this->buildRelative(DataTypeHelper::iterableToNewListInstance(
                $this->components->count() > 0 && $this->components->top() === '' ? ['.', ''] : ['.'],
            ));
        }

        // strip trailing slash
        $baseComponents = $this->components;
        if ($baseComponents->count() > 0 && $baseComponents->top() === '') {
            $baseComponents = clone $baseComponents; // clone when necessary
            $baseComponents->pop();
        }

        // strip trailing slash
        $targetComponents = clone $targetPath->components; // always clone
        if ($targetComponents->count() > 0 && $targetComponents->top() === '') {
            $targetComponents->pop();
        }

        $length = min($baseComponents->count(), $targetComponents->count());
        $equals ??= static fn ($a, $b) => $a === $b;

        for ($i = 0; $i < $length; $i++) {
            if (!$equals($baseComponents[$i], $targetComponents[$i])) {
                break;
            }
        }

        // delete $i components from the beginning (common prefix)
        for ($j = 0; $j < $i; $j++) {
            $targetComponents->shift();
        }

        // add (baseLen - $i) .. elements
        $numBaseDiff = $baseComponents->count() - $i;
        for ($j = 0; $j < $numBaseDiff; $j++) {
            $targetComponents->unshift('..');
        }

        // relative marker
        $targetComponents->unshift('.');
        // trailing slash
        if ($targetPath->components->count() && $targetPath->components->top() === '') {
            $targetComponents->push('');
        }

        return $this->buildRelative($targetComponents);
    }

    protected function buildRelative(SplDoublyLinkedList $components): RelativePathInterface
    {
        $path = new RelativePath('.');
        $path->components = $components;

        return $path;
    }
}
