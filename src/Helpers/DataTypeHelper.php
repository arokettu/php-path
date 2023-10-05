<?php

declare(strict_types=1);

namespace Arokettu\Path\Helpers;

use Ds\Deque;

/**
 * @internal
 */
final class DataTypeHelper
{
    public static function iterableToNewListInstance(iterable $iterable): Deque
    {
        if ($iterable instanceof Deque) {
            return clone $iterable;
        }

        $list = new Deque();

        foreach ($iterable as $value) {
            $list->push($value);
        }

        return $list;
    }
}
