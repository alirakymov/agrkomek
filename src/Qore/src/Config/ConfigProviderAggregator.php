<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace Qore\Config;

use Laminas\ConfigAggregator\ConfigAggregator;


/**
 * Provide a collection of PHP files returning config arrays.
 */
class ConfigProviderAggregator extends ConfigAggregator
{
    /**
     * configProviders
     *
     * @var mixed
     */
    private $configProviders = [];

    /**
     * config
     *
     * @var mixed
     */
    private $config = [];

    /**
     * __construct
     *
     * @param array|string $_configProviders
     */
    public function __construct($_configProviders)
    {
        if (is_string($_configProviders) && is_file($_configProviders)) {
            $_configProviders = include $_configProviders;
        }

        if (! is_array($_configProviders)) {
            return;
        }

        $configAggregator = new ConfigAggregator($_configProviders);
        $this->config = $configAggregator->getMergedConfig();
    }

    /**
     * __invoke
     *
     */
    public function __invoke() : array
    {
        return $this->config;
    }

}
