<?php

declare(strict_types=1);

namespace Qore\ORM\Gateway;

use Qore\ORM;
use Qore\ORM\Entity;
use Qore\ORM\Sql;
use Qore\Collection\Collection;
use Cake\Collection\CollectionInterface;
use Closure;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql as ZendSql;
use Laminas\Db\ResultSet\ResultSet;
use Qore\ORM\Entity\SoftDeleteInterface;
use Qore\ORM\Gateway\Exception\UnknownProcessor;
use Qore\ORM\Mapper\MapperInterface;
use Qore\ORM\Sql\Select;
use Qore\Qore;

/**
 * Class: Gateway
 *
 * @see GatewayInterface
 */
class Gateway implements GatewayInterface
{
    const ROOT_PROCESSOR_ALIAS = '@root';

    /**
     * count
     *
     * @var int
     */
    private static $count = 0;

    /**
     * entity
     *
     * @var string
     */
    protected $entity = null;

    /**
     * mm
     *
     * @var mixed
     */
    protected $mm = null;

    /**
     * processor
     *
     * @var mixed
     */
    protected $processor = null;

    /**
     * mapper
     *
     * @var MapperInterface 
     */
    protected MapperInterface $mapper;

    /**
     * mapper
     *
     * @var MapperInterface 
     */
    protected Adapter $adapter;

    /**
     * processors
     *
     * @var mixed
     */
    protected $processors = [];

    /**
     * processorPathPreffix
     *
     * @var mixed
     */
    protected $processorPathPreffix = null;

    /**
     * select
     *
     * @var mixed
     */
    protected $select = null;

    /**
     * selectCursors
     *
     * @var mixed
     */
    protected $selectCursors = [];

    /**
     * where
     *
     * @var mixed
     */
    protected $where = null;

    /**
     * whereCursors
     *
     * @var mixed
     */
    protected $whereCursors = [];

    /**
     * with deleted
     *
     * @var bool 
     */
    protected bool $withDeleted = false;

    /**
     * repository
     *
     * @var ProcessorRepository
     */
    protected ?ProcessorRepository $repository = null;

    /**
     * isPrepared
     *
     * @var mixed
     */
    protected $isPrepared = false;

    /**
     * debug flag 
     *
     * @var bool 
     */
    private bool $debug = false;

    /**
     * __construct
     *
     * @param mixed $_entity
     * @param ORM\ModelManager $_mm
     */
    public function __construct($_entity, ORM\ModelManager $_mm)
    {
        $this->mm = $_mm;

        # - If entity is instance of EntityInterface
        if (is_object($_entity)) {
            if ($_entity instanceof Entity\EntityInterface) {
                $this->entity = $_entity->getEntityName();
                $repository = $this->mm->getProcessorRepository($this->entity);
                $repository->set($_entity);
            } elseif ($_entity instanceof CollectionInterface && $_entity->count()) {
                $entities = $_entity;
                $_entity = $entities->first();
                $this->entity = $_entity->getEntityName();
                $repository = $this->mm->getProcessorRepository($this->entity);
                foreach ($entities as $entity) {
                    $repository->set($entity);
                }
            } else {
                throw new Exception\UnknownEntity(vsprintf('Entity object (%s) must be instance of %s', [get_class($_entity), Entity\EntityInterface::class]));
            }
        # - If entity is string of entity name
        } else {
            $this->entity = $_entity;
            $repository = $this->mm->getProcessorRepository($this->entity);
        }

        $this->repository = $repository;
        $this->mapper = $this->mm->getMapper($this->entity);
        $this->adapter = $this->mm->getAdapter();
        $this->processor = new TableProcessor(
            $this->mapper->getTable($this->entity),
            $repository,
            $this,
            $this->mm,
            self::ROOT_PROCESSOR_ALIAS
        );

        $this->initialize();
    }

    /**
     * initilize
     *
     */
    public function initialize(): void
    {
        # - Init where object
        $this->where = new Sql\Where();
        $this->where->setGateway($this);

        # - Init select object
        $this->select = new Sql\Select();
        $this->select->setGateway($this);
        $this->select->where($this->where);

        # - Init processor path preffix
        $this->processorPathPreffix = self::ROOT_PROCESSOR_ALIAS;
        $this->processors[$this->processorPathPreffix] = $this->processor;

        # - If repository initilized with entities
        if ($this->processor->getRepository()->count()) {
            $this->initReferencesFromEntities($this->processor->getRepository()->getAll());
        }
    }

    /**
     * getTableGateway
     *
     * @param string $_tableName
     */
    public function getTableGateway(string $_tableName = null) : TableGateway
    {
        if (is_null($_tableName)) {
            $_tableName = $this->processor->getTableName();
        }

        return new TableGateway(
            $_tableName,
            $this->mm->getAdapter(),
            null,
            new ResultSet(ResultSet::TYPE_ARRAY),
            (new Sql\Sql($this->mm->getAdapter(), $_tableName))->setGateway($this)
        );
    }

    /**
     * with
     *
     * @param string $_reference
     * @param \Closure $_callback
     */
    public function with(string $_reference, \Closure $_callback=null) : GatewayInterface
    {
        $processorPath = $this->prepareProcessorPath($_reference);

        $referenceProcessor = new ReferenceProcessor(
            $this->processor->getReference($_reference),
            null,
            $this->processor,
            $this,
            $this->mm,
            $processorPath
        );

        $this->processors[$processorPath] = $referenceProcessor;

        if ($_callback) {
            $_callback(new GatewayCursor($this, $this, $referenceProcessor));
        }

        return $this;
    }

    /**
     * Check for soft delete strategy
     *
     * @return bool 
     */
    public function isSoftDelete(): bool
    {
        return in_array(
            SoftDeleteInterface::class, 
            class_implements($this->mm->getMapper($this->entity)->getEntityClass($this->entity))
        );
    }

    /**
     * select
     *
     * @param callable $_select
     */
    public function select(\Closure $_select = null, $_processorPath = null)
    {
        if (! is_null($_select)) {
            # - Get initialized cursor
            $gatewayCursor = $this->getSelectCursor($_processorPath);
            # - Process select closure
            $_select($gatewayCursor);

            return $this;
        }

        return $this->select;
    }

    /**
     * Build conditions for current subject
     *
     * @param Closure|array $_where (optional)
     * @param  $_processorPath (optional)
     *
     * @return Gateway
     */
    public function where($_where = null, $_processorPath = null)
    {
        if (! is_null($_where)) {
            # - Get initialized cursor
            $gatewayCursor = $this->getWhereCursor($_processorPath);
            # - Process where closure
            is_array($_where)
                ? (fn ($_w) => $_w($_where))($gatewayCursor)
                : $_where($gatewayCursor);

            return $this;
        }

        return $this->where;
    }

    public function getSqlString()
    {
        $this->prepareSelect();
        $tgw = $this->getTableGateway($this->getRootProcessor()->getTableName());
                
        return $tgw->getSql()->buildSqlString($this->select);
    }

    /**
     * all
     *
     */
    public function all()
    {
        # - Prepare sql with all references
        $this->prepareSelect();

        if ($this->isSoftDelete() && ! $this->withDeleted) {
            $this->where(function($_where) {
                $_where(['@this.__deleted' => null]);
            });
        }
        
        $result = Qore::measure(sprintf('ORM: Execute sql statement %s', $this->entity), function() {
            return $this->getTableGateway($this->getRootProcessor()->getTableName())
                ->selectWith($this->select());
        });

        # - Execute sql statement and parse results
        $this->parseResult($result);

        # - Save return array of entities
        $return = $this->compareEntities($this->getRootProcessor()->getRepository()->getAll());

        # - Flush repositories
        $this->flushRepositories();

        return $return;
    }

    /**
     * Get prepared select query string
     *
     * @return string
     */
    public function getSelectQuery(): string
    {
        # - Prepare sql with all references
        $this->prepareSelect();

        if ($this->isSoftDelete()) {
            $this->where(function($_where) {
                $_where(['@this.__deleted' => null]);
            });
        }
        
        return $this->getTableGateway($this->getRootProcessor()->getTableName())
            ->buildSqlString($this->select());
    }

    /**
     * Retrive prepared select
     *
     * @return \Qore\ORM\Sql\Select
     */
    public function getPreparedSelect(): Select
    {
        # - Prepare sql with all references
        $this->prepareSelect();

        $sql = new Sql\Sql($this->mm->getAdapter());
        $sql->setGateway($this);
        $sql->replaceReferencePathsWithAliases($this->select);

        return $this->select;
    }

    /**
     * count
     *
     */
    public function count()
    {
        $this->prepareSelect();

        $this->select(function($_select) {
            $_select->columns([
                'count' => new ZendSql\Expression(
                    'COUNT(*)'
                )
            ], true);
        });

        $result = $this->getTableGateway($this->getRootProcessor()->getTableName())
            ->selectWith($this->select);
        $return = $result->current()['count'];

        return $return;
    }

    /**
     * one
     *
     */
    public function one()
    {
        return $this->all()->first();
    }

    /**
     * save
     *
     */
    public function save()
    {
        foreach ($this->processors as $processor) {
            $processor->saveEntities();
        }

        $this->saveReferences();

        return $this->compareEntities($this->processor->getRepository()->getAll());
    }

    /**
     * unset
     *
     */
    public function unset()
    {
        foreach ($this->processors as $processor) {
            $processor->getRepository()->unset();
        }
    }

    /**
     * saveReferences
     *
     */
    public function saveReferences()
    {
        foreach ($this->processors as $processorPath => $processor) {
            # - If namespace of processor relevant for current namespace
            if (! $this->matchProcessorPath($processorPath)) {
                continue;
            }

            # - Save current processor
            $currentProcessor = $this->processor;
            $this->processor = $processor;
            # - Save current path preffix
            $currentProcessorPathPreffix = $this->processorPathPreffix;
            $this->processorPathPreffix = $processorPath;
            # - Run entity compare
            $processor->saveReferences($currentProcessor->getInsertIdMap());
            # - Restore processor
            $this->processor = $currentProcessor;
            # - Restore reference path preffix
            $this->processorPathPreffix = $currentProcessorPathPreffix;
        }
    }

    /**
     * With deleted entities
     *
     * @param bool|null $_bool (optional)
     *
     * @return Gateway
     */
    public function withDeleted(?bool $_bool = null): Gateway
    {
        $_bool ??= true;

        $this->withDeleted = $_bool;
        return $this;
    }

    /**
     * delete
     *
     * @param mixed $_withReferencedEntities
     */
    public function delete($_withReferencedEntities = false)
    {
        if (! is_null($this->repository) && $this->repository->count() > 0) {
            if ($_withReferencedEntities) {
                $processors = array_slice($this->processors, 1);
                foreach ($processors as $processor) {
                    $processor->getRepository()->flush();
                }
            }
            return $this->deleteReferences();
        } elseif (count($this->where->getPredicates()) > 0) {
            if ($this->isSoftDelete()) {
                $this->getTableGateway()->softDelete($this->where);
            } else {
                $this->getTableGateway()->delete($this->where);
            }
        }
    }

    /**
     * deleteReferences
     *
     * @param Select $_select
     */
    public function deleteReferences(ZendSql\Select $_select = null): void
    {
        if ($_select === null) {
            $this->processor->deleteEntities();
            return;
        }

        $references = $this->processor->getTableReferences();
        foreach ($references as $referenceName => $reference) {
            # - Continue if this is parent reference
            if ($this->processor->getReferenceHash() === $reference->getReferenceHash()) {
                continue;
            }
            # - prepare processor path
            $processorPath = $this->prepareProcessorPath($referenceName);
            # - create reference processor
            $referenceProcessor = new ReferenceProcessor(
                $reference,
                null,
                $this->processor,
                $this,
                $this->mm,
                $processorPath
            );
            # - save reference processor
            $this->processors[$processorPath] = $referenceProcessor;
            # - Save current last reference and set new
            $currentProcessor = $this->processor;
            $this->processor = $referenceProcessor;
            # - Save current reference path preffix and set new
            $currentProcessorPathPreffix = $this->processorPathPreffix;
            $this->processorPathPreffix = $processorPath;
            # - delete references
            $result = $this->processor->deleteEntities($_select);
            # - TODO: fixit, comapare this direction with transaction feature
            if (! $result) { return; }
            # - Restore current subjects
            $this->processor = $currentProcessor;
            $this->processorPathPreffix = $currentProcessorPathPreffix;
        }
    }

    /**
     * getRepository
     *
     */
    public function getRepository()
    {
        return $this->processor->getRepository();
    }

    /**
     * getReferenceContract
     *
     */
    public function getProcessor() : ReferenceProcessorInterface
    {
        return $this->processor;
    }

    /**
     * getRootProcessor
     *
     */
    public function getRootProcessor()
    {
        $this->processorPathPreffix = self::ROOT_PROCESSOR_ALIAS;
        return $this->processors[$this->processorPathPreffix];
    }

    /**
     * getReferencePathPreffix
     *
     */
    public function getProcessorPathPreffix() : string
    {
        return $this->processorPathPreffix;
    }

    /**
     * initReferencesFromEntities
     *
     */
    public function initReferencesFromEntities(array $_entities) : void
    {
        $references = $this->processor->getTableReferences();
        foreach ($references as $referenceName => $reference) {
            $referenceEntities = $unlinkedEntities = [];
            foreach ($_entities as $entity) {
                if (isset($entity[$referenceName]) && $entity->isWatched($referenceName)) {
                    if (is_object($entity[$referenceName]) && $entity[$referenceName] instanceof Entity\Entity) {
                        $referenceEntities[$entity['id']] = [$entity[$referenceName]];
                    } elseif (is_object($entity[$referenceName]) && $entity[$referenceName] instanceof Collection) {
                        $referenceEntities[$entity['id']] = $entity[$referenceName]->toList();
                    } else {
                        $referenceEntities[$entity['id']] = $entity[$referenceName];
                    }
                }

                if ($unlinked = $entity->unlinkedEntities($referenceName)) {
                    $unlinkedEntities[$entity['id']] = $unlinked;
                }
            }

            if (! $referenceEntities && ! $unlinkedEntities) {
                continue;
            }

            $processorPath = $this->prepareProcessorPath($referenceName);

            $referenceProcessor = new ReferenceProcessor(
                $reference,
                null,
                $this->processor,
                $this,
                $this->mm,
                $processorPath
            );

            $this->processors[$processorPath] = $referenceProcessor;

            # - Save current last reference and set new
            $currentProcessor = $this->processor;
            $this->processor = $referenceProcessor;

            # - Save current reference path preffix and set new
            $currentProcessorPathPreffix = $this->processorPathPreffix;
            $this->processorPathPreffix = $processorPath;

            if ($referenceEntities) {
                # - Save entities to reference entities
                $this->processor->setEntitiesToRepository($referenceEntities);
                # - Recursive init reference entities
                $allReferenceEntities = [];
                foreach ($referenceEntities as $entities) {
                    $entities = is_object($entities) && $entities instanceof CollectionInterface
                        ? $entities->toList()
                        : $entities;
                    $allReferenceEntities = array_merge($allReferenceEntities, $entities);
                }
                $this->initReferencesFromEntities($allReferenceEntities);
            }

            if ($unlinkedEntities) {
                # - Set unlinked entities
                $this->processor->unlinkEntities($unlinkedEntities);
            }

            # - Restore current subjects
            $this->processor = $currentProcessor;
            $this->processorPathPreffix = $currentProcessorPathPreffix;
        }
    }

    /**
     * compareEntities
     *
     */
    public function compareEntities(array $_entities = []) : Collection
    {
        $processors = array_slice($this->processors, 1);
        foreach ($_entities as $entity) {
            foreach ($processors as $processorPath => $reference) {
                # - check if that reference relate for this referencePathPreffix
                if (! $this->matchProcessorPath($processorPath)) {
                    continue;
                }
                # - Save current path preffix
                $currentProcessorPathPreffix = $this->processorPathPreffix;
                $this->processorPathPreffix = $processorPath;
                # - Run entity compare
                $reference->compareEntity($entity);
                # - Restore reference path preffix
                $this->processorPathPreffix = $currentProcessorPathPreffix;
            }
        }

        return new Collection(array_values($_entities));
    }

    /**
     * fromCursor
     *
     * @param callable $_callback
     * @param GatewayCursor $_cursor
     */
    public function fromCursor(callable $_callback, GatewayCursor $_cursor)
    {
        # - Save current last reference and set new
        $currentProcessor = $this->processor;
        $this->processor = $_cursor->getGatewayProcessor();

        # - Save current reference path preffix and set new
        $currentProcessorPathPreffix = $this->processorPathPreffix;
        $this->processorPathPreffix = $this->processor->getProcessorPath();

        $result = $_callback($this);

        # - Restore current subjects
        $this->processor = $currentProcessor;
        $this->processorPathPreffix = $currentProcessorPathPreffix;

        return $result;
    }

    /**
     * getWhereCursor
     *
     * @param mixed $_processorPath
     */
    public function getWhereCursor($_processorPath = null)
    {
        $_processorPath = $this->prepareProcessorPathForCursor($_processorPath);

        if (! isset($this->processors[$_processorPath])) {
            throw new UnknownProcessor(sprintf('Processor for %s is not registered', $_processorPath));
        }

        if (! isset($this->whereCursors[$_processorPath])) {
            $this->whereCursors[$_processorPath] = new GatewayCursor($this, $this->where, $this->processors[$_processorPath]);
            # - Register after call action
            $this->whereCursors[$_processorPath]->_afterCall(function($_method, $_args) {
                $this->where->handleCursors();
            });
        }

        return $this->whereCursors[$_processorPath];
    }

    /**
     * getSelectCursor
     *
     * @param mixed $_processorPath
     */
    public function getSelectCursor($_processorPath = null)
    {
        $_processorPath = $this->prepareProcessorPathForCursor($_processorPath);

        if (! isset($this->processors[$_processorPath])) {
            throw new UnknownProcessor(sprintf('Processor for %s is not registered', $_processorPath));
        }

        if (! isset($this->selectCursors[$_processorPath])) {
            $this->selectCursors[$_processorPath] = new GatewayCursor($this, $this->select, $this->processors[$_processorPath]);
            # - Register after call action
            $this->selectCursors[$_processorPath]->_afterCall(function($_method, $_args) {
                $this->select->handleCursors();
            });
        }

        return $this->selectCursors[$_processorPath];
    }

    /**
     * prepareSql
     *
     */
    public function prepareSelect()
    {
        # - Prepare references
        $processorPathPreffixes = [];
        foreach ($this->processors as $processorPath => $referenceProcessor) {
            $processorPathPreffixes[$processorPath] = $referenceProcessor;
            $currentProcessor = $this->processor;
            $this->processor = $processorPathPreffixes[
                substr($processorPath, 0, strripos($processorPath, '.') ?: strlen($processorPath))
            ];

            $referenceProcessor->prepareSelect();

            # - Restore current last reference
            $this->processor = $currentProcessor;
        }


        return $this->select;
    }

    /**
     * Build prepared select
     *
     * @return \Qore\ORM\Sql\Select
     */
    public function buildSelect(): Select
    {
        $this->prepareSelect();
        $sql = (new Sql\Sql($this->mm->getAdapter(), $this->processor->getTableName()))
            ->setGateway($this);

        $sql->replaceReferencePathsWithAliases($this->select);
        return $this->select;
    }

    /**
     * Get processors subject alias replacements
     *
     * @param bool $_useAlias (optional) - use table true:alias or false:name
     *
     * @return array
     */
    public function getProcessorsReplacements(bool $_useAlias = true): array
    {
        # - Prepare where sql
        foreach ($this->processors as $processorPath => $referenceProcessor) {
            $return = array_merge(
                $return ?? [],
                $referenceProcessor->getProcessorReplacements($processorPath, $_useAlias)
            );
        }

        krsort($return);
        return $return;
    }

    /**
     * __clone
     *
     */
    public function __clone()
    {
        # - Clone all construct objects
        $this->where = clone $this->where;
        $this->where->setGateway($this);

        $this->select = clone $this->select;
        $this->select->where($this->where);
        $this->select->setGateway($this);

        # - Clone processors
        foreach ($this->processors as $processorPath => $processor) {
            ($this->processors[$processorPath] = clone $processor)->setGateway($this);
        }

        # - Set gateway cursor to end
        $this->processor = $this->processors[$this->processorPathPreffix];

        $this->selectCursors = [];
        $this->whereCursors = [];
    }

    /**
     * parseResult
     *
     * @param mixed $_result
     */
    protected function parseResult($_result) : void
    {
        foreach ($_result as $row) {
            foreach ($this->processors as $processor) {
                Qore::measure(sprintf('ORM: Parse Result %s', $this->entity), function() use ($processor, $row) {
                    $processor->parseResult($row);
                });
            }
        }
    }

    /**
     * prepareProcessorPathForCursor
     *
     * @param mixed $_processorPath
     */
    protected function prepareProcessorPathForCursor($_processorPath)
    {
        return $_processorPath ?? $this->processor->getProcessorPath();
    }

    /**
     * prepareReferenceContact
     *
     */
    protected function prepareProcessorPath(string $_reference) : string
    {
        return $this->processorPathPreffix . '.' . $_reference;
    }

    /**
     * matchReferencePath
     *
     * @param string $_referencePath
     */
    protected function matchProcessorPath(string $_processorPath) : bool
    {
        return $this->processorPathPreffix === substr($_processorPath, 0, strripos($_processorPath, '.') ?: strlen($_processorPath));
    }

    /**
     * flushRepositories
     *
     */
    protected function flushRepositories(): Gateway
    {
        foreach ($this->processors as $reference) {
            $reference->flushRepository();
        }

        return $this;
    }

    /**
     * Set debug mode on
     *
     * @return Gateway
     */
    public function debug(): Gateway
    {
        $this->debug = true;
        return $this;
    }

    /**
     * Dump
     *
     * @param $m 
     *
     * @return void
     */
    public function dump($m): void
    {
        $this->debug && dump($m);
    }

}
