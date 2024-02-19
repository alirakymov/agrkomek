<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\ApiPrivate;

use Mezzio\Authentication\UserInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\Services\Tracking\TrackingInterface;
use Qore\SynapseManager\SynapseManager;

/**
 * Class: RoutesMiddleware
 *
 * @see BaseMiddleware
 */
class UserDeviceRegisterMiddleware implements MiddlewareInterface
{
    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param DelegateInterface $_handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $mm = Qore::service(ModelManager::class);
        $sm = Qore::service(SynapseManager::class);

        $user = $_request->getAttribute(UserInterface::class);

        $user = $mm('SM:User')
            ->with('devices')
            ->where(['@this.phone' => $user->getIdentity()])
            ->one();

        if (! $user) {
            return $_handler->handle($_request);
        }

        $deviceIdentifier = $_request->getHeader('agro-device-id');

        if (! $deviceIdentifier) {
            return $_handler->handle($_request);
        }

        $deviceIdentifier = current($deviceIdentifier);

        $device = $user->devices()->firstMatch([
            'token' => $deviceIdentifier,
        ]);

        if (is_null($device)) {
            $device = $mm('SM:Device', [
                'token' => $deviceIdentifier,
            ]);
            $device->link('user', $user);
            $mm($device)->save();
        }

        return $_handler->handle($_request);
    }

}

