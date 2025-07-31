<?php

declare(strict_types=1);

namespace Arokettu\Path;

abstract readonly class AbstractPath implements PathInterface
{
    public string $prefix;
    /** @var list<string> */
    public array $components;

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
            $relativeComponents = $path->components;
            if (!array_is_list($relativeComponents)) {
                throw new \InvalidArgumentException(
                    'Poor RelativePathInterface implementation: getComponents() must return a list',
                );
            }
        }

        if ($path->isRoot()) {
            $newPath = clone($this, [
                'components' => $relativeComponents,
            ]);

            return $newPath;
        }

        $components = $this->components;

        // remove trailing slash
        if (array_last($components) === '') {
            array_pop($components);
        }

        $numComponents = \count($relativeComponents);
        for ($i = 0; $i < $numComponents; $i++) {
            if ($relativeComponents[$i] === '.') {
                continue;
            }

            if (
                $relativeComponents[$i] === '..' &&
                $components !== [] &&
                array_last($components) !== '..' &&
                array_last($components) !== '.'
            ) {
                array_pop($components);
                continue;
            }

            $components[] = $relativeComponents[$i];
        }

        $newPath = clone($this, [
            'components' => $this->normalizeHead($components, $strict),
        ]);

        return $newPath;
    }

    protected function normalize(array $components): array
    {
        $numComponents = \count($components);

        // skip empties in the beginning
        for ($i = 0; $i < $numComponents; $i++) {
            if ($components[$i] !== '') {
                break;
            }
        }

        $componentsList = [];

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
                $componentsList !== [] // beginning of the list
            ) {
                array_pop($componentsList);
                continue;
            }

            $componentsList[] = $component;
            $prevComponent = $component;
        }

        // trailing slash logic
        if ($component === '') {
            $componentsList[] = '';
        }

        return $componentsList;
    }

    protected function normalizeHead(array $components, bool $strict): array
    {
        while ($components !== []) {
            if ($components[0] === '.') {
                array_shift($components);
                continue;
            }

            if ($components[0] === '..') {
                if ($strict) {
                    throw new \UnexpectedValueException('Relative path went beyond root');
                }

                array_shift($components);
                continue;
            }

            break;
        }

        return $components;
    }

    public function toString(): string
    {
        return $this->prefix . implode('/', $this->components);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function __debugInfo(): array
    {
        return [
            'path' => $this->toString(),
        ];
    }

    public function __serialize(): array
    {
        return [$this->prefix, $this->components];
    }

    public function __unserialize(array $data): void
    {
        [$this->prefix, $this->components] = $data;
    }
}
