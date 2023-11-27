<?php

declare(strict_types=1);

namespace Qore\Front\Protocol;

use Qore\Front\Protocol\Component\QCBaseComponent;

/**
 * Class: BaseProtocol
 *
 * @see ProtocolInterface
 * @abstract
 */
abstract class BaseProtocol implements ProtocolInterface
{
    /**
     * options
     *
     * @var array
     */
    protected $options = [];

    /**
     * name
     *
     * @var string
     */
    protected $name = null;

    /**
     * title
     *
     * @var mixed
     */
    protected $title = null;

    /**
     * type
     *
     * @var mixed
     */
    protected $type = null;

    /**
     * actions
     *
     * @var mixed
     */
    protected $actions = [];

    /**
     * components
     *
     * @var array
     */
    protected $components = [];

    /**
     * componentRuns
     *
     * @var mixed
     */
    protected $componentRuns = [];

    /**
     * registeredComponents
     *
     * @var mixed
     */
    protected static $registeredComponents = [];

    /**
     * @var string|null
     */
    private ?string $parent = null;

    /**
     * get
     *
     * @param string $_name
     * @param array $_options
     * @return $this
     */
    final public static function get(string $_name, array $_options = [])
    {

        if (! isset(self::$registeredComponents[$_name])) {
            self::$registeredComponents[$_name] = (new static($_name))->setOptions($_options);
        }

        return self::$registeredComponents[$_name];
    }

    /**
     * __construct
     *
     * @param string $_name
     */
    public function __construct(string $_name)
    {
        $this->name = $_name;
        // $this->options['componentName'] = $_name;
    }

    /**
     * in
     *
     * @param mixed $_component
     */
    public function in($_component)
    {
        if ($_component instanceof ProtocolInterface) {
            $_component = $_component->getName();
        }

        if (! isset(self::$registeredComponents[$_component])) {
            self::$registeredComponents[$_component] = QCBaseComponent::get($_component);
        }

        return self::$registeredComponents[$_component]->component($this);
    }

    /**
     * setOptions
     *
     * @param array $_options
     */
    public function setOptions(array $_options)
    {
        if (isset($_options['type'])) {
            $this->type = $_options['type'];
            unset($_options['type']);
        }

        $this->options = $_options;
        return $this;
    }

    /**
     * setActions
     *
     * @param array $_actions
     */
    public function setActions(array $_actions)
    {
        $this->actions = $_actions;
        return $this;
    }

    /**
     * setTitle
     *
     * @param string $_title
     */
    public function setTitle(string $_title)
    {
        $this->options['title'] = $_title;
        return $this;
    }

    /**
     * inBlock
     *
     */
    public function inBlock(bool $_inBlock)
    {
        $this->options['inBlock'] = $_inBlock;
        return $this;
    }

    /**
     * setBreadcrumbs
     *
     * @param array $_breadcrumbs
     */
    public function setBreadcrumbs(array $_breadcrumbs)
    {
        $this->options['breadcrumbs'] = $_breadcrumbs;
        return $this;
    }

    /**
     * component
     *
     * @param ProtocolInterface $_component
     * @return $this
     */
    public function component($_component)
    {
        $this->components[] = $_component;
        return $this;
    }

    /**
     * redirect
     *
     * @param string $_uri
     */
    public function redirect(string $_url)
    {
        $this->run('redirect', ['url' => $_url]);
        return $this;
    }

    /**
     * setOption
     *
     * @param mixed $_name
     * @param mixed $_value
     */
    public function setOption($_name, $_value)
    {
        $this->options[$_name] = $_value;
        return $this;
    }

    /**
     * run
     *
     * @param string $_commandName
     * @param array $_commandData
     */
    public function run(string $_commandName, array $_commandData = [])
    {
        $this->componentRuns[] = [
            'command' => $_commandName,
            'options' => $_commandData,
        ];

        return $this;
    }

    /**
     * Set parent component name
     *
     * @param string $_name
     *
     * @return ComponentInterface
     */
    public function setParent(string $_name)
    {
        $this->parent = $_name;
        return $this;
    }

    /**
     * Get parent component name
     *
     * @return string|null
     */
    public function getParent() : ?string
    {
        return $this->parent;
    }

    /**
     * asArray
     *
     */
    public function asArray()
    {
        $component = array_merge([
            'is' => 'component',
            'name' => $this->getName(),
            'parent' => $this->getParent(),
            'type' => $this->getType(),
            'merge-strategy' => 'concat',
            'component-actions' => $this->getActions(),
        ], $this->options);

        $result = [ $component ];

        if (! is_null($this->components)) {
            foreach ($this->components as $component) {
                $component->setParent($this->name);
                $result = array_merge(
                    $result,
                    $component instanceof ProtocolInterface ? $component->asArray() : $component->compose()
                );
            }
        }

        foreach ($this->componentRuns as $options) {
            $result[] = [
                'is' => 'command',
                'name' => $this->getName(),
                'command' => $options['command'],
                'options' => $options['options'] ?? [],
            ];
        }

        return $result;
    }

    /**
     * asArray
     *
     */
    public function asArrayOld()
    {
        $baseOptions = [
            'type' => $this->getType(),
            'name' => $this->getName(),
            'component-actions' => $this->getActions(),
            'components' => [],
            'component-runs' => [],
        ];

        if ($this->title) {
            $baseOptions['component-title'] = $this->title;
        }

        $return = array_merge($baseOptions, $this->options);

        foreach ($this->components as $component) {
            $return['components'][] = $component instanceof ProtocolInterface
                ? $component->asArray()
                : $component->compose();
        }

        foreach ($this->componentRuns as $commandName => $commandData) {
            $return['component-runs'][$commandName] = $commandData;
        }

        return $return;
    }

    /**
     * getName
     *
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * setName
     *
     */
    public function setName(string $_name)
    {
        $this->name = $_name;
        return $this;
    }

    /**
     * getType
     *
     */
    public function getType()
    {
        return $this->type;
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
     * getComponents
     *
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * getType
     *
     */
    public function getActions()
    {
        return $this->actions;
    }

}
