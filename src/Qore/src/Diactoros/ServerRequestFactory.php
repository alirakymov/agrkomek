<?php

declare(strict_types=1);

namespace Qore\Diactoros;

use Qore\Qore;
use Qore\Database\Adapter\Adapter; use Psr\Container\ContainerInterface;
use Laminas\Diactoros as ZendDiactoros;
use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;

class ServerRequestFactory extends ZendDiactoros\ServerRequestFactory
{
    /**
     * Function to use to get apache request headers; present only to simplify mocking.
     *
     * @var callable
     */
    protected static $apacheRequestHeaders = 'apache_request_headers';

    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : callable
    {
        return function () {
            return static::fromGlobals();
        };
    }

    /**
     * Create a request from the supplied superglobal values.
     *
     * If any argument is not supplied, the corresponding superglobal value will
     * be used.
     *
     * The ServerRequest created is then passed to the fromServer() method in
     * order to marshal the request URI and headers.
     *
     * @see fromServer()
     * @param array $server $_SERVER superglobal
     * @param array $query $_GET superglobal
     * @param array $body $_POST superglobal
     * @param array $cookies $_COOKIE superglobal
     * @param array $files $_FILES superglobal
     * @return ServerRequest
     * @throws InvalidArgumentException for invalid file values
     */
    public static function fromGlobals(
        ?array $server = null,
        ?array $query = null,
        ?array $body = null,
        ?array $cookies = null,
        ?array $files = null,
        ?FilterServerRequestInterface $requestFilter = null
    ): ServerRequest {
        $server = ZendDiactoros\normalizeServer(
            $server ?: $_SERVER,
            is_callable(self::$apacheRequestHeaders) ? self::$apacheRequestHeaders : null
        );
        $files   = ZendDiactoros\normalizeUploadedFiles($files ?: $_FILES);
        $headers = ZendDiactoros\marshalHeadersFromSapi($server);

        if (null === $cookies && array_key_exists('cookie', $headers)) {
            $cookies = ZendDiactoros\parseCookieHeader($headers['cookie']);
        }

        $request = new ServerRequest(
            $server,
            $files,
            ZendDiactoros\marshalUriFromSapi($server, $headers),
            ZendDiactoros\marshalMethodFromSapi($server),
            'php://input',
            $headers,
            $cookies ?: $_COOKIE,
            $query ?: $_GET,
            $body ?: $_POST,
            ZendDiactoros\marshalProtocolVersionFromSapi($server)
        );
        return $request->withUri($request->getUri()->withScheme('https'));
    }

}
