<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Form\DataSource;

use Qore\Qore;
use Qore\ORM\Entity;
use Qore\Collection\Collection;

/**
 * Class: EntityDataSource
 *
 * @see DataSourceInterface
 */
class EntityDataSource implements DataSourceInterface
{
    protected $entities;

    /**
     * __construct
     *
     * @param mixed $_entities
     */
    public function __construct($_entities)
    {
        if ($_entities instanceof Entity\Entity) {
            $this->entities = new Collection([$_entities]);
        } elseif ($_entities instanceof Collection) {
            $this->entities = $_entities;
        } elseif (is_array($_entities)) {
            $this->entities = new Collection($_entities);
        } else {
            throw new DataSourceException(sprintf('Unknown type (%s) of entities for entity data structure', is_object($_entities) ? get_class($_entities) : gettype($_entities)));
        }
    }

    /**
     * extractData
     *
     */
    public function extractData()
    {
        return $this->entities;
    }
}
