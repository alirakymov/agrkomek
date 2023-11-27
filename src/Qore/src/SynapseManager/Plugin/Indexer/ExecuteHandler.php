<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

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
            case $_model->isMapping():
                return $this->_handler->map($_model);
            case $_model->isIndexing():
                return $this->_handler->index($_model);
            case $_model->isSearch():
                return $this->_handler->search($_model);
        }
    }

}
