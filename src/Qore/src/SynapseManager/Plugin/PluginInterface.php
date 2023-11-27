<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin;

use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface;
use Qore\SynapseManager\SynapseManager;

Interface PluginInterface
{
    /**
     * Set synapse manager instance
     *
     * @param SynapseManager $_sm
     *
     * @return void
     */
    public function setSynapseManager(SynapseManager $_sm) : void;

    /**
     * Set artificer instance
     *
     * @param \Qore\SynapseManager\Artificer\ArtificerInterface $_artificer
     *
     * @return void
     */
    public function setArtificer(ArtificerInterface $_artificer) : void;

}
