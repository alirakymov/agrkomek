<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper;

use Qore\ORM;

class Provider implements ProviderInterface
{
    /**
     * mm
     *
     * @var ORM\ModelManager
     */
    protected $mm = null;

    /**
     * mappers
     *
     * @var Mapper[]
     */
    protected $mappers = [];

    /**
     * initialized
     *
     * @var mixed
     */
    protected $initialized = [];

    /**
     * __construct
     *
     */
    public function __construct()
    {
    }

    /**
     * registerMapper
     *
     * @param Mapper $_mapper
     */
    public function registerMapper(Mapper $_mapper) : void
    {
        $_mapper->setProvider($this);
        if (! $_mapper->hasModelManager() && ! is_null($this->mm)) {
            $_mapper->setModelManager($this->mm);
        }

        $this->mappers[] = $_mapper;
    }

    /**
     * initialize
     *
     * @param ORM\ModelManager $_mm
     */
    public function initialize(ORM\ModelManager $_mm = null) : void
    {
        if (! is_null($_mm)) {
            $this->mm = $_mm;
        }

        foreach ($this->mappers as $mapper) {
            # - Skip initialized mapper
            if (in_array($mapper->getNamespace(), $this->initialized)) {
                continue;
            }

            $mapper->setModelManager($this->mm);
            $mapper->initReferences();
            $mapper->initSubscribes();

            $this->initialized[] = $mapper->getNamespace();
        }
    }

    /**
     * get
     *
     */
    public function get(string $_entity) : Mapper
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->has($_entity)) {
                return $mapper;
            }
        }

        throw new Exception\UnknownMapper(vsprintf('Undefined mapper for entity (%s)', [$_entity]));
    }
}
