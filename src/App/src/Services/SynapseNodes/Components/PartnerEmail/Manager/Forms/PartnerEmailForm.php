<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\PartnerEmail\Manager\Forms;

use Laminas\Validator\Hostname as ValidatorHostname;
use Qore\DealingManager\ResultInterface;
use Qore\EventManager\EventManager;
use Qore\Form\Field\Submit;
use Qore\Form\FormManager;
use Qore\Form\Validator\Digits;
use Qore\Form\Validator\EmailAddress;
use Qore\Form\Validator\Hostname;
use Qore\Form\Validator\Required;
use Qore\Qore as Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Form\FormArtificer;

/**
 * Class: PartnerEmailForm
 *
 * @see Qore\SynapseManager\Artificer\Form\FormArtificer
 */
class PartnerEmailForm extends FormArtificer
{
    /**
     * subscribe
     *
     * @param EventManager $_em
     */
    public function subscribe(EventManager $_em)
    {

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
     * Get field validators
     *
     * @return array
     */
    protected function getValidators(): array
    {
        return [
            'title' => [
                [
                    'type' => Required::class,
                    'message' => 'данное поле обязательно',
                ],
            ],
            'email' => [
                [
                    'type' => EmailAddress::class,
                    'message' => 'указан неверный адрес',
                ]
            ],
            'password' => [
                [
                    'type' => Required::class,
                    'message' => 'данное поле обязательно',
                ],
            ],
            'host' => [
                [
                    'type' => Required::class,
                    'message' => 'данное поле обязательно',
                    'break' => true,
                ],
                [
                    'type' => Hostname::class,
                    'message' => 'неверный адрес imap-сервера',
                    'options' => [
                        ValidatorHostname::ALLOW_ALL,
                    ]

                ],
            ],
            'port' => [
                [
                    'type' => Digits::class,
                    'message' => 'порт должен быть целым числом',
                ],
            ],
            'smtpHost' => [
                [
                    'type' => Required::class,
                    'message' => 'данное поле обязательно',
                    'break' => true,
                ],
                [
                    'type' => Hostname::class,
                    'message' => 'неверный адрес smtp-сервера',
                    'options' => [
                        ValidatorHostname::ALLOW_ALL,
                    ]

                ],
            ],
            'smtpPort' => [
                [
                    'type' => Digits::class,
                    'message' => 'порт должен быть целым числом',
                ],
            ],
        ];
    }
}
