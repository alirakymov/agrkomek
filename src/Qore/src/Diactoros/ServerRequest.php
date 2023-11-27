<?php

declare(strict_types=1);

namespace Qore\Diactoros;

use ArrayAccess;
use Laminas\Json;
use Laminas\Diactoros\ServerRequest as DiactorosServerRequest;

/**
 * Class: ServerRequest
 *
 * @see ZendDiactoros\ServerRequest
 */
class ServerRequest extends DiactorosServerRequest
{
    /**
     * isXmlHttpRequest
     *
     */
    public function isXmlHttpRequest() : bool
    {
        return in_array('XMLHttpRequest', $this->getHeader('x-requested-with'));
    }

    /**
     * getJsonBody
     *
     */
    public function parseJsonBody($_type = Json\Json::TYPE_ARRAY)
    {
        return Json\Json::decode($this->getBody()->getContents(), $_type);
    }

    /**
     * Get data from parsed body
     *
     * @param string $_path
     * @param $_default (optional)
     *
     * @return mixed
     */
    public function __invoke(string $_path, $_default = null)
    {
        $_path = explode('.', $_path);
        $property = $this->getParsedBody();

        foreach ($_path as $point) {
            if (! is_array($property) && ! $property instanceof ArrayAccess || ! isset($property[$point])) {
                return $_default;
            }

            $property = &$property[$point];
        }

        return $property;
    }

}
