<?php

declare(strict_types=1);

namespace Qore\Debug;

use DebugBar\StandardDebugBar;
use Psr\Log\LogLevel;

class DebugBar extends StandardDebugBar
{
    /**
     * message
     *
     * @param mixed $_message
     * @param mixed $_logLevel
     */
    public function message($_message, $_logLevel = LogLevel::INFO)
    {
        $this['messages']->addMessage($_message, $_logLevel);
    }
}
