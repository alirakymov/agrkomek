<?php

namespace Qore\SynapseManager\Plugin\Designer;

interface ComposableEntityInterface
{

    /**
     * Set unique id for composable block of designer plugin
     *
     * @param string $_unique
     *
     * @return \Qore\ORM\Entity\EntityInterface
     */
    public function setUnique(string $_unique): ComposableEntityInterface;

    /**
     * Get unique id for composable block of designer plugin
     *
     * @return string|null
     */
    public function getUnique(): ?string;

}
