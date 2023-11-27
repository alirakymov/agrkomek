<?php

namespace Qore\SynapseManager\Plugin\Indexer;

interface DataTypeConverterInterface
{
    /**
     * Convert attribute type to mapping data type
     *
     * @param string $_type
     *
     * @return array
     */
    public function convert(string $_type) : array;

    /**
     * Convert data type
     *
     * @param string $_type
     * @param  $_value
     *
     * @throws PluginException
     *
     * @return mixed
     */
    public function convertValue(string $_type, $_value);
}
