<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\FormMaker;

use Psr\Container\ContainerInterface;
use Qore\DealingManager\DealingManager;
use Qore\Elastic\Elastic;
use Qore\SynapseManager\Plugin\Indexer\Indexer;

class FormMakerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container) : FormMaker
    {
        return new FormMaker();
    }

}
