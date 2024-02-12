<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Product;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: Product
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Product extends SynapseBaseEntity
{

    /**
     * Sort product structure
     *
     * @return Product
     */
    public function structure(): Product
    {
        ! is_null($this->stages()) && $this->stages()->each(function ($_stage) {
            $sortOrder = $_stage->getOption('ProductTemplate-order', []);
            ! is_null($_stage->templates()) && $_stage->templates = $_stage->templates()->sortBy(function($_item) use ($sortOrder) {
                return (int)array_search($_item->id, array_values($sortOrder));
            }, SORT_ASC);
        });

        $sortOrder = $this->getOption('ProductStage-order', []);
        ! is_null($this->stages()) && $this->stages = $this->stages()->sortBy(function($_item) use ($sortOrder) {
            return (int)array_search($_item->id, array_values($sortOrder));
        }, SORT_ASC);

        return $this;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
