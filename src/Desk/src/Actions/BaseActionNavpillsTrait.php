<?php

declare(strict_types=1);

namespace Qore\Desk\Actions;

use Mezzio\Router\RouteResult;
use Qore\Qore;
use Qore\App\Actions\ManagerIndex;
use Psr\Http\Message\ServerRequestInterface;

trait BaseActionNavpillsTrait
{
    /**
     * getNavpills
     *
     */
    protected function getNavpills(ServerRequestInterface $_request)
    {
        $routeResult = $_request->getAttribute(RouteResult::class);
        $items = $this->getNavpillsItems();

        $explode = explode('/', ltrim($_request->getUri()->getPath(), '/'));
        $firstElement = array_shift($explode);

        foreach ($items as &$item) {
            $explode = explode('/', ltrim($item['url'], '/'));
            $itemFirstElement = array_shift($explode);
            $item['active'] = $firstElement === $itemFirstElement;
        }

        return [
            'items' => $items,
        ];
    }

    /**
     * getItems
     *
     */
    protected function getNavpillsItems()
    {
        return [
            [
                'label' => 'Модератор',
                'url' => Qore::url(Qore::service(ManagerIndex::class)->getRouteName('index')),
            ],
            [
                'label' => 'Администратор',
                'url' => Qore::url(Qore::service(Index::class)->routeName('index')),
            ],
        ];
    }

}
