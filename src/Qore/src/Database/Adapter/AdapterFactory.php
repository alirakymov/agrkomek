<?php

declare(strict_types=1);

namespace Qore\Database\Adapter;

use Qore\Qore;
use Interop\Container\ContainerInterface;

/**
 * Class: AdapterFactory
 *
 */
class AdapterFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container)
    {
        return new Adapter(Qore::config('db'));
    }

}
