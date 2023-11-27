<?php

namespace Qore\Form;

use Exception;
use Laminas\Session\Container;
use Psr\Http\Message\ServerRequestInterface;
use Qore\Form\Protector\ProtectorInterface;
use Qore\SessionManager\SessionManager;

/**
 * Class: FormManager
 *
 * @see FormManagerInterface
 */
class FormManager implements FormManagerInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const SESSION_PREFFIX = 'FormManager:';

    /**
     * name
     *
     * @var mixed
     */
    protected $name = null;

    /**
     * action
     *
     * @var mixed
     */
    protected $action = null;

    /**
     * method
     *
     * @var mixed
     */
    protected $method = self::METHOD_POST;

    /**
     * fields
     *
     * @var mixed
     */
    protected $fields = [];

    /**
     * options
     *
     * @var mixed
     */
    protected $options = [];

    /**
     * data
     *
     * @var mixed
     */
    protected $data = [];

    /**
     * isValid
     *
     * @var mixed
     */
    protected $isValid = null;

    /**
     * defaultDecorator
     *
     * @var Decorator\DecoratorInterface
     */
    protected $defaultDecorator = null;

    /**
     * sessionManager
     *
     * @var mixed
     */
    protected $sessionManager = null;

    /**
     * config
     *
     * @var mixed
     */
    protected $config = null;

    /**
     * request
     *
     * @var mixed
     */
    protected $request = null;

    /**
     * token
     *
     * @var mixed
     */
    protected $token = null;

    /**
     * tokenField
     *
     * @var mixed
     */
    protected $tokenField = null;

    /**
     * protectors
     *
     * @var mixed
     */
    protected $protectors = [];

    /**
     * __construct
     *
     * @param Decorator\DecoratorInterface $_decorator
     */
    public function __construct(Decorator\DecoratorInterface $_decorator, SessionManager $_session, array $_config)
    {
        $this->defaultDecorator = $_decorator;
        $this->sessionManager = $_session;
        $this->config = $_config;

        $this->token = sha1(uniqid('', true));
    }

    /**
     * setDefaultDecorator
     *
     * @param Decorator\DecoratorInterface $_decorator
     */
    public function setDefaultDecorator(Decorator\DecoratorInterface $_decorator) : FormManager
    {
        $this->defaultDecorator = $_decorator;
        return $this;
    }

    /**
     * setRequest
     *
     * @param ServerRequestInterface $_request
     */
    public function setRequest(ServerRequestInterface $_request) : FormManager
    {
        $this->request = $_request;
        return $this;
    }

    /**
     * setSessionManager
     *
     */
    public function setSessionManager(SessionManager $_session) : FormManager
    {
        $this->sessionManager = $_session;
        return $this;
    }

    /**
     * getSesssionManager
     *
     */
    public function getSesssionManager() : SessionManager
    {
        return $this->sessionManager;
    }

    /**
     * getSessionName
     *
     * @param string $_token
     */
    public function getSessionName(string $_token = null) : string
    {
        return strtolower(static::class) . '_' . ($_token ?? $this->token);
    }

    /**
     * getSessionContainer
     *
     * @param ServerRequestInterface $_request
     */
    public function getSessionContainer(ServerRequestInterface $_request = null) : Container
    {
        if (is_null($this->name)) {
            throw new FormManagerException("Please set name for form before initialize session container!");
        }

        $sessionManager = $this->sessionManager;

        if (! is_null($_request)) {
            $params = $_request->getParsedBody();
            if (isset($params['__fmtoken'])) {
                $container = $sessionManager($this->getSessionName($params['__fmtoken']));
                if ($container->exists() && isset($container['name']) && $container['name'] === $this->name) {
                    $this->setToken($params['__fmtoken']);
                    return $container;
                }
            }
        }

        $container = $sessionManager($this->getSessionName($this->token));
        $container['name'] = $this->name;
        return $container->setExpirationSeconds(1*60*60);
    }

    /**
     * checkSessionContainer
     *
     * @param ServerRequestInterface $_request
     */
    public function checkSessionContainer(ServerRequestInterface $_request) : ?Container
    {
        $params = $_request->getParsedBody();
        $sessionManager = $this->sessionManager;

        if (isset($params['__fmtoken'])) {
            $container = $sessionManager($this->getSessionName($params['__fmtoken']));
            if ($container->exists() && isset($container['name']) && $container['name'] === $this->name) {
                $this->token = $params['__fmtoken'];
                return $container;
            }
        }

        return null;
    }

    /**
     * config
     *
     * @param string $_path
     * @param mixed $_default
     */
    public function config(string $_path, $_default = null)
    {
        $config = $this->config;
        $path = explode('.', $_path);

        foreach ($path as $paramKey) {
            if (isset($config[$paramKey])) {
                $config = $config[$paramKey];
            } else {
                return $_default;
            }
        }

        return $config;
    }

    /**
     * __invoke
     *
     * @param string $_name
     * @param array $_fields
     * @param array $_data
     */
    public function __invoke(string $_name, string $_action = null, array $_fields = null, array $_options = null, array $_data = null)
    {
        $this->setName($_name);
        ! $_action ?: $this->setAction($_action);
        ! $_fields ?: $this->setFields($_fields);
        ! $_options ?: $this->setOptions($_options);
        ! $_data ?: $this->setData($_data);

        /** Set form token */
        return $this;
    }

    /**
     * setToken
     *
     * @param string $_token
     */
    public function setToken(string $_token) : FormManagerInterface
    {
        $this->token = $_token;
        if (! is_null($this->tokenField)) {
            $this->tokenField->setData($_token);
        }
        return $this;
    }

    /**
     * setName
     *
     * @param string $_name
     */
    public function setName(string $_name)
    {
        $this->name = $_name;
        return $this;
    }

    /**
     * getName
     *
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * setAction
     *
     * @param string $_action
     */
    public function setAction(string $_action)
    {
        $this->action = $_action;
        return $this;
    }

    /**
     * getAction
     *
     */
    public function getAction() : string
    {
        return $this->action;
    }

    /**
     * setMethod
     *
     * @param mixed $_method
     */
    public function setMethod($_method) : FormManagerInterface
    {
        $this->method = $_method;
        return $this;
    }

    /**
     * getMethod
     *
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * setFields
     *
     * @param array $_fields
     */
    public function setFields(array $_fields) : FormManager
    {
        # - Build object of field from type element
        foreach ($_fields as $fieldName => &$field) {
            # - TODO: check to object of Field\FieldInterface
            $this->setField(new $field['type']($fieldName, $field));
        }

        return $this;
    }

    /**
     * getFields
     *
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * setField
     *
     * @param Field\FieldInterface $_field
     */
    public function setField(Field\FieldInterface $_field)
    {
        $this->fields[$_field->getName()] = $_field;
        return $this;
    }

    /**
     * getField
     *
     * @param string $_fieldName
     */
    public function getField(string $_fieldName) : ?Field\FieldInterface
    {
        return $this->fields[$_fieldName] ?? null;
    }

    /**
     * resetFields
     *
     */
    public function resetFields() : FormManager
    {
        $this->fields = [];
        return $this;
    }

    /**
     * setOptions
     *
     * @param array $_options
     */
    public function setOptions(array $_options) : FormManagerInterface
    {
        $this->options = $_options;
        return $this;
    }

    /**
     * getOptions
     *
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * setData
     *
     * @param iterable $_data
     */
    public function setData(iterable $_data)
    {
        # - Reset validation
        $this->resetValidation();
        # - Save form data
        $this->data = $_data;
        # - Set data to each field
        foreach ($_data as $fieldName => $value) {
            if (! isset($this->fields[$fieldName])) {
                continue;
            }
            $this->fields[$fieldName]->setData($value);
        }

        return $this;
    }

    /**
     * getData
     *
     * @param bool $_validate
     */
    public function getData(bool $_validate = true)
    {
        # - If validate
        if ($_validate && $this->isValid === null) {
            $this->validate();
        }

        $return = [];
        # - Compare result for return
        foreach($this->fields as $fieldName => $field) {
            $return[$fieldName] = $field->getValue();
        }

        return $return;
    }

    /**
     * getErrors
     *
     */
    public function getErrors()
    {
        $return = [];
        # - Compare result for return
        foreach($this->fields as $fieldName => $field) {
            if ($fieldErrors = $field->getErrors()) {
                $return[$fieldName] = $fieldErrors;
            }
        }

        return $return;
    }

    /**
     * validate
     *
     */
    public function validate()
    {
        $this->isValid = true;
        foreach($this->fields as $fieldName => $field) {
            if (! $field->isValid()) {
                $this->isValid = false;
            }
        }
    }

    /**
     * isValid
     *
     */
    public function isValid()
    {
        if ($this->isValid === null) {
            $this->validate();
        }

        return $this->isValid;
    }

    /**
     * resetValidation
     *
     */
    public function resetValidation()
    {
        $this->isValid = null;
        foreach ($this->fields as $field) {
            $field->resetValidation();
        }
    }

    /**
     * decorate
     *
     * @param mixed $_strategy
     * @param Decorator\DecoratorInterface $_decorator
     */
    public function decorate($_strategy, Decorator\DecoratorInterface $_decorator = null)
    {
        if (is_null($_decorator)) {
            $_decorator = $this->defaultDecorator;
        }

        if ($this->protectors && ! is_null($this->request)) {
            $this->protect($this->request);
        }

        $_decorator->setForm($this);

        return $_decorator($_strategy);
    }

    /**
     * setProtector
     *
     */
    public function setProtectors(array $_protectors) : FormManager
    {
        foreach ($_protectors as $key => $protector) {
            if (! $protector instanceof ProtectorInterface) {
                throw new Exception(sprintf(
                    "Protector class (%s) must implements of %s interface", $protector, Protector\ProtectorInterface::class
                ));
            }

            $this->protectors[] = $protector->setFormManager($this);
        }

        return $this;
    }

    /**
     * protect
     *
     */
    public function protect(ServerRequestInterface $_request) : FormManager
    {
        foreach ($this->protectors as $protector) {
            $protector->protect($_request);
        }

        return $this;
    }

    /**
     * inspect
     *
     */
    public function inspect(ServerRequestInterface $_request) : ServerRequestInterface
    {
        foreach ($this->protectors as $protector) {
            $_request = $protector->inspect($_request);
        }

        return $_request;
    }

}
