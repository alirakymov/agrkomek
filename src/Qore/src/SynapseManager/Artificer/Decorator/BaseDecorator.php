<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Decorator;

use Qore\Collection\CollectionInterface;
use Qore\SynapseManager\Artificer\ArtificerInterface;

/**
 * Class: BaseDecorator
 *
 */
abstract class BaseDecorator
{
    /**
     * artificer
     *
     * @var mixed
     */
    protected $artificer = null;

    /**
     * model
     *
     * @var mixed
     */
    protected $model = null;

    /**
     * filters
     *
     * @var mixed
     */
    protected $filters = null;

    /**
     * options
     *
     * @var mixed
     */
    protected $options = [];

    /**
     * __construct
     *
     */
    public function __construct()
    {
        # - Construct
    }

    /**
     * initialize
     *
     * @param ArtificerInterface $_artificer
     */
    public function initialize(ArtificerInterface $_artificer, array $_options = []) : BaseDecorator
    {
        $this->artificer = $_artificer;
        $this->options = $_options;
        $this->model = $_artificer->getModel()->snapshot();
        $this->filters = $this->model->getFilters(true);
        return $this;
    }

    /**
     * build
     *
     * @param mixed $_data
     */
    public function build($_data = null)
    {
        if (is_null($this->artificer)) {
            throw new DecoratorException(sprintf(
                "Please initialize decorator with artificer (%s\:\:initialize) object before build decoration!",
                static::class
            ));
        }

        return $this->buildDecoration($_data);
    }

    /**
     * getOption
     *
     * @param mixed $_param
     * @param mixed $_default
     */
    protected function getOption($_param, $_default = null)
    {
        $config = $this->options;
        $_param = explode('.', $_param);

        foreach ($_param as $paramKey) {
            if (isset($config[$paramKey])) {
                $config = $config[$paramKey];
            } else {
                return $_default;
            }
        }

        return $config;
    }

    /**
     * buildDecoration
     *
     * @param mixed $_data
     */
    abstract public function buildDecoration($_data = null);

}
