<?php

namespace Qore\Form\Field;

class Field implements FieldInterface
{
    # - Text type
    const TYPE_TEXT = 'text';
    # - Text type
    const TYPE_HIDDEN = 'hidden';
    # - Button type
    const TYPE_BUTTON = 'button';
    # - Button type
    const TYPE_BUTTON_GROUP = 'button-group';
    # - Text type
    const TYPE_SWITCHER = 'switch';
    # - Password type
    const TYPE_PASSWORD = 'password';
    # - Email type
    const TYPE_EMAIL = 'email';
    # - Textarea type
    const TYPE_TEXTAREA = 'textarea';
    # - Wysiwyg type
    const TYPE_WYSIWYG = 'wysiwyg';
    # - BlockEditor type
    const TYPE_BLOCK_EDITOR = 'blockeditor';
    # - Dropzone type
    const TYPE_DROPZONE = 'dropzone';
    # - Select type
    const TYPE_SELECT = 'select';
    # - TreeSelect type
    const TYPE_TREESELECT = 'treeselect';
    # - AutoComplete type
    const TYPE_AUTOCOMPLETE = 'autocomplete';
    # - Datetime type
    const TYPE_DATETIME = 'datetime';
    # - Checkbox type
    const TYPE_CHECKBOX = 'checkbox';
    # - Slider type
    const TYPE_SLIDER = 'slider';
    # - Submit type
    const TYPE_SUBMIT = 'submit';
    # - Colorpicker type
    const TYPE_COLORPICKER = 'colorpicker';
    # = CodeEditor type
    const TYPE_CODEEDITOR = 'codeeditor';

    /**
     * type
     *
     * @var mixed
     */
    protected $type = null;

    /**
     * name
     *
     * @var mixed
     */
    protected $name = null;

    /**
     * label
     *
     * @var mixed
     */
    protected $label = null;

    /**
     * placeholder
     *
     * @var mixed
     */
    protected $placeholder = null;

    /**
     * value
     *
     * @var mixed
     */
    protected $value = null;

    /**
     * info
     *
     * @var mixed
     */
    protected $info = null;

    /**
     * data
     *
     * @var mixed
     */
    protected $data = null;

    /**
     * options
     *
     * @var mixed
     */
    protected $options = [];

    /**
     * position
     *
     * @var mixed
     */
    protected $position = [];

    /**
     * additional
     *
     * @var mixed
     */
    protected $additional = [];

    /**
     * conditions
     *
     * @var mixed
     */
    protected $conditions = [];

    /**
     * actions
     *
     * @var mixed
     */
    protected $actions = [];

    /**
     * validators
     *
     * @var mixed
     */
    protected $validators = [];
    /**
     * errors
     *
     * @var mixed
     */
    protected $errors = [];

    /**
     * isValid
     *
     * @var mixed
     */
    protected $isValid = null;

    /**
     * construct
     *
     * @param string $_name
     * @param array $_options
     */
    public function __construct(string $_name, array $_options = [])
    {
        $this->setName($_name);
        ! isset($_options['label']) ?: $this->setLabel($_options['label']);
        ! isset($_options['placeholder']) ?: $this->setPlaceholder($_options['placeholder']);
        ! isset($_options['value']) ?: $this->setValue($_options['value']);
        ! isset($_options['data']) ?: $this->setData($_options['data']);
        ! isset($_options['info']) ?: $this->setInfo($_options['info']);
        ! isset($_options['validators']) ?: $this->setValidators($_options['validators']);
        ! isset($_options['options']) ?: $this->setOptions($_options['options']);
        ! isset($_options['position']) ?: $this->setPosition($_options['position']);
        ! isset($_options['additional']) ?: $this->setAdditional($_options['additional']);
        ! isset($_options['conditions']) ?: $this->setConditions($_options['conditions']);
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
     * setType
     *
     * @param string $_type
     */
    public function setType(string $_type)
    {
        $this->type = $_type;
        return $this;
    }

    /**
     * getType
     *
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * setLabel
     *
     * @param string $_label
     */
    public function setLabel(string $_label)
    {
        $this->label = $_label;
        return $this;
    }

    /**
     * getLabel
     *
     */
    public function getLabel() : string
    {
        return $this->label ?? '';
    }

    /**
     * setPlaceholder
     *
     * @param string $_placeholder
     */
    public function setPlaceholder(string $_placeholder)
    {
        $this->placeholder = $_placeholder;
        return $this;
    }

    /**
     * getPlaceholder
     *
     * @param string $_placeholder
     */
    public function getPlaceholder() : string
    {
        return $this->placeholder ?? '';
    }

    /**
     * setValue
     *
     * @param mixed $_value
     */
    public function setValue($_value) : FieldInterface
    {
        $this->value = $_value;
        return $this;
    }

    /**
     * setInfo
     *
     * @param string $_info
     */
    public function setInfo(string $_info) : FieldInterface
    {
        $this->info = $_info;
        return $this;
    }

    /**
     * getInfo
     *
     */
    public function getInfo(): string
    {
        return $this->info ?? '';
    }

    /**
     * setAdditional
     *
     * @param mixed $_additional
     */
    public function setAdditional($_additional) : FieldInterface
    {
        $this->additional = $_additional;
        return $this;
    }

    /**
     * getAdditional
     *
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * setConditions
     *
     * @param mixed $_additional
     */
    public function setConditions(array $_conditions) : FieldInterface
    {
        $this->conditions = $_conditions;
        return $this;
    }

    /**
     * getConditions
     *
     * @param bool $_decorate
     */
    public function getConditions(bool $_decorate = false) : array
    {
        if (! $_decorate) {
            return $this->conditions;
        }

        $return = [];
        foreach ($this->conditions as $condition) {
            $return[] = is_object($condition) ? $condition->decorate() : $condition;
        }

        return $return;
    }

    /**
     * setActions
     *
     * @param array $_actions
     */
    public function setActions(array $_actions) : FieldInterface
    {
        $this->actions = $_actions;
        return $this;
    }

    /**
     * getActions
     *
     * @param bool $_decorate
     */
    public function getActions(bool $_decorate = false) : array
    {
        if (! $_decorate) {
            return $this->actions;
        }

        $return = [];
        foreach ($this->actions as $action) {
            $return[] = is_object($action) ? $action->decorate() : $action;
        }

        return $return;
    }

    /**
     * setData
     *
     * @param mixed $_value
     */
    public function setData($_data) : FieldInterface
    {
        $this->data = $_data;
        $this->resetValidation();
        return $this;
    }

    /**
     * Get field data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * getValue
     *
     */
    public function getValue()
    {
        return $this->data ?? $this->value ?? null;
    }

    /**
     * getOptioin
     *
     * @param string $_option
     * @param mixed $_default
     */
    public function getOption(string $_option, $_default = null)
    {
        return $this->options[$_option] ?? $_default;
    }

    /**
     * setValidators
     *
     * @param array $_validators
     */
    public function setValidators(array $_validators)
    {
        $this->validators = $this->prepareValidators($_validators);
        return $this;
    }

    /**
     * addValidator
     *
     * @param array $_params
     */
    public function addValidator(array $_params)
    {
        $this->validators = array_merge($this->validators, $this->prepareValidators([$_params]));
        return $this;
    }

    /**
     * prepareValidators
     *
     * @param array $_validators
     */
    private function prepareValidators(array $_validators) : array
    {
        foreach ($_validators as &$validator) {
            # - Get type and message
            $type = $validator['type'];
            $message = $validator['message'] ?? null;
            # - Validator options
            $validatorOptions = $validator['options'] ?? [];
            # - Check if is not array
            $validatorOptions = is_array($validatorOptions)
                ? $validatorOptions
                : [$validatorOptions];
            # - Set breakable
            $validatorOptions['break'] = $validator['break'] ?? false;
            # - Get instance of validator $type
            $validator = $type::get($validatorOptions);
            # - If isset message
            if ($message) {
                $validator->setMessage($message);
            }
        }

        return $_validators;
    }

    /**
     * setOptions
     *
     * @param mixed $_options
     */
    public function setOptions($_options) : FieldInterface
    {
        $this->options = $_options;
        return $this;
    }

    /**
     * getOptions
     *
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * setPosition
     *
     * @param mixed $_position
     *  - [ 'set' => 'top|bottom|after|before', 'target' => '{field-name}' ]
     */
    public function setPosition($_position) : FieldInterface
    {
        $this->position = $_position;
        return $this;
    }

    /**
     * getOptions
     *
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * isValid
     *
     */
    public function isValid() : ?bool
    {
        if ($this->isValid === null) {
            $this->validate();
        }

        return $this->isValid;
    }

    /**
     * validate
     *
     */
    public function validate() : FieldInterface
    {
        $this->isValid = true;

        $errors = [];
        foreach ($this->validators as $validator) {
            if(! $validator->isValid($this->data)) {
                $this->isValid = false;
                $errors = array_merge($errors, $validator->getMessages());
                if ($validator->isBreakable()) {
                    break;
                }
            }
        }

        $this->errors = $errors;
        return $this;
    }

    /**
     * getErrors
     *
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * resetValidation
     *
     */
    public function resetValidation() : FieldInterface
    {
        $this->isValid = null;
        $this->errors = [];
        return $this;
    }
}
