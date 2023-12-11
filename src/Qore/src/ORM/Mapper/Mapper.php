<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper;


use Qore\ORM\ModelManager;
use Qore\ORM\Entity;
use Qore\Database\Adapter\Adapter;
use Laminas\Db\Sql;
use Laminas\Db\Sql\Ddl;

/**
 * Class: Mapper
 *
 * @see MapperInterface
 */
class Mapper implements MapperInterface
{
    /**
     * driver
     *
     * @var Driver\DriverInterface
     */
    protected $driver = null;

    /**
     * namespace
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * provider
     *
     * @var mixed
     */
    protected $provider = null;

    /**
     * structure
     *
     * @var mixed
     */
    protected $structure = [];

    /**
     * mm
     *
     * @var mixed
     */
    protected $mm = null;

    /**
     * namespaceSeparator
     *
     * @var string
     */
    protected $namespaceSeparator = '_';

    /**
     * initializedSubscribes
     *
     * @var mixed
     */
    protected $initializedSubscribes = [];

    /**
     * __construct
     *
     * @param string $_namespace
     * @param Mapper\Driver\DriverInterface $_driver
     */
    public function __construct(string $_namespace, Driver\DriverInterface $_driver)
    {
        $this->namespace = $_namespace;
        $this->driver = $_driver;
        $this->driver->setMapper($this);
        $this->initTables();
    }

    /**
     * getNamespace
     *
     */
    public function getNamespace() : string
    {
        return $this->namespace;
    }

    /**
     * setProvider
     *
     * @param Provider $_provider
     */
    public function setProvider(Provider $_provider) : void
    {
        $this->provider = $_provider;
    }

    /**
     * hasModelManager
     *
     */
    public function hasModelManager() : bool
    {
        return ! is_null($this->mm);
    }

    /**
     * setModelManager
     *
     * @param ModelManager $_mm
     */
    public function setModelManager(ModelManager $_mm) : void
    {
        $this->mm = $_mm;
    }

    /**
     * setStructure
     *
     * @param array $_structure
     */
    public function setStructure(array $_structure) : void
    {
        $this->structure = $_structure;
    }

    /**
     * initTables
     *
     */
    public function initTables() : void
    {
        $this->driver->prepareTables();
    }

    /**
     * initReferences
     *
     */
    public function initReferences() : void
    {
        $this->driver->prepareReferences();
    }

    /**
     * initSubsribes
     *
     */
    public function initSubscribes() : void
    {
        foreach ($this->structure as $tableName => $table) {
            if (in_array($table->getEntityClass(), $this->initializedSubscribes)) {
                continue;
            }
            $table->getEntityClass()::initSubscribes($this->mm->getEventManager());
            $this->initializedSubscribes[] = $table->getEntityClass();
        }
    }

    /**
     * getStructure
     *
     */
    public function getStructure() : array
    {
        return $this->structure;
    }

    /**
     * has
     *
     * @param string $_entity
     */
    public function has(string $_entity) : bool
    {
        if (class_exists($_entity)) {
            return ! is_null($this->findByClass($_entity));
        } else {
            list($namespace, $entityName) = $this->parseEntityName($_entity);
            return $this->namespace === $namespace && isset($this->structure[$entityName]);
        }
    }

    /**
     * getTable
     *
     * @param string $_entity
     */
    public function getTable(string $_entity) : Table\Table
    {
        list($namespace, $entityName) = $this->parseEntityName($_entity);

        if (! isset($this->structure[$entityName])) {
            return $this->provider->get($_entity)->getTable($_entity);
        }

        return $this->structure[$entityName];
    }

    /**
     * getEntityName
     *
     * @param string $_entity
     */
    public function getEntityName(string $_entity) : string
    {
        if (class_exists($_entity)) {
            $_entity = $this->findByClass($_entity)->getEntityName();
        }

        return implode(':', $this->parseEntityName($_entity));
    }

    /**
     * Find table by class name
     *
     * @param string $_className
     *
     * @return Table\Table|null;
     */
    public function findByClass(string $_className) : ?Table\Table
    {
        $_className = ltrim($_className, '\\');

        if ($_className === ltrim($this->defaultEntityClass(), '\\')) {
            throw new Exception\UnknownTable(sprintf('It is imposible to find a match entity mapping by default class name %s', $_className));
        }

        foreach ($this->structure as $table) {
            if (ltrim($table->getEntityClass(), '\\') === $_className) {
                return $table;
            }
        }

        return null;
    }

    /**
     * getEntityClass
     *
     * @param string $_entityName
     */
    public function getEntityClass(string $_entityName) : string
    {
        return $this->getTable($_entityName)->getEntityClass() ?? $this->defaultEntityClass();
    }

    /**
     * defaultEntityClass
     *
     */
    public function defaultEntityClass() : string
    {
        return Entity\Entity::class;
    }

    /**
     * parseEntityName
     *
     * @param string $_entity
     */
    protected function parseEntityName(string $_entity) : array
    {
        $result = explode(':', $_entity, 2);
        return count($result) === 1 ? array_merge([$this->namespace], $result) : $result;
    }

    /**
     * getDatabaseStructure
     *
     */
    public function getDatabaseStructure() : array
    {
        $tables = $this->loadDatabaseTables();

        foreach ($tables as $key => $table) {
            unset($tables[$key]);
            if ($entityName = $this->convertDBNameToEntityName($table)) {
                $tables[$table] = new Table\Table($table, $entityName, $this->loadColumns($table), $this->loadConstraints($table));
            }
        }

        return $tables;
    }

    /**
     * convertDBNameToEntityName
     *
     * @param mixed $_tableName
     */
    public function convertDBNameToEntityName($_tableName)
    {
        if (stripos($_tableName, $this->namespaceSeparator) === false) {
            return false;
        }

        return substr($_tableName, 0, stripos($_tableName, $this->namespaceSeparator))
            . ':'
            . substr($_tableName, stripos($_tableName, $this->namespaceSeparator)+strlen($this->namespaceSeparator));
    }

    /**
     * convertEntityNameToDBName
     *
     * @param mixed $_entityName
     */
    public function convertEntityNameToDBName($_entityName)
    {
        list($namespace, $tableName) = explode(':', $_entityName);
        return $namespace . $this->namespaceSeparator . $tableName;
    }

    /**
     * loadTables
     *
     */
    protected function loadDatabaseTables() : array
    {
        $sql = "SHOW TABLES";
        $result = $this->mm->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE)->toArray();

        $return = [];
        foreach ($result as $table) {
            $return[] = array_shift($table);
        }

        return $return;
    }

    /**
     * loadColumns
     *
     * @param string $_table
     */
    public function loadColumns(string $_table) : array
    {
        $platform = $this->mm->getAdapter()->getPlatform();
        $sql = "SHOW COLUMNS FROM " . $platform->quoteIdentifierChain($_table);
        $result = $this->mm->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE)->toArray();

        $return = [];
        foreach ($result as $column) {
            $return[$column['Field']] = $this->getColumn($column);
        }

        return $return;
    }

    /**
     * loadConstraints
     *
     * @param string $_table
     */
    public function loadConstraints(string $_table) : array
    {
        $platform = $this->mm->getAdapter()->getPlatform();
        $sql = "SHOW INDEXES FROM " . $platform->quoteIdentifierChain($_table);

        $result = $this->mm->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE)->toArray();

        $indexes = [];
        foreach ($result as $index) {
            if (! isset($indexes[$index['Key_name']])) {
                $indexes[$index['Key_name']] = $index;
                $indexes[$index['Key_name']]['Columns'] = [(int)$index['Seq_in_index'] => $index['Column_name']];
            } else {
                $indexes[$index['Key_name']]['Columns'][(int)$index['Seq_in_index']] = $index['Column_name'];
            }
        }

        $return = [];
        foreach ($indexes as $index) {
            $return[$index['Key_name']] = $this->getConstraint($index);
        }

        return $return;
    }

    /**
     * getColumn
     *
     * @param array $_column
     */
    protected function getColumn(array $_column) : Ddl\Column\ColumnInterface
    {
        if ($_column['Type'] == 'tinyint(1)') {
            dump($_column);
        }
        $type = $this->matchColumnType($_column['Type']);
        $isNull = $_column['Null'] !== 'NO';

        if (is_subclass_of($type['class'], Ddl\Column\AbstractPrecisionColumn::class)) {
            $params = $type['length'] !== null ? explode(',', $type['length'], 2) : [null, null];
            $column = new $type['class']($_column['Field'], $params[0], $params[1] ?? null, $isNull, $_column['Default']);
        } elseif (is_subclass_of($type['class'], Ddl\Column\AbstractLengthColumn::class)) {
            $column = new $type['class']($_column['Field'], $type['length'], $isNull, $_column['Default']);
        } else {
            $column = new $type['class']($_column['Field'], $isNull, $_column['Default']);
        }

        if ($options = $this->matchOptions($_column)) {
            foreach ($options as $optionName => $optionValue) {
                $column->setOption($optionName, $optionValue);
            }
        }

        return $column;
    }

    /**
     * matchColumnType
     *
     * @param string $_type
     */
    protected function matchColumnType(string $_type) : array
    {
        $typePattern = '/([a-z]+)(?:\(([0-9,]+)\))?/';
        preg_match($typePattern, $_type, $match);

        $return = [];

        switch (true) {
            case $match[1] === 'int':
                $return['class'] = Table\Column\Integer::class;
                break;
            case $match[1] === 'decimal':
                $return['class'] = Table\Column\Decimal::class;
                break;
            case $match[1] === 'smallint':
                $return['class'] = Table\Column\Integer::class;
                break;
            case $match[1] === 'bigint':
                $return['class'] = Table\Column\BigInteger::class;
                break;
            case $match[1] === 'varchar':
                $return['class'] = Table\Column\Varchar::class;
                break;
            case $match[1] === 'text':
                $return['class'] = Table\Column\Text::class;
                break;
            case $match[1] === 'mediumtext':
                $return['class'] = Table\Column\Text::class;
                break;
            case $match[1] === 'longtext':
                $return['class'] = Table\Column\LongText::class;
                break;
            case $match[1] === 'datetime':
                $return['class'] = Table\Column\Datetime::class;
                break;
            case $match[1] === 'timestamp':
                $return['class'] = Table\Column\Timestamp::class;
                break;
            default:
                throw new Exception\UnknownColumnType(sprintf('Unknown column type %s', $_type));
                break;
        }

        $return['length'] = isset($match[2]) ? $match[2] : null;

        return $return;
    }

    /**
     * matchOptions
     *
     * @param array $_column
     */
    protected function matchOptions(array $_column) : array
    {
        $options = [];

        $optionsPattern = '/(unsigned|zerofil|binary|auto_increment)/';
        if (preg_match_all($optionsPattern, $_column['Type'], $matches)) {
            foreach ($matches[1] as $value) {
                $options[$value] = true;
            }
        }

        if (preg_match_all($optionsPattern, $_column['Extra'], $matches)) {
            foreach ($matches[1] as $value) {
                $options[$value] = true;
            }
        }

        return $options;
    }

    /**
     * getConstraint
     *
     * @param array $_index
     */
    protected function getConstraint(array $_index) : Ddl\Constraint\ConstraintInterface
    {
        if ($_index['Key_name'] === 'PRIMARY') {
            $constraintClass = Table\Constraint\PrimaryKey::class;
        } elseif ((int)$_index['Non_unique'] === 0) {
            $constraintClass = Table\Constraint\UniqueKey::class;
        } else {
            $constraintClass = Table\Constraint\Index::class;
        }

        ksort($_index['Columns']);
        return new $constraintClass(array_values($_index['Columns']), $_index['Key_name']);
    }

}
