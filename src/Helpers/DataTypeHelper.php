<?php

declare(strict_types=1);

namespace Arokettu\Path\Helpers;

use SplDoublyLinkedList;

/**
 * @internal
 */
final class DataTypeHelper
{
    public static function iterableToNewListInstance(iterable $iterable): SplDoublyLinkedList
    {
        if ($iterable instanceof SplDoublyLinkedList) {
            return clone $iterable;
        }

        $list = new SplDoublyLinkedList();

        foreach ($iterable as $value) {
            $list->push($value);
        }

        return $list;
    }
}
