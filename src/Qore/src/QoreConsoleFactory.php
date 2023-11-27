<?php

/**
 * @see       https://github.com/mezzio/mezzio for the canonical source repository
 * @copyright Copyright (c) 2015-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/mezzio/mezzio/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Qore;

use Mezzio\Helper\ServerUrlHelper;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriFactoryInterface;
use Qore\Console\Commands\DumpServer;

/**
 * Create an Qore Console Application instance.
 *
 */
class QoreConsoleFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : QoreConsole
    {
        $application = new QoreConsole('Welcome to Qore Framework Console Application');

        $config = $container->get('config');
        # - Initialize ServerUrlHelper with global project URI
        $container->get(ServerUrlHelper::class)
            ->setUri($container->get(UriFactoryInterface::class)
                ->createUri($config['qore']['project']['uri'] ?? '')
            );
        # - Initialize commands
        $commands = $config['console']['commands'] ?? [];
        foreach ($commands as $command) {
            $application->add($container->get($command));
        }

        if ($config['debug'] ?? false) {
            $container->get(DumpServer::class)->initFallbackDumper();
        }

        return $application;
    }

}
