<?php

declare(strict_types=1);

namespace Qore\Debug;

use Qore\Qore;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\Console\Commands\DumpServer;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class: ExecutionMeasureMiddleware
 *
 * @see MiddlewareInterface
 */
class ExecutionMeasureMiddleware implements MiddlewareInterface
{
    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param RequestHandlerInterface $_handler
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        # - Initialize debugbar
        $debugBar = Qore::service(DebugBar::class);
        # - Start Measurement
        $debugBar['time']->startMeasure('application', 'Application execution time');
        # - Register VarDumper handler
        // $this->registerVarDumper();
        # - Launch Application
        $return = $_handler->handle($_request);
        # - Fix debugbar datac
        $debugBar['time']->stopMeasure('application');
        $debugBar->sendDataInHeaders(true);
        # - return result
        return $return;
    }

    /**
     * registerVarDumper
     *
     */
    protected function registerVarDumper()
    {
        $locker = Qore::service(DumpServer::class)->getLocker();
        if (! $locker->acquire()) {
            $cloner = new VarCloner();
            $fallbackDumper = new HtmlDumper();
            $dumper = new ServerDumper(Qore::config('dump-server.host'), $fallbackDumper, [
                'cli' => new CliContextProvider(),
                'source' => new SourceContextProvider(),
            ]);

            VarDumper::setHandler(function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var)->withMaxDepth(40));
            });
        } else {
            $locker->release();
        }
    }

}
