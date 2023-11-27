<?php

namespace Qore\Desk\Actions;

use Laminas\Diactoros\Response\RedirectResponse;

use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Qore\Desk\Actions\BaseAction;
use Qore\Daemon\Supervisor;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

/**
 * Class: Services
 *
 * @see BaseAction
 */
class Debugger extends BaseAction
{
    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/debugger', null, function ($_router) {
            $_router->any('/[{clear: clear}]', 'index');
        });
    }

    /**
     * run
     *
     */
    protected function run()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        switch (true) {
            case $routeResult->getMatchedRouteName() === $this->routeName('index'):
                return $this->runIndex();
        }
    }

    /**
     * runIndex
     *
     */
    protected function runIndex()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $file = Qore::config('dump-server.html-file');

        if (isset($routeParams['clear'])) {
            file_exists($file) && file_put_contents($file, '');
            return new RedirectResponse(Qore::url($this->routeName('index'), ['clear' => null]));
        }

        return new HtmlResponse($this->template->render('app::debugger', [
            'title' => 'Qore Debug Console',
            'clear_url' => Qore::url($this->routeName('index'), ['clear' => 'clear']),
            'content' => file_exists($file) ? file_get_contents($file) : '',
        ]));
    }

}
