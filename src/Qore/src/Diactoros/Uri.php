<?php

declare(strict_types=1);

namespace Qore\Diactoros;

use Laminas\Diactoros\Uri as DiactorosUri;

/**
 * Implementation of Psr\Http\UriInterface.
 *
 * Provides a value object representing a URI for HTTP requests.
 *
 * Instances of this class  are considered immutable; all methods that
 * might change state are implemented such that they retain the internal
 * state of the current instance and return a new instance that contains the
 * changed state.
 */
class Uri extends DiactorosUri
{
    /**
     * @var int[] Array indexed by valid scheme names to their corresponding ports.
     */
    protected $allowedSchemes = [
        'http'  => 80,
        'https' => 443,
        'chrome-extension' => 80,
    ];
}
