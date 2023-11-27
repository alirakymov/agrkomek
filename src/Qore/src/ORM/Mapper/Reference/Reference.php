<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper\Reference;

use Qore\Collection\Collection;
use Qore\ORM\Gateway;
use Qore\ORM\Entity\EntityInterface;
use Qore\ORM\Mapper\Table;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Predicate;

class Reference implements ReferenceInterface
{
    /**
     * OneToOne reference type
     */
    const O2O = 1;

    /**
     * OneToMany reference type
     */
    const O2M = 2;

    /**
     * ManyToMany reference type
     */
    const M2M = 3;

    /**
     * ToOne reference object type
     */
    const TOONE = 1;

    /**
     * ToMany reference object type
     */
    const TOMANY = 2;

    /**
     * referenceHash
     *
     * @var mixed
     */
    protected $referenceHash = null;
    /**
     * referenceName
     *
     * @var mixed
     */
    protected $referenceName = null;

    /**
     * _referenceMap
     *
     * @var mixed
     */
    protected $referenceMap = null;

    /**
     * referenceType
     *
     * @var mixed
     */
    protected $referenceType = null;

    /**
     * decoreateReferenceType
     *
     * @var mixed
     */
    protected $decorateReferenceType = null;

    /**
     * conditions
     *
     * @var mixed
     */
    protected $conditions = [];

    /**
     * strict
     *
     * @var mixed
     */
    protected $strictMode = false;

    /**
     * unqiuePreffix
     *
     * @var mixed
     */
    protected $unqiuePreffix = null;

    /**
     * __construct
     *
     * @param string $_referenceHash
     * @param string $_referenceName
     * @param array $_referenceMap
     * @param int $_referenceType
     * @param array $_conditions
     * @param bool $_strictMode
     */
    public function __construct(
        string $_referenceHash,
        string $_referenceName,
        array $_referenceMap,
        int $_referenceType,
        array $_conditions = [],
        bool $_strictMode = false,
        int $_decorateReferenceType = null
    ) {
        $this->referenceHash = $_referenceHash;
        $this->referenceName = $_referenceName;
        $this->referenceMap = $_referenceMap;
        $this->referenceType = $_referenceType;
        $this->conditions = $_conditions;
        $this->strictMode = $_strictMode;
        $this->decorateReferenceType = $_decorateReferenceType ?? $_referenceType;
    }

    /**
     * getReferenceHash
     *
     */
    public function getReferenceHash()
    {
        return $this->referenceHash;
    }

    /**
     * getReferenceName
     *
     */
    public function getReferenceName()
    {
        return $this->referenceName;
    }

    /**
     * getReferenceMap
     *
     */
    public function getReferenceMap()
    {
        return $this->referenceMap;
    }

    /**
     * getReferenceType
     *
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }

    /**
     * getDecorateReferenceType
     *
     */
    public function getDecorateReferenceType()
    {
        return $this->decorateReferenceType;
    }

    /**
     * getConditions
     *
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * getStrictMode
     *
     */
    public function getStrictMode() : bool
    {
        return $this->strictMode;
    }

}
