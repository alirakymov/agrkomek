<?php

declare(strict_types=1);

namespace Qore\Middleware\Action;

use Qore\Middleware\BaseMiddleware;

/**
 * Class: BaseActionMiddleware
 *
 * @see ActionMiddlewareInterface
 * @see BaseMiddleware
 * @abstract
 */
abstract class BaseActionMiddleware extends BaseMiddleware implements ActionMiddlewareInterface
{
    /**
     * routeName
     *
     * @param string $_actionClass
     * @param string $_routeName
     *
     * @return string
     */
    public function routeName(string $_actionClass, string $_routeName = null) : string
    {
        if ($_routeName === null) {
            $_routeName = $_actionClass;
            $_actionClass = static::class;
        }

        return $_actionClass . ($_routeName ? '.' . $_routeName : '');
    }

    /**
     * splitRouteName
     *
     * @param string $_routeName
     */
    public function splitRouteName(string $_routeName) : array
    {
        $splitted = explode('.', $_routeName);
        if ($splitted[0] == static::class) {
            unset($splitted[0]);
        }
        return array_values($splitted);
    }
}
