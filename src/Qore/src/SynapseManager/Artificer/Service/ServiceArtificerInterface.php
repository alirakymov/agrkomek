<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Service;

use Psr\Http\Message\ServerRequestInterface;
use Qore\SynapseManager\Artificer;
use Qore\Middleware\Action\ActionMiddlewareInterface;
use Psr\Http\Server\MiddlewareInterface;
use Qore\SynapseManager\Artificer\Form\FormArtificerInterface;

/**
 * Interface: ServiceArtificerInterface
 *
 * @see Artificer\ArtificerInterface
 */
interface ServiceArtificerInterface extends Artificer\ArtificerInterface, ActionMiddlewareInterface, MiddlewareInterface
{
    /**
     * Get form artificer by given name
     *
     * @param string $_formName
     *
     * @return \Qore\SynapseManager\Artificer\Form\FormArtificerInterface|null
     */
    public function getFormArtificer(string $_formName) : ?FormArtificerInterface;

 }
