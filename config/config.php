<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use Qore\Config\ConfigProviderAggregator;

function touch_dir($_path)
{
    if (is_array($_path)) {
        $_path = implode(DS, $_path);
    }

    if (is_dir($_path) || mkdir($_path, 0755, true)) {
        return realpath($_path);
    } else {
        throw new Exception(sprintf('Directory or file not found: %s', $_path));
    }
}

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = array_merge(
    ['config_cache_path' => touch_dir(PROJECT_STORAGE_PATH . DS . 'cache') . DS .  'config-cache.php'],
    IS_CLI ? [ConfigAggregator::ENABLE_CACHE => false] : []
);

$aggregator = new ConfigAggregator([
    \Mezzio\Authentication\OAuth2\ConfigProvider::class,
    \Laminas\Serializer\ConfigProvider::class,
    \Laminas\Log\ConfigProvider::class,
    \Laminas\Cache\ConfigProvider::class,
    \Laminas\Session\ConfigProvider::class,
    \Laminas\Form\ConfigProvider::class,
    \Laminas\InputFilter\ConfigProvider::class,
    \Laminas\Filter\ConfigProvider::class,
    \Laminas\Hydrator\ConfigProvider::class,
    \Laminas\Validator\ConfigProvider::class,
    \Laminas\Db\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,
    \Mezzio\Authentication\Basic\ConfigProvider::class,
    \Mezzio\Authentication\ConfigProvider::class,
    \Mezzio\Authentication\OAuth2\ConfigProvider::class,
    \Mezzio\Session\Ext\ConfigProvider::class,
    \Mezzio\Flash\ConfigProvider::class,
    \Mezzio\Session\ConfigProvider::class,
    \Mezzio\Helper\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Mezzio\Twig\ConfigProvider::class,
    \Mezzio\Cors\ConfigProvider::class,
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Qore\ConfigProvider::class,
    \Qore\Csrf\ConfigProvider::class,
    \Qore\Console\ConfigProvider::class,
    \Qore\Desk\ConfigProvider::class,
    \Qore\App\ConfigProvider::class,
    \Qore\Config\ConfigProvider::class,
    \Qore\SynapseManager\ConfigProvider::class,
    \Qore\InterfaceGateway\ConfigProvider::class,
    \Qore\Sanitizer\ConfigProvider::class,
    \Qore\QueueManager\ConfigProvider::class,
    \Qore\NotifyManager\ConfigProvider::class,
    \Qore\App\SynapseNodes\Components\Moderator\Authentication\ConfigProvider::class,
    # - Config provider aggregator for submodules
    new ConfigProviderAggregator(QORE_CONFIG_PATH . '/config.providers.php'),
    # - Global Framework configs
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{,*.}global.php'),
    # - Local Framework configs
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{,*.}local.php'),
    # - Include cache configuration
    new ArrayProvider($cacheConfig),
    # - Development mode configuration
    new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path'], [\Laminas\ZendFrameworkBridge\ConfigPostProcessor::class]);

return $aggregator->getMergedConfig();
