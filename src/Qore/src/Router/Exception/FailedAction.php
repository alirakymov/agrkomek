<?php

namespace Qore\Router\Exception;

use Qore\ExceptionInterface;
use RuntimeException;

/**
 * @inheritDoc
 */
class FailedAction extends RuntimeException implements ExceptionInterface
{
}
