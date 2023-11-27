<?php
/**
 * @see       https://github.com/mezzio/mezzio-twigrenderer for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/mezzio/mezzio-twigrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Qore\Template\Twig;

use Qore\UploadManager\UploadManager;
use Qore\ImageManager\ImageManager;
use Psr\Container\ContainerInterface;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use Mezzio\Twig\TwigRendererFactory;
use Mezzio\Twig\Exception\InvalidConfigException;
use Psr\Http\Message\ServerRequestInterface;
use Qore\App\Middlewares\CsrfGuardMiddleware;

use function sprintf;

class TwigExtensionFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container) : TwigExtension
    {
        if (! $container->has(ServerUrlHelper::class)) {
            throw new InvalidConfigException(sprintf(
                'Missing required `%s` dependency.',
                ServerUrlHelper::class
            ));
        }

        if (! $container->has(UrlHelper::class)) {
            throw new InvalidConfigException(sprintf(
                'Missing required `%s` dependency.',
                UrlHelper::class
            ));
        }

        $config = $container->has('config') ? $container->get('config') : [];
        $config = TwigRendererFactory::mergeConfig($config);

        $request = $container->has(ServerRequestInterface::class) 
            ? $container->get(ServerRequestInterface::class) 
            : fn() => null;

        $request = $request();

        return new TwigExtension(
            $container->get(ServerUrlHelper::class),
            $container->get(UrlHelper::class),
            $container->get(UploadManager::class),
            $container->get(ImageManager::class),
            $request,
            $config['assets_url'] ?? '',
            $config['global_assets_url'] ?? '',
            $config['assets_version'] ?? '',
            $this->prepareGlobals($container, $request, $config)
        );
    }

    /**
     * prepareGlobals
     *
     * @param ContainerInterface $_container
     * @param ServerRequestInterface|null $_request [TODO:description]
     * @param mixed $_config
     *
     * @return array 
     */
    private function prepareGlobals(ContainerInterface $_container, ?ServerRequestInterface $_request, $_config): array
    {
        $extends = [];
        if (isset($_config['global_objects'])) {
            foreach ($_config['global_objects'] as $key => $class) {
                $extends[$key] = $_container->get($class);
            }
        }

        return array_merge($_config['globals'] ?? [], $extends);
    }

}
