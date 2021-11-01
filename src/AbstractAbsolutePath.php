<?php

declare(strict_types=1);

namespace Arokettu\Path;

use Arokettu\Path\Helpers\DataTypeHelper;

abstract class AbstractAbsolutePath extends AbstractPath implements AbsolutePathInterface
{
    /**
     * @param static $targetPath
     */
    public function makeRelative(AbsolutePathInterface $targetPath, ?\Closure $equals = null): RelativePathInterface
    {
        if (\get_class($this) !== \get_class($targetPath) || $this->prefix !== $targetPath->prefix) {
            throw new \InvalidArgumentException(
                'You can only make relative path from paths of same type and same prefix'
            );
        }

        // optimize if the same instance
        if ($this === $targetPath || $this->components === $targetPath->components) {
            return $this->buildRelative(DataTypeHelper::iterableToNewListInstance(['.']));
        }

        $length = min($this->components->count(), $targetPath->components->count());
        $equals ??= fn($a, $b) => $a === $b;

        for ($i = 0; $i < $length; $i++) {
            if (!$equals($this->components[$i], $targetPath->components[$i])) {
                break;
            }
        }

        $relativeComponents = clone $targetPath->components;

        // delete $i components from the beginning (common prefix)
        for ($j = 0; $j < $i; $j++) {
            $relativeComponents->shift();
        }

        // add (baseLen - $i) .. elements
        $numBaseDiff = $this->components->count() - $i;
        for ($j = 0; $j < $numBaseDiff; $j++) {
            $relativeComponents->unshift('..');
        }

        $relativeComponents->unshift('.');

        return $this->buildRelative($relativeComponents);
    }

    protected function buildRelative(\SplDoublyLinkedList $components): RelativePathInterface
    {
        $path = new RelativePath('.');
        $path->components = $components;

        return $path;
    }
}
