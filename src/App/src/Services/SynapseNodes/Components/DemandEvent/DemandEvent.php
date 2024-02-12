<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\DemandEvent;

use Qore\App\Services\Tracking\TrackingInterface;
use Qore\App\SynapseNodes\Components\Demand\Demand;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Qore\SynapseManager\SynapseManager;

/**
 * Class: DemandEvent
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class DemandEvent extends SynapseBaseEntity
{
    /**
     * Calculate sla for demandEvents 
     *
     * @param DemandEvent $_event 
     *
     * @return DemandEvent
     */
    public function calculateSla(DemandEvent $_event): DemandEvent
    {
        $sm = Qore::service(SynapseManager::class);
        $mm = Qore::service(ModelManager::class);

        if (! (int)$_event->slaDate) {
            return $this;
        }

        $currentTime = (int)$this['slaDate'] ?: (new \DateTime('now'))->getTimestamp();
        $sla = $currentTime - (int)$_event->slaDate;

        $this['sla'] = $sla;
        $this['slaDate'] = $currentTime;
        $this['slaTarget'] = $_event->id;

        $_event['sla'] = (-1) * $sla;
        $_event['slaTarget'] = $this['id'];

        $mm($this)->save();
        $mm($_event)->save();

        $mm($_event)->with('demand')->one();


        /** @var TrackingInterface */
        $tracking = Qore::service(TrackingInterface::class);

        $tracking->fire(Demand::DEMAND_SLA_CHANGED, $_event->demand());

        return $this;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($_event) {
            $entity = $_event->getTarget();

            $entity->data = is_string($entity->data) 
                ? $entity->data 
                : serialize($entity->data);
        });

        static::after('save', $func = function($_event) {
            $entity = $_event->getTarget();
            
            $entity->data = is_string($entity->data) 
                ? unserialize($entity->data) 
                : $entity->data;
        });

        static::after('initialize', $func);

        parent::subscribe();
    }

}
