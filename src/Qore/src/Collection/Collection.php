<?php

declare(strict_types=1);

namespace Qore\Collection;

use Cake\Collection\Collection as CakeCollection;

/**
 * A collection is an immutable list of elements with a handful of functions to
 * iterate, group, transform and extract information from it.
 */
class Collection extends CakeCollection implements CollectionInterface
{
    use CollectionTrait;
}
