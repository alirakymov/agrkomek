<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Filter;

use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class ExecuteHandler implements ExecutableInterface
{
    /**
     * @var HandlerInterface
     */
    private $_handler;

    /**
     * Constructor
     *
     * @param HandlerInterface $_handler
     */
    public function __construct(HandlerInterface $_handler)
    {
        $this->_handler = $_handler;
    }

    /**
     * Execute mapping
     *
     * @param ModelInterface $_model
     *
     * @return bool
     */
    public function execute(ModelInterface $_model) : bool
    {
        switch (true) {
            case $_model->isBuild():
                return $this->_handler->build($_model);
        }
    }

}
