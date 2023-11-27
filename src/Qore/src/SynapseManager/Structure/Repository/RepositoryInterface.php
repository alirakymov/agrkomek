<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;

use Qore\SynapseManager\Artificer;

interface RepositoryInterface
{
    /**
     * get
     *
     * @param string $_artificerName
     */
    public function get(string $_artificerName) : ?ArtificerInterface;

    /**
     * set
     *
     * @param Artificer\ArtificerInterface $_artificer
     */
    public function set(ArtificerInterface $_artificer) : void;

    /**
     * loadSubjects
     *
     */
    public function loadSubjects() : void;

}
