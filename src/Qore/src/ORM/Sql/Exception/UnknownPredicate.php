<?php

namespace Qore\ORM\Sql\Exception;

use Qore\ExceptionInterface;
use RuntimeException;

/**
 * @inheritDoc
 */
class UnknownPredicate extends RuntimeException implements ExceptionInterface
{
}
