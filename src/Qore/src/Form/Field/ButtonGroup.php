<?php

namespace Qore\Form\Field;

class ButtonGroup extends Field
{
    protected $type = self::TYPE_BUTTON_GROUP;

    /**
     * @var array<Button> - array of buttons
     *
     */
    protected array $buttons = [];

    /**
     * @param string $_name
     * @param array $_options (optional)
     */
    public function __construct(string $_name, array $_options = [])
    {
        parent::__construct($_name, $_options);
        # - Set buttons from options
        foreach ($_options['buttons'] ?? [] as $button) {
            $this->setButton($button);
        }
    }

    /**
     * Set button to group of buttons
     *
     * @param Button $_button
     *
     * @return ButtonGroup
     */
    public function setButton(Button $_button): ButtonGroup
    {
        $this->buttons[] = $_button;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdditional()
    {
        $buttons = [];
        foreach ($this->buttons as $button) {
            $buttons[] = [
                'label' => $button->getLabel(),
                'action' => $button->getAction(),
                'actionUri' => $button->getActionUri(),
                'options' => $button->getOptions(),
                'additional' => $button->getAdditional(),
            ];
        }

        return array_merge($this->additional, [ 'buttons' => $buttons, ]);
    }

}
