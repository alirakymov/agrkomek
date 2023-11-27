<?php

namespace Qore\Router\Exception;

use Qore\ExceptionInterface;
use RuntimeException;

/**
 * @inheritDoc
 */
class UndefinedRouteMiddleware extends RuntimeException implements ExceptionInterface
{
}
