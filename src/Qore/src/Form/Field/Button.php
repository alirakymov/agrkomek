<?php

namespace Qore\Form\Field;

class Button extends Field
{
    /**
     * @var string
     */
    const ACTION_SUBMIT = 'submit';

    /**
     * @var string
     */
    const ACTION_REQUEST = 'request';

    /**
     * @var string
     */
    const ACTION_REDIRECT = 'redirect';

    /**
     * @var string
     */
    const ACTION_SEPARATOR = 'separator';

    /**
     * @var string
     */
    protected $type = self::TYPE_BUTTON;

    /**
     * @var string
     */
    protected string $_action = 'request';

    /**
     * @var ?string
     */
    protected ?string $_actionUri = null;

    /**
     * @param string $_name
     * @param array $_options (optional)
     */
    public function __construct(string $_name, array $_options = [])
    {
        parent::__construct($_name, $_options);
        ! isset($_options['action']) ?: $this->setAction($_options['action']);
        ! isset($_options['actionUri']) ?: $this->setActionUri($_options['actionUri']);
    }

    /**
     * Set action type
     * - available values: request|submit|redirect|separator
     *
     * @param string $_action
     *
     * @return Button
     */
    public function setAction(string $_action): Button
    {
        $this->_action = $_action;
        return $this;
    }

    /**
     * Get action type
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->_action;
    }

    /**
     * Set button action uri
     *
     * @param string $_actionUri
     *
     * @return Button
     */
    public function setActionUri(string $_actionUri): Button
    {
        $this->_actionUri = $_actionUri;
        return $this;
    }

    /**
     * Set button action uri
     *
     * @return ?string
     */
    public function getActionUri(): ?string
    {
        return $this->_actionUri;
    }

}
