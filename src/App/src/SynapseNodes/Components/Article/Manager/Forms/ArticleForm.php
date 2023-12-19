<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Article\Manager\Forms;

use Qore\App\SynapseNodes\Components\Article\Article;
use Qore\DealingManager\ResultInterface;
use Qore\EventManager\EventManager;
use Qore\Form\Field\Select;
use Qore\Form\Field\Submit;
use Qore\Form\FormManager;
use Qore\Form\Validator\InArray;
use Qore\Qore as Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Form\FormArtificer;

/**
 * Class: ArticleForm
 *
 * @see Qore\SynapseManager\Artificer\Form\FormArtificer
 */
class ArticleForm extends FormArtificer
{
    /**
     * subscribe
     *
     * @param EventManager $_em
     */
    public function subscribe(EventManager $_em)
    {
        # - Нахожу сущность, описывающую поле в форме под атрибут component
        $languageField = $this->entity->fields()->filter(function($_field) {
            return $_field->isAttribute() && $_field->relatedAttribute()->name === 'language';
        })->first();

        if ($languageField) {
            # - Вешаю действие на событие после инициализации поля (т.е. когда уже создан объект \Qore\Form\Field\...)
            $_em->attach($languageField->getFieldEventName($this, 'init.after', self::LCL), function ($_event) {
                # - Беру объект поля
                $field = $_event->getTarget();
                # - Формирую коллекцию возможных вариантов заполнения поля

                $options = Qore::collection(Article::getLanguages());
                # - Назначаю валидацию для данного поля
                $field->addValidator([
                    'type' => InArray::class,
                    'message' => 'Выбран неверный компонент',
                    'options' => [
                        'haystack' => $options->map(function($service) {
                            return $service['id'];
                        })->toList(),
                    ],
                ]);

                # - Модифицирую поле, если оно по типу Select
                if ($field instanceof Select) {
                    # - Добавляю опции выбора к полю
                    $field->setOptions($options->toList());
                }
            });
        }
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


}
