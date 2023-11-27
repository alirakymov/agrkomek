<?php

declare(strict_types=1);

namespace Qore\ORM\Entity;

use ArrayAccess;
use Cake\Collection\CollectionInterface;
use Laminas\EventManager\Event;
use ArrayObject;
use Qore\Collection\Collection;
use Qore\ORM\ModelManager;
use Qore\ORM\Mapper\Mapper;
use Qore\ORM\Mapper\Table;
use Qore\ORM\Mapper\Reference;
use Qore\EventManager\EventManager;
use Qore\Qore;

/**
 * Class: Entity
 *
 * @see EntityInterface
 * @see ArrayObject
 */
class Entity extends ArrayObject implements EntityInterface
{
    /**
     * @var array<String>
     */
    protected array $mutators = [];

    /**
     * entity
     *
     * @var mixed
     */
    protected $entityName = null;

    /**
     * unlinks
     *
     * @var mixed
     */
    protected $unlinks = [];

    /**
     * table
     *
     * @var mixed
     */
    protected $table = null;

    /**
     * isInitialize
     *
     * @var mixed
     */
    protected $isInitialize = false;

    /**
     * subscribes
     *
     * @var mixed
     */
    protected static $subscribes = [];

    /**
     * watchedReferences
     *
     * @var mixed
     */
    protected $watchedReferences = [];

    /**
     * processedEvents
     *
     * @var mixed
     */
    protected $processedEvents = [];

    /**
     * __construct
     *
     * @param string $_entity
     * @param mixed $_input
     * @param int $_flags
     * @param string $_iteratorClass
     */
    public function __construct($_table, $_input = [], int $_flags=0, string $_iteratorClass='ArrayIterator')
    {
        $this->table = $_table;
        $this->entityName = $this->table->getEntityName();
        parent::__construct($_input, $_flags, $_iteratorClass);
        $this->initialize();
    }

    /**
     * initialize
     *
     * @param mixed $_input
     */
    protected function initialize()
    {
        $this->isInitialize = true;

        if (! isset($this['id'])) {
            $this['id'] = uniqid('new:', true);
        }

        if (! isset($this['__idinsert'])) {
            $this['__idinsert'] = null;
        }

        if (! isset($this['__keep'])) {
            $this['__keep'] = true;
        }

        $this->isInitialize = false;
    }

    /**
     * combine
     *
     * @param mixed $_input
     */
    public function combine($_input) : Entity
    {
        $this->isInitialize = true;

        foreach ($_input as $property => $value) {
            if ($property == '__version') {
                continue;
            }
            $this[$property] = $value;
        }

        $this->isInitialize = false;
        return $this;
    }

    /**
     * extend
     *
     * @param mixed $_input
     */
    public function extend($_input) : Entity
    {
        $this->isInitialize = true;

        $entityKeys = array_keys($this->getArrayCopy());
        foreach ($_input as $property => $value) {
            if (! in_array($property, $entityKeys)) {
                $this[$property] = $value;
            }
        }

        $this->isInitialize = false;
        return $this;
    }

    /**
     * setTable
     *
     */
    public function setTable(Table\TableInterface $_table = null) : Entity
    {
        $this->table = $_table;
        return $this;
    }

    /**
     * getTable
     *
     */
    public function getTable() : Table\TableInterface
    {
        return $this->table;
    }

    /**
     * __get
     *
     * @param string $_property
     */
    public function __get(string $_property)
    {
        return $this[$_property] ?? null;
    }

    /**
     * __set
     *
     * @param string $_property
     * @param mixed $_value
     */
    public function __set(string $_property, $_value) : void
    {
        $this[$_property] = $_value;
    }

    /**
     * __invoke
     *
     * @param array $_entity
     * @param bool $_exchange
     */
    public function __invoke(array $_entity, bool $_exchange = false) : Entity
    {
        if ($_exchange) {
            $this->exchangeArray($_entity);
        } else {
            foreach ($_entity as $key => $value) {
                $this[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * __call
     *
     * @param mixed $_name
     * @param mixed $_arguments
     */
    public function __call($_name, $_arguments)
    {
        if (substr($_name, 0, 1) == '_') {
            $this['@reference.' . substr($_name, 1)] = $_arguments[0] ?? null;
        } else {
            if ($this->table->hasReference($_name)) {
                $reference = $this->table->getReference($_name);
                # - Link with new entities
                if ($_arguments) {
                    $this->link($_name, $_arguments[0]);
                # - Get elements
                } else {
                    if ($reference->getDecorateReferenceType() === Reference\Reference::TOONE) {
                        return isset($this[$_name]) && $this[$_name] instanceof CollectionInterface
                            ? $this[$_name]->first()
                            : ($this[$_name] ?? null);
                    } else {
                        return $this[$_name] ?? new Collection([]);
                    }
                }
            } else {
                throw new Exception\EntityException(vsprintf('Undefined method %s in %s class!', [
                    $_name,
                    static::class,
                ]));
            }
        }
    }

    /**
     * Get value from storage
     *
     * @param $_key
     *
     * @return mixed
     */
    public function originalGet(string $_key)
    {
        return isset($this[$_key]) ? parent::offsetGet($_key) : null;
    }

    /**
     * Set value to storage
     *
     * @param string $_key
     * @param mixed $_value
     */
    public function originalSet(string $_key, $_value) : void
    {
        parent::offsetSet($_key, $_value);
    }

    /**
     * offsetGet
     *
     * @param mixed $_key
     */
    public function offsetGet($_key): mixed
    {
        $property = isset($this[$_key]) ? parent::offsetGet($_key) : null;
        if ($this->mutators) {
            foreach ($this->mutators as &$mutator) {
                if (is_string($mutator)) {
                    $mutator = new $mutator($this);
                }
                $property = $mutator->get($property, $_key);
            }
        }

        return $property;
    }

    /**
     * offsetSet
     *
     * @param mixed $_key
     * @param mixed $_value
     */
    public function offsetSet($_key, $_value) : void
    {
        if ($this->mutators) {
            foreach ($this->mutators as &$mutator) {
                if (is_string($mutator)) {
                    $mutator = new $mutator($this);
                }
                $_value = $mutator->set($_value, $_key);
            }
        }

        parent::offsetSet($_key, $_value);
    }

    /**
     * offsetUnset
     *
     * @param mixed $_key
     */
    public function offsetUnset($_key) : void
    {
        // $this['__version']++;
        parent::offsetUnset($_key);
    }

    /**
     * getEntityName
     *
     */
    public function getEntityName() : string
    {
        return $this->entityName;
    }

    /**
     * link
     *
     * @param mixed $_referenceName
     * @param mixed $_entities
     */
    public function link($_referenceName, $_entities) : Entity
    {
        if (is_object($_entities) && $_entities instanceof EntityInterface) {
            $_entities = [$_entities];
        }

        $this->watchedReferences[] = $_referenceName;
        return $this->setRelatedEntities($_referenceName, $_entities);
    }

    /**
     * unlink
     *
     * @param string $_reference
     * @param mixed $_entity
     */
    public function unlink(string $_reference, $_entity) : Entity
    {
        if (isset($this->unlinks[$_reference]) && $this->unlinks[$_reference] === '*') {
            return $this;
        } elseif (! isset($this->unlinks[$_reference])) {
            $this->unlinks[$_reference] = [];
        }

        $iEntity = null;

        if (is_object($_entity) && $_entity instanceof Entity) {
            $this->unlinks[$_reference][] = $iEntity = $_entity['id'];
        }

        if (is_array($_entity) && isset($_entity['id'])) {
            $this->unlinks[$_reference][] = $iEntity = $_entity['id'];
        }

        if (is_scalar($_entity)) {
            if ($_entity === '*') {
                $this->unlinks[$_reference] = $_entity;
                $this[$_reference] = new Collection([]);
                return $this;
            } else {
                $this->unlinks[$_reference][] = $iEntity = $_entity;
            }
        }

        if (! is_null($iEntity)) {
            $this[$_reference] = $this[$_reference]->reject(function($_entity) use ($iEntity) {
                return (int)$_entity['id'] === (int)$iEntity;
            });
        }

        if (is_array($_entity) && ! $this->isNumeric($_entity)) {
            foreach ($_entity as $entity) {
                $this->unlink($_reference, $entity);
            }
        }

        $this->unlinks[$_reference] = array_unique($this->unlinks[$_reference]);

        return $this;
    }

    /**
     * flushUnlinks
     *
     * @param string $_reference
     */
    public function flushUnlinks(string $_reference) : Entity
    {
        if (isset($this->unlinks[$_reference])) {
            unset($this->unlinks[$_reference]);
        }
        return $this;
    }

    /**
     * unlinkedEntities
     *
     * @param mixed $_referenceName
     */
    public function unlinkedEntities($_referenceName) : array
    {
        if (! isset($this->unlinks[$_referenceName])) {
            return [];
        }

        return is_scalar($this->unlinks[$_referenceName])
            ? [$this->unlinks[$_referenceName]]
            : $this->unlinks[$_referenceName];
    }

    /**
     * setRelatedEntities
     *
     * @param mixed $_referenceName
     * @param mixed $_entities
     */
    public function setRelatedEntities($_referenceName, $_entities) : Entity
    {
        $_entities = is_array($_entities) ? new Collection($_entities) : $_entities;
        if ($this->table->getReference($_referenceName)->getReferenceType() === Reference\Reference::TOONE) {
            $this[$_referenceName] = $_entities->first();
        } else {
            $this[$_referenceName] = $this[$_referenceName] ?? new Collection([]);
            is_array($this[$_referenceName]) && $this[$_referenceName] = new Collection($this[$_referenceName]);
            $ids = $this[$_referenceName]->map(function($_entity) {
                return is_object($_entity) ? $_entity['id'] : $_entity;
            })->toList();

            $this[$_referenceName] = $this[$_referenceName]->append($_entities->filter(fn($_entity) => ! in_array($_entity['id'], $ids))->compile());
            /* $currentEntities = array_merge( */
            /*     $this[$_referenceName]->toArray(), */
            /*     ! is_null($_entities) ? $_entities->filter(function($_entity) use ($ids) { return ! in_array($_entity['id'], $ids); })->toArray() : [] */
            /* ); */
            /*  */
            /* $this[$_referenceName] = new Collection($currentEntities); */
        }

        return $this;
    }

    /**
     * clear
     *
     */
    public function clear()
    {
        $entityKeys = array_keys($this->getArrayCopy());
        foreach ($entityKeys as $key) {
            if ($this->table->hasReference($key)) {
                unset($this[$key]);
            }
        }
        return $this;
    }

    /**
     * isNew
     *
     */
    public function isNew() : bool
    {
        return ! isset($this['id']) || substr((string)$this['id'], 0, 3) == 'new';
    }

    /**
     * isWatched
     *
     * @param string $_referenceName
     */
    public function isWatched(string $_referenceName)
    {
        return in_array($_referenceName, $this->watchedReferences);
    }

    /**
     * isKept
     *
     */
    public function isKept()
    {
        return isset($this['__keep']) && $this['__keep'];
    }

    /**
     * setEventProcessResult
     *
     * @param string $_eventHash
     * @param mixed $_result
     */
    public function setEventProcessResult(string $_eventHash, $_result)
    {
        $this['__processedEvents'] = array_merge($this['__processedEvents'] ?? [], [$_eventHash => $_result]);
        return $this;
    }

    /**
     * isEventProcessed
     *
     * @param string $_eventHash
     */
    public function isEventProcessed(string $_eventHash) : bool
    {
        return in_array($_eventHash, array_keys($this['__processedEvents'] ?? []), true);
    }

    /**
     * getEventProcessResult
     *
     * @param string $_eventHash
     */
    public function getEventProcessResult(string $_eventHash)
    {
        return $this['__processedEvents'][$_eventHash] ?? null;
    }

    /**
     * Flush processed events
     *
     * @return Entity
     */
    public function flushEvents(): Entity
    {
        $this['__processedEvents'] = [];
        return $this;
    }

    /**
     * getStorage
     *
     */
    public function getStorage()
    {
        return $this->getArrayCopy();
    }

    /**
     * Extract attributes from current entity
     *
     *  Examples:
     *  $entity['some'] = [ 'path' => [ 'attribute' => 'value' ] ];
     *  -------------------------------------------------
     *  $entity->extract([
     *      'some.path.attribute' => 'attribute'
     *  ]); 
     *  --------------------------
     *  [ 'attribute' => 'value' ]
     *  -------------------------------------------------
     *  $extracted = $entity->extract([
     *      'some.path.attribute' => fn ($_v) => mb_substr($_v, 0, 2),
     *  ]); 
     *  ---------------------------------
     *  [ 'some.path.attribute' => 'va' ]
     *  -------------------------------------------------
     *
     * @param array $_attributes
     *
     * @return array
     */
    public function extract(array $_attributes): array
    {
        $return = [];
        foreach ($_attributes as $key => $attribute) {
            $index = $attribute;
            if (! is_int($key)) {
                $attribute = $key;
            }

            $target = $this;
            $path = explode('.', $attribute);

            foreach ($path as $segment) {
                if ((is_array($target) || $target instanceof ArrayAccess) && ! isset($target[$segment])) {
                    $target = null;
                    break;
                }
                $target = $target[$segment];
            }

            if (is_callable($index)) {
                $return[$attribute] = $index($target, $this);
            } else {
                $return[$index] = $target === $this ? null : $target;
            }
        }

        return $return;
    }

    /**
     * toArray
     *
     * @param bool $_recursive
     */
    public function toArray(bool $_recursive = false, array $_predictRecursionChain = []) : array
    {
        $return = $this->getArrayCopy();

        if (! $_recursive) {
            return $return;
        }

        array_push($_predictRecursionChain, spl_object_id($this));

        foreach ($return as $key => $attribute) {
            if ($this->table->hasReference($key)) {
                $attribute = $this->$key();
            }
            if ($attribute instanceof EntityInterface) {
                if (in_array(spl_object_id($attribute), $_predictRecursionChain, true)) {
                    unset($return[$key]);
                    continue;
                }
                $return[$key] = $attribute->toArray(true, $_predictRecursionChain);
            } elseif ($attribute instanceof CollectionInterface) {
                $return[$key] = $attribute->filter(function($_item) use ($_predictRecursionChain) {
                    return is_object($_item) && ! in_array(spl_object_id($_item), $_predictRecursionChain, true);
                })->map(function($_entity) use ($_predictRecursionChain) {
                    return $_entity->toArray(true, $_predictRecursionChain);
                })->toList();
            } else {
                $return[$key] = $attribute;
            }
        }

        return $return;
    }

    /**
     * dump
     *
     */
    public function dump(\ArrayObject $_dumped = null)
    {
        $currentDump = clone $this;
        $currentDump->setTable(null);

        if (is_null($_dumped)) {
            $_dumped = new \ArrayObject();
        }

        if (! isset($_dumped[static::class])) {
            $_dumped[static::class] = [];
        }

        if (isset($_dumped[static::class][$this->id])) {
            return $_dumped[static::class][$this->id];
        }

        $_dumped[static::class][$this->id] = $currentDump;

        $attributes = $this->getArrayCopy();
        foreach ($attributes as $key => $attribute) {
            if ($attribute instanceof EntityInterface) {
                $currentDump[$key] = $attribute->dump($_dumped);
            } elseif ($attribute instanceof Collection) {
                $currentDump[$key] = $attribute->map(function($_entity) use ($_dumped) {
                    return $_entity->dump($_dumped);
                })->toArray();
            }
        }

        return $currentDump;
    }

    /**
     * Simple test for a numeric array
     *
     * @param array $array
     */
    protected function isNumeric(array $array)
    {
        return preg_match('/^[0-9]+$/', implode('', array_keys($array))) ? true : false;
    }

    /**
     * initSubscribes
     *
     */
    public static function initSubscribes(EventManager $_em)
    {
        # - Custom entity subscribes
        static::subscribe();
        # - System entity subscribes
        self::subscribeSystem();

        if (! isset(static::$subscribes[static::class])) {
            return;
        }

        foreach (static::$subscribes[static::class] as $eventName => $closures) {
            foreach ($closures as $closure) {
                $_em->attach($eventName, $closure);
            }
        }
    }

    /**
     * subscribe
     *
     * initialize - first initialize in repository
     * create - create item in DB.
     * save - save item in DB
     * delete - delete item in DB
     * cascadeDelete - cascade delete items from DB (strict references)
     *
     */
    public static function subscribe()
    {
    }

    /**
     * subscribeSystem
     *
     */
    private static function subscribeSystem()
    {
        static::after('initialize', function($e) {
            $entity = $e->getTarget();

            if ($entity->isNew()) {
                $entity->__created = new \DateTime('now');
                $entity->__updated = new \DateTime('now');
            }

            if (isset($entity['__created']) && is_string($entity['__created'])) {
                $entity['__created'] = new \DateTime($entity['__created']);
            }

            if (isset($entity['__updated']) && is_string($entity['__updated'])) {
                $entity['__updated'] = new \DateTime($entity['__updated']);
            }

        });

        static::before('save', function($e){

            $entity = $e->getTarget();
            $entity['__is-saving'] = true;

            $entity->__updated = new \DateTime('now');

            if (isset($entity['__created']) && is_object($entity->__created) && $entity->__created instanceof \DateTime) {
                $entity->__created = $entity->__created->format('Y-m-d H:i:s');
            }

            if (isset($entity['__updated']) && is_object($entity->__updated) && $entity->__updated instanceof \DateTime) {
                $entity->__updated = $entity->__updated->format('Y-m-d H:i:s');
            }
        });

        static::after('save', function($e) {

            $entity = $e->getTarget();
            $entity['__is-saving'] = false;

            if (isset($entity['__created']) && is_string($entity['__created'])) {
                $entity['__created'] = new \DateTime($entity['__created']);
            }

            if (isset($entity['__updated']) && is_string($entity['__updated'])) {
                $entity['__updated'] = new \DateTime($entity['__updated']);
            }
        });
    }

    /**
     * Register before action
     *
     * @param string $_name
     * @param \Closure $_closure
     * @param bool $_safety (optional)
     *
     * @return void
     */
    public static function before(string $_name, \Closure $_closure, bool $_safety = false)
    {
        $currentSubscribes = static::$subscribes[static::class] ?? [];

        $closure = $_safety ? self::getSafeClosure($_closure) : $_closure;

        $eventName = static::getEventName($_name, 'before');
        $currentSubscribes[$eventName] = array_merge(
            $currentSubscribes[$eventName] ?? [],
            [$closure]
        );

        static::$subscribes[static::class] = $currentSubscribes;
    }

    /**
     * Register after action event
     *
     * @param string $_name
     * @param \Closure $_closure
     * @param bool $_safety (optional)
     *
     * @return void
     */
    public static function after(string $_name, \Closure $_closure, bool $_safety = false)
    {
        $currentSubscribes = static::$subscribes[static::class] ?? [];

        $closure = $_safety ? self::getSafeClosure($_closure) : $_closure;

        $eventName = static::getEventName($_name, 'after');
        $currentSubscribes[$eventName] = array_merge(
            $currentSubscribes[$eventName] ?? [],
            [$closure]
        );

        static::$subscribes[static::class] = $currentSubscribes;
    }

    /**
     * getEntityEventName
     *
     * @param mixed $_event
     * @param mixed $_factor
     */
    public static function getEventName($_event, $_factor) : string
    {
        return static::class . ':' . $_event . '.' . $_factor;
    }

    /**
     * getSafeClosure
     *
     * @param Closure $_closure
     */
    final public static function getSafeClosure(\Closure $_closure)
    {
        $closureHash = self::getClosureHash($_closure);
        return function ($_event) use ($_closure, $closureHash) {
            if (is_object($target = $_event->getTarget()) && $target instanceof EntityInterface) {
                $eventHash = sha1($closureHash . $_event->getName());
                if (! $target->isEventProcessed($eventHash)){
                    /* $target->setEventProcessResult($eventHash, null); */
                    $target->setEventProcessResult($eventHash, $_closure($_event));
                }
                return $target->getEventProcessResult($eventHash);
            } else {
                return $_closure($_event);
            }
        };
    }

    /**
     * getClosureHash
     *
     * @param Closure $_closure
     */
    private static function getClosureHash(\Closure $_closure)
    {
        $reflection = new \ReflectionFunction($_closure);
        return sha1($reflection->getFileName() . $reflection->getStartLine() . $reflection->getEndLine());
    }

}
