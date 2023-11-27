<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity;
use Qore\ORM\Mapper\Reference\Reference;
use Qore\ORM\Mapper\Table\Column;

/**
 * Class: SynapseAttribute
 *
 * @see Entity\Entity
 */
class SynapseRelation extends Entity\Entity
{
    /**
     * getTypes
     *
     */
    public static function getTypes()
    {
        return [
            Reference::O2O => [
                'label' => 'One-To-One'
            ],
            Reference::O2M => [
                'label' => 'One-To-Many'
            ],
            Reference::M2M => [
                'label' => 'Many-To-Many'
            ],
        ];
    }

    /**
     * isO2O
     *
     */
    public function isO2O() : bool
    {
        return (int)$this->type === Reference::O2O;
    }

    /**
     * isO2M
     *
     */
    public function isO2M() : bool
    {
        return (int)$this->type === Reference::O2M;
    }

    /**
     * isO2M
     *
     */
    public function isM2M() : bool
    {
        return (int)$this->type === Reference::O2M;
    }

    /**
     * isToOne
     *
     * @param mixed $_regardToSynapseID
     */
    public function isToOne($_regardToSynapseID) : bool
    {
        if ((int)$this->type == Reference::O2O) {
            return true;
        } elseif ((int)$this->type == Reference::M2M) {
            return false;
        }

        return (int)$_regardToSynapseID == (int)$this->iSynapseFrom;
    }

    /**
     * isToMany
     *
     */
    public function isToMany()
    {
        return ! $this->isToOne();
    }

}
