<?php

declare(strict_types=1);

namespace Qore\ORM\Gateway;

/**
 * Class: GatewayCursor
 *
 */
class GatewayCursor implements GatewayCursorInterface
{
    /**
     * gateway
     *
     * @var mixed
     */
    private $gateway = null;

    /**
     * subject
     *
     * @var mixed
     */
    private $subject = null;

    /**
     * beforeCall
     *
     * @var mixed
     */
    private $beforeCall = null;

    /**
     * afterCall
     *
     * @var mixed
     */
    private $afterCall = null;

    /**
     * gatewayProcessor
     *
     * @var mixed
     */
    private $gatewayProcessor = null;

    /**
     * __construct
     *
     * @param mixed $_gateway
     * @param mixed $_subject
     * @param mixed $_gatewayProcessor
     */
    public function __construct($_gateway, $_subject, $_gatewayProcessor)
    {
        $this->gateway = $_gateway;
        $this->subject = $_subject;
        $this->gatewayProcessor = $_gatewayProcessor;
    }

    /**
     * __call
     *
     */
    public function __call($_method, $_args)
    {
        # - Evaluate gateway methods from cursor environment
        return $this->gateway->fromCursor(function($_gateway) use ($_method, $_args){
            return $this->launch($_method, $_args);
        }, $this);
    }

    /**
     * __get
     *
     * @param mixed $_property
     */
    public function __get($_property)
    {
        # - Evaluate gateway methods from cursor environment
        return $this->gateway->fromCursor(function($_gateway) use ($_property){
            return $this->launch($_property);
        }, $this);
    }

    /**
     * __invoke
     *
     * @param ... $_args
     */
    public function __invoke(...$_args)
    {
        # - Evaluate gateway methods from cursor environment
        return $this->gateway->fromCursor(function($_gateway) use ($_args){
            return $this->launch('__invoke', $_args);
        }, $this);
    }

    /**
     * _beforeCall
     *
     * @param callable $_callback
     */
    public function _beforeCall(callable $_callback)
    {
        $this->beforeCall = $_callback;
    }

    /**
     * _afterCall
     *
     * @param callable $_callback
     */
    public function _afterCall(callable $_callback)
    {
        $this->afterCall = $_callback;
    }

    /**
     * getGatewayProcessor
     *
     */
    public function getGatewayProcessor()
    {
        return $this->gatewayProcessor;
    }

    /**
     * launch
     *
     * @param mixed $_methodOrProperty
     * @param mixed $_args
     */
    private function launch($_methodOrProperty, $_args = null)
    {
        $subject = $this->subject;
        # - Launch before callback
        is_null($beforeCall = $this->beforeCall) || $beforeCall($_methodOrProperty, $_args);
        # - Launch call
        if (is_null($_args)) {
            $result = $this->subject->{$_methodOrProperty};
        } else {
            $result = $_methodOrProperty === '__invoke' ? $subject(...$_args) : call_user_func_array([$this->subject, $_methodOrProperty], $_args);
        }
        # - Launch after callback
        is_null($afterCall = $this->afterCall) || $afterCall($_methodOrProperty, $_args);
        # - check result to compliance with subject
        return is_object($result) && get_class($result) === get_class($this->subject) ? $this : $result;
    }

}
