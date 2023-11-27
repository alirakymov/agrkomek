<?php

namespace Qore\Template\Twig;

use Mezzio\Twig\TwigExtension as MezzioTwigExtension;
use Qore\Qore;
use Qore\Debug\DebugBar;
use Mezzio\Twig\TwigEnvironmentFactory as ZendTwigEnvironmentFactory;
use Twig\Loader;
use Psr\Container\ContainerInterface;

class TwigEnvironmentFactory
{
    /**
     * @param ContainerInterface $container
     * @return TwigEnvironment
     * @throws Exception\InvalidConfigException for invalid config service values.
     */
    public function __invoke(ContainerInterface $_container)
    {
        $environment = (new ZendTwigEnvironmentFactory())($_container);
        # - Get all registered loaders and append default loader
        $loaders = $this->getAppLoaders($_container)
            ->appendItem($environment->getLoader());
        # - Set ChainLoader with all loaders
        $environment->setLoader(new Loader\ChainLoader($loaders->toList()));
        # - Set cache
        $environment->setCache(Qore::config('twig.cache_dir', false));
        # - Set global vars
        $environment->addGlobal('DebugBar', Qore::service(DebugBar::class)->getJavascriptRenderer());

        $extension = $_container->get(MezzioTwigExtension::class);
        if ($extension instanceof TwigExtension) {
            $extension->setRenderer($environment);
        }

        return $environment;
    }

    /**
     * getAppLoaders
     *
     * @param ContainerInterface $_container
     */
    private function getAppLoaders(ContainerInterface $_container)
    {
        return Qore::collection(Qore::config('twig.loaders', []))->map(function($_loaderClass) use ($_container) {
            return $_container->get($_loaderClass);
        });
    }

}
