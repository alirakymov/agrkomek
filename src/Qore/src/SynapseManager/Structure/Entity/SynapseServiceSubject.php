<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity;
use Qore\ORM\Mapper\Reference\Reference;

/**
 * Class: SynapseServiceSubject
 *
 * @see Entity\Entity
 */
class SynapseServiceSubject extends Entity\Entity
{
    const RELATION_DELIMETER = ':';
    const RELATION_TYPE_AUTO = 0;
    const RELATION_TYPE_FROM = 1;
    const RELATION_TYPE_TO   = 2;

    /**
     * prepareDataToForm
     *
     */
    public function prepareDataToForm()
    {
        $relationType = $this->relationType;
        if ((int)$this->relationType === self::RELATION_TYPE_AUTO
            && isset($this['relation'], $this['serviceFrom']['synapse'])
        ) {
            $relationType = (int)$this->relation->iSynapseFrom === (int)$this->serviceFrom->synapse->id
                ? self::RELATION_TYPE_FROM
                : self::RELATION_TYPE_TO;
        }

        $this['relatedSynapseService'] = isset($this['iSynapseRelation'], $this['iSynapseServiceTo'])
            ? implode(static::RELATION_DELIMETER, [$this['iSynapseRelation'], $this['iSynapseServiceTo'], $relationType])
            : null;

        return $this;
    }

    /**
     * calcRelationType
     *
     */
    public function calcRelationType()
    {
        $relationType = $this->relationType;
        if ((int)$this->relationType === self::RELATION_TYPE_AUTO
            && isset($this['relation'], $this['serviceFrom']['synapse'])
        ) {
            $relationType = (int)$this->relation->iSynapseFrom === (int)$this->serviceFrom->synapse->id
                ? self::RELATION_TYPE_FROM
                : self::RELATION_TYPE_TO;
        }

        return (int)$relationType;
    }

    /**
     * getReferenceName
     *
     */
    public function getReferenceName()
    {
        return $this->calcRelationType() === self::RELATION_TYPE_TO
            ? $this->relation->synapseAliasTo
            : $this->relation->synapseAliasFrom;
    }

    /**
     * initReferenceMetadata
     *
     */
    public function initReferenceMetadata(Entity\EntityInterface $_entity)
    {
        list($serviceFrom, $serviceTo) = $this->calcRelationType() === self::RELATION_TYPE_TO
            ? [$this->serviceTo->id, $this->serviceFrom->id]
            : [$this->serviceFrom->id, $this->serviceTo->id];

        $_entity->_iSynapseServiceFrom($serviceFrom);
        $_entity->_iSynapseServiceTo($serviceTo);
    }

    /**
     * Get relation type
     *
     * @return bool
     */
    public function isToOne(): bool
    {
        if ((int)$this->relation->type == Reference::O2O) {
            return true;
        } elseif ((int)$this->relation->type == Reference::M2M) {
            return false;
        }

        return $this->calcRelationType() !== self::RELATION_TYPE_FROM;
    }

    /**
     * getLabel
     *
     */
    public function getLabel()
    {
        return isset($this['serviceTo'], $this['serviceTo']['synapse'], $this['relation'])
            ? sprintf(
                '%s(%s)::%s',
                $this->serviceTo->synapse->name,
                $this->relation[$this->calcRelationType() === $this::RELATION_TYPE_FROM ? 'synapseAliasFrom' : 'synapseAliasTo'],
                $this->serviceTo->name)
            : sprintf('Subject:%s (Relation:%s)', $this->id, $this->iSynapseRelation);
    }

    /**
     * getRightNameIdentifier
     *
     */
    public function getRightNameIdentifier()
    {
        return $this->serviceTo->synapse->name . ':' . $this->serviceTo->name;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($e){
            $entity = $e->getTarget();
            if (isset($entity['relatedSynapseService'])) {
                list($entity['iSynapseRelation'], $entity['iSynapseServiceTo'], $entity['relationType']) = explode(
                    static::RELATION_DELIMETER,
                    $entity['relatedSynapseService']
                );
            }
        });
    }

}
