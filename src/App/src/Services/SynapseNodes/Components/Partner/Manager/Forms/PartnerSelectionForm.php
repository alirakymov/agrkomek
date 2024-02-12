<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Partner\Manager\Forms;

use Qore\EventManager\EventManager;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\SynapseManager\Artificer\Form\FormArtificer;

/**
 * Class: PartnerSelectionForm
 *
 * @see Qore\SynapseManager\Artificer\Form\FormArtificer
 */
class PartnerSelectionForm extends FormArtificer
{
    /**
     * Subscribe for events
     *
     * @param \Qore\EventManager\EventManager $_em 
     * @return void 
     */
    public function subscribe(EventManager $_em): void
    {
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        $partners = $mm('SM:Partner')->with('group')
            ->where(fn($_where) => $_where->isNull('@this.group.id'))
            ->all();

        $_em->attach(
            sprintf('%s/id[*]@init.before', $this->getPreffix($this::LCL)), 
            function($_event) use ($partners) {
                $this->model->registerFilters([
                    'id' => array_merge(
                        $partners->extract('id')->toList(),
                        $_event->getParams()['data']->extract('id')->toList()
                    ),
                ]);
            }
        );
    }

}
