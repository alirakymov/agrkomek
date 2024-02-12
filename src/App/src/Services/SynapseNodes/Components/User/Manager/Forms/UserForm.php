<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Manager\Forms;

use Qore\App\SynapseNodes\Components\User\User;
use Qore\DealingManager\ResultInterface;
use Qore\EventManager\EventManager;
use Qore\Form\Field\Submit;
use Qore\Form\FormManager;
use Qore\Form\Validator\Regex;
use Qore\Form\Validator\Required;
use Qore\Qore as Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Form\FormArtificer;

/**
 * Class: UserForm
 *
 * @see Qore\SynapseManager\Artificer\Form\FormArtificer
 */
class UserForm extends FormArtificer
{
    /**
     * subscribe
     *
     * @param EventManager $_em
     */
    public function subscribe(EventManager $_em)
    {
        $phoneField = $this->entity->fields()->filter(function($_field) {
            return $_field->isAttribute() && $_field->relatedAttribute()->name == 'phone';
        })->first();

        $_em->attach($phoneField->getFieldEventName($this, 'init.after'), function ($_event) {
            $field = $_event->getTarget();
            $field->setAdditional(array_merge($field->getAdditional(), [
                /* 'mask' => '+7 (###) ###-##-##', */
                'input-mode' => 'numeric',
            ]));
        });

        $roleField = $this->entity->fields()->filter(function($_field) {
            return $_field->isAttribute() && $_field->relatedAttribute()->name == 'role';
        })->first();

        $_em->attach($roleField->getFieldEventName($this, 'init.after'), function ($_event) {
            $field = $_event->getTarget();
            $field->setOptions(User::getRolesList());
        });
    }

    /**
     * compile
     *
     */
    public function compile(): ?ResultInterface
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
     * Get validators
     *
     * @return array
     */
    protected function getValidators(): array
    {
        return [
            'default' => [
                [
                    'type' => Required::class,
                    'break' => true,
                    'message' => 'обязательное поле',
                ]
            ],
            'phone' => [
                [
                    'type' => Regex::class,
                    'message' => 'неправильный номер',
                    'options' => [
                        'pattern' => '/^\d{10}$/',
                    ],
                ]
            ],
        ];
    }


}
