<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Form;

use Qore\SynapseManager\Artificer;
use Qore\Middleware\Action\ActionMiddlewareInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Interface: FormArtificerInterface
 *
 * @see Artificer\ArtificerInterface
 * @see MiddlewareInterface
 */
interface FormArtificerInterface extends Artificer\ArtificerInterface, ActionMiddlewareInterface, MiddlewareInterface
{
}
