<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Operation\Manager\Forms;

use Qore\App\SynapseNodes\Components\Operation\Operation;
use Qore\DealingManager\ResultInterface;
use Qore\EventManager\EventManager;
use Qore\Form\Field\Submit;
use Qore\Form\FormManager;
use Qore\Form\Validator\Regex;
use Qore\Qore as Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Form\FormArtificer;

/**
 * Class: OperationForm
 *
 * @see Qore\SynapseManager\Artificer\Form\FormArtificer
 */
class OperationForm extends FormArtificer
{
    /**
     * subscribe
     *
     * @param EventManager $_em
     */
    public function subscribe(EventManager $_em)
    {
        $script = $this->entity->fields()->filter(function($_field) {
            return $_field->isAttribute() && $_field->relatedAttribute()->name == 'event';
        })->first();

        $_em->attach($script->getFieldEventName($this, 'init.after'), function ($_event) {
            $field = $_event->getTarget();
            $field->setOptions(Operation::getEvents());

        });
    }

    /**
     * compile
     *
     */
    public function compile() : ?ResultInterface
    {
        # - Mount form structure
        $this->mountFormStructure();
        # - Mount fields of related service forms
        $result = $this->next->process($this->model);
        # - Build form
        $this->mountSubmitField($fm = $this->marshalForm());

        return $this->getResponseResult(['form' => $fm]);
    }

    /**
     * mountSubmitField
     *
     * @param Qore\Form\FormManager $_fm
     */
    protected function mountSubmitField(FormManager $_fm)
    {
        # - Ignore if is not main form artificer
        if (! $this->isFirstArtificer()) {
            return;
        }

        $isCreate = is_null($routeResult = $this->model->getRouteResult())
            || $routeResult->getMatchedRouteName() === $this->getRouteName(
                $this->model->getArtificers()->takeLast(2)->first()->getRoutesNamespace(),
                'create'
            );

        # - Add submit button on form
        $_fm->setField(new Submit('submit', [
            'label' => $isCreate ? 'Создать' : 'Сохранить',
        ]));
    }

    /**
     * Return array of validartors
     *
     * @return array
     */
    protected function getValidators(): array
    {
        return [
            'name' => [
                [
                    'type' => Regex::class,
                    'message' => 'неправильно задано имя [A-z0-9]+',
                    'options' => [
                        'pattern' => '/^[A-z0-9]+$/u',
                    ],
                ],
            ]
        ];
    }

}
