<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\PageWidget\Manager\Forms;

use Mezzio\Router\RouteResult;
use Qore\App\SynapseNodes\System\PageWidget\PageWidget;
use Qore\Collection\CollectionInterface;
use Qore\EventManager\EventManager;
use Qore\Form\Action\Request;
use Qore\Form\Field;
use Qore\Form\Field\Select;
use Qore\Form\Field\Submit;
use Qore\Form\FormManager;
use Qore\Form\Validator\InArray;
use Qore\Front as QoreFront;
use Qore\Qore as Qore;
use Qore\App\SynapseNodes;
use Qore\Router\RouteCollector;
use Qore\DealingManager;
use Qore\SynapseManager\Artificer\Form\FormArtificer;
use Qore\SynapseManager\Artificer\Service;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use Qore\Form\Validator;

/**
 * Class: PageWidgetForm
 *
 * @see SynapseNodes\BaseManagerFormArtificer
 */
class PageWidgetForm extends FormArtificer
{
    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->any('/service-form', 'service-form');
    }

    /**
     * subscribe
     *
     * @param EventManager $_em
     */
    public function subscribe(EventManager $_em)
    {
        # - Подписываемся на события по инициализации поля service
        $this->subscribeServiceField($_em);
        # - Подписываемся на события по инициализации вложенных форм Service#....
        $this->subscribeServiceForm($_em);
        # - Подписываемся на события по инициализации поля template
        $this->subscribeTemplateField($_em);
    }

    /**
     * Подписываемся на инициализацию поля component
     *
     * @param EventManager $_em
     *
     * @return void
     */
    private function subscribeServiceField(EventManager $_em)
    {
        # - Нахожу сущность, описывающую поле в форме под атрибут component
        $serviceField = $this->entity->fields()->filter(function($_field) {
            return $_field->isAttribute() && $_field->relatedAttribute()->name === 'service';
        })->first();
        # - Собираем все субъекты текущего сервиса
        $serviceSubjects = $this->entity->service()->subjectsFrom()->filter(function($_subject){
            return $_subject->serviceTo()->synapse()->name !== 'Page';
        });

        if ($serviceField) {
            # - Вешаю действие на событие после инициализации поля (т.е. когда уже создан объект \Qore\Form\Field\...)
            $_em->attach($serviceField->getFieldEventName($this, 'init.after', self::LCL), function ($_event) use ($serviceSubjects) {
                # - Беру объект поля
                $field = $_event->getTarget();

                $systemServices = Qore::collection(PageWidget::getSystemServices())->map(function($_label, $_id) {
                    return [ 'id' => $_id, 'label' => $_label, ];
                })->toList();

                # - Формирую коллекцию возможных вариантов заполнения поля
                $options = $serviceSubjects->map(function($_subject){
                    return [
                        'id' => $_subject->serviceTo()->getSynapseServiceName(),
                        'label' => $_subject->serviceTo()->label,
                    ];
                })->prepend($systemServices);
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
                # - Регистрируем реакцию на изменение поля
                $field->setActions([
                    new Request([ 'url' => Qore::url($this->getRouteName('service-form')) ])
                ]);
            });
        }
    }

    /**
     * Подписываемся на события по инициализации вложенных форм Service#....
     *
     * @param \Qore\EventManager\EventManager $_em
     *
     * @return void
     */
    private function subscribeServiceForm(EventManager $_em)
    {
        # - Нахожу сущность, описывающую поле в форме под атрибут service
        $widgetServiceForms = $this->entity->fields()->filter(function($_field) {
            return $_field->isForm() && $_field->relatedSubject()->serviceTo()->synapse()->name !== 'Page';
        });

        foreach ($widgetServiceForms as $form) {
            $_em->attach($form->getFieldEventName($this, 'init.before', self::LCL), function ($_event) use ($form) {
                # - Отключаем все поля вложенных форм синапса PageService и оставляем поля
                # - только под выбранный сервис по соответствию значения page->service
                $widget = $_event->getParam('entity', null);
                return isset($widget['service']) && $widget['service'] === $form->relatedSubject()->serviceTo()->getSynapseServiceName();
            });
        }
    }

    /**
     * Подписываемся на инициализацию поля template
     *
     * @param EventManager $_em
     *
     * @return void
     */
    private function subscribeTemplateField(EventManager $_em)
    {
        # - Нахожу сущность, описывающую поле в форме под атрибут component
        $templateField = $this->entity->fields()->filter(function($_field) {
            return $_field->isAttribute() && $_field->relatedAttribute()->name === 'template';
        })->first();
        # - Собираем все шаблоны с директории шаблонов
        $templates = $this->getTemplates();

        if ($templateField) {
            # - Вешаю действие на событие после инициализации поля (т.е. когда уже создан объект \Qore\Form\Field\...)
            $_em->attach($templateField->getFieldEventName($this, 'init.after', self::LCL), function ($_event) use ($templates) {
                # - Беру объект поля
                $field = $_event->getTarget();
                # - Формирую коллекцию возможных вариантов заполнения поля
                $options = $templates->map(function($_subject){
                    return [
                        'id' => $_subject,
                        'label' => $_subject,
                    ];
                });
                # - Назначаю валидацию для данного поля
                $field->addValidator([
                    'type' => InArray::class,
                    'message' => 'Выбран неверный шаблон',
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
     * Read template files from directory
     *
     * @return \Qore\Collection\CollectionInterface
     */
    private function getTemplates() : CollectionInterface
    {
        $folder = Qore::config('templates.paths.frontapp')[0];
        $filter = new class(new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS)) extends RecursiveFilterIterator {
            public function accept() : bool {
                return $this->isDir() || preg_match('/\.twig$/', $this->current()->getFilename());
            }
        };

        $files = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::CHILD_FIRST);

        $result = [];
        foreach ($files as $template => $file) {
            if (! $file->isDir()) {
                $template = mb_substr($template, mb_strlen($folder));
                $result[] = $template;
            }
        }

        return Qore::collection($result);
    }

    /**
     * compile
     *
     */
    public function compile() : ?DealingManager\ResultInterface
    {

        $request = $this->model->getRequest();
        $routeResult = $request->getAttribute(RouteResult::class);

        switch (true) {
            case $routeResult->getMatchedRouteName() === $this->getRouteName('service-form'):
                return $this->serviceForm();
            default:
                return $this->buildForm();
        }
    }

    /**
     * Generate service form
     *
     * @return void
     */
    private function serviceForm()
    {
        $request = $this->model->getRequest();
        $entity = $this->model->getDataSource()->extractData()->first();

        # - Mount form structure
        $this->mountFormStructure();
        # - Process sub forms
        $this->next->process($this->model);

        # - Собираем созданные поля в форму
        $fm = $this->marshalForm();
        # - Монтируем в форму кнопку отправки
        $this->mountSubmitField($fm);

        return $this->getResponseResult([
            'response' => QoreFront\ResponseGenerator::get(
                # - Перезагружаем поля в форме
                $fm->decorate(['reload-fields', 'update-model'])
            )
        ]);
    }

    /**
     * Build Form
     *
     * @return void
     */
    private function buildForm()
    {
        if (! is_null($ds = $this->model->getDataSource())) {
            $this->model['target-page'] = $ds->extractData()->first()->page();
        }
        # - Mount form structure
        $this->mountFormStructure();
        # - Mount fields of related service forms
        $result = $this->next->process($this->model);
        # - Build form and mount submit field
        $this->mountSubmitField($fm = $this->marshalForm());

        return $this->getResponseResult(['form' => $fm]);
    }

    /**
     * Return validators
     *
     * @return array
     */
    protected function getValidators() : array
    {
        return [
            'name' => function($_field, $_entity) {
                return [
                    [
                        'type' => Validator\Required::class,
                        'message' => 'пожалуйста, введите название',
                        'break' => true,
                    ],
                    [
                        'type' => Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-z0-9\-]+$/'
                        ],
                        'message' => 'Это поле принимает только латинские символы, цифры и дефис',
                        'break' => true,
                    ],
                    [
                        'type' => Validator\Callback::class,
                        'message' => 'Widget с таким именем пользователя уже существует',
                        'options' => [
                            'callback' => function($_value, $_page, $_entity) {
                                $gw = $this->gateway()->with('page')->where(function ($_where) use ($_value, $_entity) {
                                    $_where(['@this.name' => $_value]);
                                    is_object($_entity) && ! $_entity->isNew() && $_where->notEqualTo('@this.id', $_entity['id']);
                                });
                                if (! is_null($_page)) {
                                    $gw->where(function ($_where) use ($_page) {
                                        $_where(['@this.page.id' => $_page->id]);
                                    });
                                }
                                return is_null($gw->one());
                            },
                            'callbackOptions' => [$this->model['target-page'] ?? null, $_entity],
                        ],
                        'break' => true,
                    ],
                ];
            },
        ];
    }

    /**
     * mountSubmitField
     *
     * @param Form\FormManager $fm
     */
    protected function mountSubmitField(FormManager $fm)
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
        $fm->setField(new Submit('submit', [
            'label' => $isCreate ? 'Создать' : 'Сохранить',
        ]));
    }

}
