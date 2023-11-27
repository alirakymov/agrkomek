<?php
/**
 * @see       https://github.com/mezzio/mezzio for the canonical source repository
 * @copyright Copyright (c) 2015-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/mezzio/mezzio/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Qore\Console\Commands;

use Qore\Qore;
use Psr\Container\ContainerInterface;
use Mezzio\ApplicationPipeline;
use Mezzio\MiddlewareFactory;
use Symfony\Component\VarDumper\Server\DumpServer as SymfonyDumpServer;

/**
 * Class: ApplicationFactory
 *
 */
class DumpServerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : DumpServer
    {
        return new DumpServer(new SymfonyDumpServer(Qore::config('dump-server.host')));
    }
}
