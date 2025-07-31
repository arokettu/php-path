<?php

declare(strict_types=1);

namespace Arokettu\Path\Exceptions;

use Exception;

final class PathWentBeyondRootException extends Exception implements PathException
{
}
