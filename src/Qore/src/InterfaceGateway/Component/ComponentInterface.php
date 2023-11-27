<?php

namespace Qore\InterfaceGateway\Component;

use Qore\InterfaceGateway\InterfaceGateway;


/**
 * Interface: ComponentInterface
 *
 */
interface ComponentInterface
{
    /** @var string Replace strategy */
    const REPLACE = 'replace';

    /** @var string Concat strategy */
    const CONCAT = 'concat';

    /** @var string Clear strategy */
    const CLEAR = 'clear';

    /**
     * setInterfaceGateway
     *
     * @param InterfaceGateway $_ig
     */
    public function setInterfaceGateway(InterfaceGateway $_ig) : ComponentInterface;

    /**
     * getName
     *
     */
    public function getName() : string;

    /**
     * setName
     *
     * @param string $_type
     */
    public function setName(string $_type) : ComponentInterface;

    /**
     * getType
     *
     */
    public function getType() : string;

    /**
     * setType
     *
     * @param string $_type
     */
    public function setType(string $_type) : ComponentInterface;

    /**
     * getOptions
     *
     * @param array $_options
     */
    public function getOptions() : array;

    /**
     * Set options. All defined options will be replaced with new options.
     *
     * @param array $_options
     * @return ComponentInterface
     */
    public function setOptions(array $_options) : ComponentInterface;

    /**
     * Add options to exists options
     *
     * @param array $_options
     * @return ComponentInterface
     */
    public function addOptions(array $_options) : ComponentInterface;

    /**
     * Get option by index or default value
     *
     * @param string $_index
     * @param mixed $_default (optional)
     *
     * @return mixed
     */
    public function getOption(string $_index, $_default = null);

    /**
     * Set option
     *
     * @param string $_name
     * @param mixed $_value
     *
     * @return ComponentInterface
     */
    public function setOption(string $_name, $_value) : ComponentInterface;

    /**
     * Set child components
     *
     * @param array<ComponentInterface> $_components
     *
     * @return ComponentInterface|array
     */
    public function components(array $_components);

    /**
     * component
     *
     * @param ComponentInterface $_component
     * @return $this
     */
    public function component(ComponentInterface $_component) : ComponentInterface;

    /**
     * Set merge strategy 'replace'|'concat'
     *
     * @param string $_strategy
     *
     * @return ComponentInterface
     */
    public function strategy(string $_strategy) : ComponentInterface;

    /**
     * Get merge strategy
     *
     * @return string
     */
    public function getMergeStrategy() : string;

    /**
     * execute
     *
     * @param string $_command
     * @param array $_options
     */
    public function execute(string $_command, array $_options = []) : ComponentInterface;

    /**
     * compose
     *
     */
    public function compose() : array;

}
