<?php

declare(strict_types=1);

use Qore\Console\Commands\DumpServer;
use Qore\Qore;

// Delegate static file requests back to the PHP built-in webserver
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
(function ($_loader) {
    # Initialize Qore application
    Qore::init(require QORE_CONFIG_PATH . DS . 'container.php');

    $profiler = null;
    # - Register dump server if it's debug mode
    if (Qore::config('debug', false)) {
        Qore::service(DumpServer::class)->initFallbackDumper();
        if (isset($_GET['q-profile'])) {
            $profiler = new \Xhgui\Profiler\Profiler(
                new \Xhgui\Profiler\Config(Qore::config('profiler', []))
            );
            $profiler->start();
        }
    }

    # - Execute programmatic/declarative middleware pipeline and routing
    # - configuration statements
    (require QORE_CONFIG_PATH . DS . 'pipeline.php')(
        Qore::app(),
        Qore::service(\Mezzio\MiddlewareFactory::class),
        Qore::container()
    );

    Qore::app()->run();

    if ($profiler) {
        /* // stop profiler */
        /* $profilerData = $profiler->disable(); */
        /* // send $profiler_data to saver */
        /* $profiler->save($profilerData); */
    }

})($loader);
