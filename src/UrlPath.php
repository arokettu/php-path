<?php

declare(strict_types=1);

namespace Arokettu\Path;

use Arokettu\Path\Exceptions\PathWentBeyondRootException;
use ValueError;

final readonly class UrlPath extends AbstractAbsolutePath
{
    public static function parse(string $path, bool $strict = false): self
    {
        return new self($path, $strict);
    }

    protected function parsePath(string $path, bool $strict): void
    {
        $urlComponents = parse_url($path);

        if ($urlComponents === false) {
            throw new ValueError('Url is malformed');
        }

        $urlPath = $urlComponents['path'] ?? '';

        $prefix = '';
        if (isset($urlComponents['scheme'])) {
            $prefix .= $urlComponents['scheme'] . ':';
        }
        if (isset($urlComponents['host'])) {
            $prefix .= '//';
            if (isset($urlComponents['user'])) {
                $prefix .= $urlComponents['user'];
                if (isset($urlComponents['pass'])) {
                    $prefix .= ':' . $urlComponents['pass'];
                }
                $prefix .= '@';
            }
            $prefix .= $urlComponents['host'] . '/';
        }

        $components = explode('/', $urlPath);

        $parsedComponents = $this->normalize($components);

        if ($parsedComponents !== [] && $parsedComponents[0] === '..') {
            if ($strict) {
                throw new PathWentBeyondRootException('Path went beyond root');
            }

            do {
                array_shift($parsedComponents);
            } while ($parsedComponents[0] === '..');
        }

        $this->prefix = $prefix;
        $this->components = $parsedComponents;
    }
}
