<?php

declare(strict_types=1);

namespace Qore\InterfaceGateway\Component;

use Qore\InterfaceGateway\InterfaceGateway;

abstract class AbstractComponent implements ComponentInterface
{
    /**
     * name
     *
     * @var string
     */
    protected $name = null;

    /**
     * type
     * @var mixed
     */
    protected $type = null;

    /**
     * @var string
     */
    protected ?string $parent = null;

    /**
     * @var string - Merge strategy ['replace', 'concat', 'clear']
     */
    protected ?string $mergeStrategy = null;

    /**
     * options
     *
     * @var array
     */
    protected $options = [];

    /**
     * components
     *
     * @var array<ComponentInterface>|null
     */
    protected $components = null;

    /**
     * commands
     *
     * @var mixed
     */
    protected $commands = [];

    /**
     * ig
     *
     * @var mixed
     */
    protected $ig = null;

    /** __construct
     *
     * @param string $_name
     * @param array $_options
     */
    public function __construct(string $_name, array $_options = [])
    {
        $this->name = $_name;
        $this->options = $_options;
    }

    /**
     * setInterfaceGateway
     *
     */
    public function setInterfaceGateway(InterfaceGateway $_ig): ComponentInterface
    {
        $this->ig = $_ig;
        return $this;
    }

    /**
     * getName
     *
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * setName
     *
     */
    public function setName(string $_name): ComponentInterface
    {
        $this->name = $_name;
        return $this;
    }

    /**
     * Set parent component name
     *
     * @param string $_name
     *
     * @return ComponentInterface
     */
    public function setParent(string $_name): ComponentInterface
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
     * getType
     *
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * setType
     *
     * @param string $_type
     */
    public function setType(string $_type): ComponentInterface
    {
        $this->type = $_type;
        return $this;
    }

    /**
     * Get option by index or default value
     *
     * @param string $_index
     * @param mixed $_default (optional)
     *
     * @return mixed
     */
    public function getOption(string $_index, $_default = null)
    {
        return $this->getOptions()[$_index] ?? $_default;
    }

    /**
     * getOptions
     *
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    public function addOptions(array $_options) : ComponentInterface
    {
        $this->options = array_merge($this->options, $_options);
        return $this;
    }

    /**
     * setOptions
     *
     * @param array $_options
     */
    public function setOptions(array $_options) : ComponentInterface
    {
        $this->options = $_options;
        return $this;
    }

    /**
     * Set option
     *
     * @param string $_name
     * @param mixed $_value
     *
     * @return ComponentInterface
     */
    public function setOption(string $_name, $_value) : ComponentInterface
    {
        $this->options[$_name] = $_value;
        return $this;
    }

    /**
     * setActions
     *
     * @param array $_actions
     *
     * @return ComponentInterface
     */
    public function setActions(array $_actions) : ComponentInterface
    {
        $this->setOption('component-actions', $_actions);
        return $this;
    }

    /**
     * Get actions
     *
     * @return array
     */
    public function getActions() : array
    {
        return $this->options['component-actions'] ?? [];
    }

    /**
     * setTitle
     *
     * @param string $_title
     */
    public function setTitle(string $_title) : ComponentInterface
    {
        $this->options['title'] = $_title;
        return $this;
    }

    /**
     * Get title string
     *
     * @return ?string
     */
    public function getTitle() : ?string
    {
        return $this->options['title'] ?? null;
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
     * Set child components
     *
     * @param array<ComponentInterface> $_components
     *
     * @return ComponentInterface|array<ComponentInterface>|null
     */
    public function components(array $_components = null)
    {
        if (! is_null($_components)) {
            $this->components = array_values($_components);
            return $this;
        }

        return $this->components;
    }

    /**
     * component
     *
     * @param ComponentInterface $_component
     * @return $this
     */
    public function component(ComponentInterface $_component) : ComponentInterface
    {
        $this->components ??= [];
        $this->components[] = $_component;
        return $this;
    }

    /**
     * Set merge strategy 'replace'|'concat'
     *
     * @param string $_strategy
     *
     * @return ComponentInterface
     */
    public function strategy(string $_strategy) : ComponentInterface
    {
        $this->mergeStrategy = $_strategy;
        return $this;
    }

    /**
     * Get merge strategy
     *
     * @return string
     */
    public function getMergeStrategy() : string
    {
        return $this->mergeStrategy ?? static::CONCAT;
    }

    /**
     * Deprecated: run - old naming from FrontProtocol, use - execute
     *
     * @param string $_command
     * @param array $_options (optional)
     *
     * @return ComponentInterface
     */
    public function run(string $_command, array $_options = []) : ComponentInterface
    {
        $this->execute($_command, $_options);
        return $this;
    }

    /**
     * redirect
     *
     * @param string $_uri
     */
    public function redirect(string $_url): ComponentInterface
    {
        $this->execute('redirect', ['url' => $_url]);
        return $this;
    }

    /**
     * execute
     *
     * @param string $_command
     * @param array $_options
     */
    public function execute(string $_command, array $_options = []): ComponentInterface
    {
        $this->commands[] = [
            'command' => $_command,
            'options' => $_options,
        ];

        return $this;
    }

    /**
     * in
     *
     * @param mixed $_component
     */
    public function in($_component): ComponentInterface
    {
        $name = $_component instanceof ComponentInterface
            ? $_component->getName()
            : $_component;

        return ($this->ig)($name)->component($this);
    }

    /**
     * Place current component to block component;
     *
     * @return ComponentInterface
     */
    public function inBlock() : ComponentInterface
    {
        return ($this->ig)(Block::class, sprintf('%s.%s', $this->getName(), 'block'))
            ->setTitle($this->getTitle())
            ->component($this);
    }

    /**
     * Compose component data to array
     *
     * @return array
     */
    public function compose(): array
    {
        $component = array_merge([
            'is' => 'component',
            'name' => $this->getName(),
            'parent' => $this->getParent(),
            'type' => $this->getType(),
            'merge-strategy' => $this->getMergeStrategy(),
        ], $this->getOptions());

        $result = [ $component ];

        if (! is_null($this->components)) {
            foreach ($this->components as $component) {
                $component->setParent($this->name);
                $result = array_merge($result, $component->compose());
            }
        }

        foreach ($this->commands as $options) {
            $result[] = [
                'is' => 'command',
                'name' => $this->getName(),
                'command' => $options['command'],
                'options' => $options['options'] ?? [],
            ];
        }

        return $result;
    }

}
