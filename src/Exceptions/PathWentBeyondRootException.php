<?php

/**
 * @copyright 2021 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\Path\Exceptions;

use Exception;

final class PathWentBeyondRootException extends Exception implements PathException
{
}
