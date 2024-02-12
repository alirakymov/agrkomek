<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Authentication\Adapter;

use Mezzio\Authentication\Basic\BasicAccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class: AuthenticationAdapter
 *
 * @see AuthenticationInterface
 */
class BasicAuthentication extends BasicAccess implements AuthenticationInterface
{
    /**
     * unauthorizedResponse
     *
     * @param ServerRequestInterface $request
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->responseFactory)($request)
            ->withHeader(
                'WWW-Authenticate',
                sprintf('Basic realm="%s"', $this->realm)
            )
            ->withStatus(401);
    }

}
