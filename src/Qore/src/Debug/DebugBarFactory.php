<?php

declare(strict_types=1);

namespace Qore\Debug;

use Qore\Qore;
use Psr\Container\ContainerInterface;

class DebugBarFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : DebugBar
    {
        $debugBar = new DebugBar();
        $debugBar->setStorage(new \DebugBar\Storage\FileStorage(Qore::config('qore.paths.debuglog-files')));

        $renderer = $debugBar->getJavascriptRenderer();
        $renderer->disableVendor('fontawesome');
        $renderer->setOpenHandlerUrl(Qore::config('qore.desk-path') . '/debug');

        return $debugBar;
    }
}
