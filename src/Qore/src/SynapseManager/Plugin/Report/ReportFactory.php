<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Report;

use Psr\Container\ContainerInterface;
use Qore\DealingManager\DealingManager;
use Qore\ORM\ModelManager;
use Qore\QueueManager\QueueManager;
use Qore\UploadManager\UploadManager;

/**
 * Class: IndexerFactory
 *
 */
class ReportFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container): Report 
    {
        $config = $_container->get('config');
        return new Report(
            $_container->get(DealingManager::class),
            $_container->get(QueueManager::class),
            $_container->get(ModelManager::class),
            $_container->get(UploadManager::class),
            $config['qore']['synapse-configs']['namespaces'] ?? [],
            $config['qore']['synapse-configs']['report'] ?? [],
        );
    }

}
