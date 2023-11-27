<?php

declare(strict_types=1);

use Mezzio\Helper\ServerUrlHelper;
use Psr\Http\Message\UriFactoryInterface;
use Qore\Qore;
use Qore\QoreConsole;
use Qore\Console\Commands\DumpServer;
use Symfony\Component\Console\Application;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
(function ($_loader) {
    # - Initialize Qore application
    Qore::init(require QORE_CONFIG_PATH . DS . 'container.php');
    # - Initialize Qore Console Application
    $application = Qore::service(QoreConsole::class);
    # - Application run
    $application->run();

})($loader);
