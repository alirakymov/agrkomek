<?php

namespace Qore\Form\Validator;

class Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = null;

    /**
     * validatorInstance
     *
     * @var mixed
     */
    protected $validatorInstance = null;

    /**
     * breakable
     *
     * @var mixed
     */
    protected $breakable = false;

    /**
     * get
     *
     * @param $_params
     */
    public static function get(array $_params)
    {
        return new static($_params);
    }

    /**
     * __construct
     *
     * @param $params
     */
    private function __construct(array $_params)
    {
        # - if this validator breakable
        if (isset($_params['break'])) {
            $this->breakable = (bool)$_params['break'];
            unset($_params['break']);
        }

        $this->validatorInstance = new static::$validatorClass($_params);
    }

    /**
     * isBreakable
     *
     */
    public function isBreakable()
    {
        return $this->breakable;
    }

    /**
     * instance
     *
     */
    public function instance()
    {
        return $this->validatorInstance;
    }

    /**
     * setMessage
     *
     * @param string $_message
     */
    public function setMessage(string $_message)
    {
        return $this->instance()->setMessage($_message);
    }

    /**
     * getMessages
     *
     */
    public function getMessages() : array
    {
        return array_values($this->instance()->getMessages());
    }

    /**
     * isValid
     *
     */
    public function isValid($_data)
    {
        return $this->instance()->isValid($_data);
    }

}
