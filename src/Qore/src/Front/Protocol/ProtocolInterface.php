<?php

namespace Qore\Front\Protocol;

interface ProtocolInterface
{
    /**
     * get
     *
     * @param string $_name
     * @param array $_options
     * @return $this
     */
    public static function get(string $_name, array $_options = []);

    /**
     * __construct
     *
     * @param string $_name
     */
    public function __construct(string $_name);

    /**
     * component
     *
     * @param ProtocolInterface $_component
     * @return $this
     */
    public function component($_component);

    /**
     * asArray
     *
     * @return array
     */
    public function asArray();

    /**
     * getType
     *
     */
    public function getType();

    /**
     * getName
     *
     */
    public function getName();
}
