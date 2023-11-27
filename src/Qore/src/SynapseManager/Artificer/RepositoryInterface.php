<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;

interface RepositoryInterface
{
    /**
     * get
     *
     * @param string $_artificerName
     */
    public function get(string $_artificerName) : ?ArtificerInterface;

}
