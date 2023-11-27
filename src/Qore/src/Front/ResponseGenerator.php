<?php

namespace Qore\Front;

use Laminas\Diactoros\Response\JsonResponse;

/**
 * Class: ResponseGenerator
 *
 * @abstract
 */
abstract class ResponseGenerator
{
    /**
     * get
     *
     * @param ... $_components
     */
    final public static function get(...$_components)
    {
        $response = [];
        foreach ($_components as $component) {
            $response = array_merge($response, $component->compose());
        }

        return new JsonResponse($response);
    }
}
