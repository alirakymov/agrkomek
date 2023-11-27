<?php

declare(strict_types=1);

namespace Qore\Desk\Actions;

use Mezzio\Router\RouteResult;
use Qore\Qore;
use Qore\App\Actions\ManagerIndex;
use Psr\Http\Message\ServerRequestInterface;

trait BaseActionNavButtonGroupTrait
{
    /**
     * getNavpills
     *
     */
    protected function getNavButtonGroup(ServerRequestInterface $_request)
    {
        return [
            'buttons' => $this->getNavButtons(),
        ];
    }

    /**
     * getNavButtons
     *
     */
    protected function getNavButtons()
    {
        return [
            [
                'label' => 'Очистить кэш',
                'icon' => 'glyphicon glyphicon-erase',
                'actionUri' => Qore::url(Qore::service(CacheCleaner::class)->routeName('index')),
            ],
        ];
    }

}
