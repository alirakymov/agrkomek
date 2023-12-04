<?php

declare(strict_types=1);

namespace Qore\Cors\Configuration;

use Mezzio\Cors\Configuration\AbstractConfiguration;
use Webmozart\Assert\Assert;

final class ProjectConfiguration extends AbstractConfiguration
{
    /**
     * @psalm-param list<string> $methods
     */
    public function setAllowedMethods(array $methods): void
    {
        Assert::allString($methods);

        $methods = array_values(array_unique($methods));

        $this->allowedMethods = $methods;
    }

}
