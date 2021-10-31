<?php

declare(strict_types=1);

namespace Arokettu\Path;

abstract class AbstractPath implements PathInterface
{
    protected string $prefix;
    protected \SplDoublyLinkedList $components;

    abstract protected function parsePath(string $path): \SplDoublyLinkedList;

    public function __construct(string $path)
    {
        $this->components = $this->parsePath($path);
    }

    /**
     * @param RelativePath|string $path
     * @static
     */
    public function resolveRelative($path, bool $strict = false): self
    {
        if (\is_string($path)) {
            $path = $this->buildRelative($path);
        }

        return $this->doResolveRelative($path, $strict);
    }

    protected function buildRelative(string $path): RelativePath
    {
        return new RelativePath($path);
    }

    /**
     * @return static
     */
    protected function doResolveRelative(RelativePath $path, bool $strict): self
    {
        $relativeComponents = $path->components;

        if ($path->isRoot()) {
            $newPath = clone $this;
            $newPath->components = clone $relativeComponents;

            return $newPath;
        }

        $components = clone $this->components;

        $numComponents = $relativeComponents->count();
        for ($i = 0; $i < $numComponents; $i++) {
            if ($relativeComponents[$i] === '.') {
                continue;
            }

            if (
                $relativeComponents[$i] === '..' &&
                !$components->isEmpty() &&
                $components->top() !== '..' &&
                $components->top() !== '.'
            ) {
                $components->pop();
                continue;
            }

            $components->push($relativeComponents[$i]);
        }

        $newPath = clone $this;
        $newPath->components = $this->normalizeHead($components, $strict);

        return $newPath;
    }

    protected function normalize(array $components): \SplDoublyLinkedList
    {
        $componentsList = new \SplDoublyLinkedList();

        $prevComponent = null;
        foreach ($components as $component) {
            if ($component === '.' || $component === '') {
                continue;
            }

            if ($component === '..' && $prevComponent !== '..' && $prevComponent !== null) {
                $componentsList->pop();
            } else {
                $componentsList->push($component);
            }

            $prevComponent = $component;
        }

        return $componentsList;
    }

    protected function normalizeHead(\SplDoublyLinkedList $components, bool $strict): \SplDoublyLinkedList
    {
        while (!$components->isEmpty()) {
            if ($components[0] === '.') {
                $components->shift();
                continue;
            }

            if ($components[0] === '..') {
                if ($strict) {
                    throw new \InvalidArgumentException('Relative path went beyond root');
                }

                $components->shift();
                continue;
            }

            break;
        }

        return $components;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function getComponents(): array
    {
        return iterator_to_array($this->components, false);
    }
}
