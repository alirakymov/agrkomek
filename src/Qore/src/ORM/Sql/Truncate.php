<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Qore\ORM\Sql;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\AbstractPreparableSql;

/**
 *
 * @property Where $where
 */
class Truncate extends AbstractPreparableSql
{
    /**@#+
     * @const
     */
    const SPECIFICATION_TRUNCATE = 'truncate';
    /**@#-*/

    /**
     * {@inheritDoc}
     */
    protected $specifications = [
        self::SPECIFICATION_TRUNCATE => 'TRUNCATE TABLE %1$s',
    ];

    /**
     * @var string|TableIdentifier
     */
    protected $table = '';

    /**
     * Constructor
     *
     * @param  null|string|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->from($table);
        }
    }

    /**
     * Create from statement
     *
     * @param  string|TableIdentifier $table
     * @return self Provides a fluent interface
     */
    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getRawState($key = null)
    {
        $rawState = [
            'table' => $this->table,
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @return self Provides a fluent interface
     */
    public function where($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * @param PlatformInterface       $platform
     * @param DriverInterface|null    $driver
     * @param ParameterContainer|null $parameterContainer
     *
     * @return string
     */
    protected function processTruncate(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        return sprintf(
            $this->specifications[static::SPECIFICATION_TRUNCATE],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer)
        );
    }

}
