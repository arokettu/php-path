<?php

declare(strict_types=1);

namespace Arokettu\Path;

use Arokettu\Path\Helpers\DataTypeHelper;
use SplDoublyLinkedList;

abstract class AbstractPath implements PathInterface
{
    protected string $prefix;
    /** @var SplDoublyLinkedList<string> */
    protected SplDoublyLinkedList $components;

    abstract protected function parsePath(string $path, bool $strict): void;

    public function __construct(string $path, bool $strict = false)
    {
        $this->parsePath($path, $strict);
    }

    /**
     * @return static
     */
    public function resolveRelative(RelativePathInterface $path, bool $strict = false): self
    {
        if ($path instanceof RelativePath) {
            // optimize
            $relativeComponents = $path->components;
        } else {
            // allow external implementations
            $relativeComponents = $path->getComponents();
            if (!array_is_list($relativeComponents)) {
                throw new \InvalidArgumentException(
                    'Poor RelativePathInterface implementation: getComponents() must return a list'
                );
            }
        }

        if ($path->isRoot()) {
            $newPath = clone $this;
            $newPath->components = DataTypeHelper::iterableToNewListInstance($relativeComponents);

            return $newPath;
        }

        $components = clone $this->components;

        // remove trailing slash
        if ($components->top() === '') {
            $components->pop();
        }

        $numComponents = \count($relativeComponents);
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

    protected function normalize(array $components): SplDoublyLinkedList
    {
        $numComponents = \count($components);

        // skip empties in the beginning
        for ($i = 0; $i < $numComponents; $i++) {
            if ($components[$i] !== '') {
                break;
            }
        }

        $componentsList = new SplDoublyLinkedList();

        $component = null; // also stores last component ignoring $prevComponent logic
        $prevComponent = null;
        for ($j = $i; $j < $numComponents; $j++) {
            $component = $components[$j];

            if ($component === '.' || $component === '') {
                continue;
            }

            if (
                $component === '..' &&
                $prevComponent !== '..' && $prevComponent !== null && // leading ..'s
                $componentsList->count() > 0 // beginning of the list
            ) {
                $componentsList->pop();
                continue;
            }

            $componentsList->push($component);
            $prevComponent = $component;
        }

        // trailing slash logic
        if ($component === '') {
            $componentsList->push('');
        }

        return $componentsList;
    }

    protected function normalizeHead(SplDoublyLinkedList $components, bool $strict): SplDoublyLinkedList
    {
        while (!$components->isEmpty()) {
            if ($components[0] === '.') {
                $components->shift();
                continue;
            }

            if ($components[0] === '..') {
                if ($strict) {
                    throw new \UnexpectedValueException('Relative path went beyond root');
                }

                $components->shift();
                continue;
            }

            break;
        }

        return $components;
    }

    public function toString(): string
    {
        return $this->prefix . \iter\join('/', $this->components);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getComponents(): array
    {
        return iterator_to_array($this->components, false);
    }

    public function __debugInfo(): array
    {
        return [
            'path' => $this->toString(),
        ];
    }
}
