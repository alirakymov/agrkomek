<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Page\Manager\Forms;

use Mezzio\Router\RouteResult;

use Qore\App\SynapseNodes\System\Page\Page;
use Qore\Collection\CollectionInterface;
use Qore\EventManager\EventManager;
use Qore\Form\Action\Request;
use Qore\Form\Field;
use Qore\Form\Field\Select;
use Qore\Form\Field\Submit;
use Qore\Form\FormManager;
use Qore\Form\Validator\InArray;
use Qore\Front as QoreFront;
use Qore\Qore;
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

class PageForm extends FormArtificer
{
    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->any('/component-form', 'component-form');
    }

    /**
     * subscribe
     *
     * @param EventManager $_em
     */
    public function subscribe(EventManager $_em)
    {
        # - Подписываемся на события по инициализации поля component
        $this->subscribeComponentField($_em);
        # - Подписываемся на события по инициализации вложенных форм Component#....
        $this->subscribeComponentForm($_em);
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
    private function subscribeComponentField(EventManager $_em)
    {
        # - Нахожу сущность, описывающую поле в форме под атрибут component
        $componentField = $this->entity->fields()->filter(function($_field) {
            return $_field->isAttribute() && $_field->relatedAttribute()->name === 'componentService';
        })->first();
        # - Собираем все субъекты текущего сервиса, которые относятся к синапсу Component
        $componentSubjects = $this->entity->service()->subjectsFrom()->filter(function($_subject){
            return $_subject->serviceTo()->synapse()->name === 'PageComponent';
        });

        if ($componentField) {
            # - Вешаю действие на событие после инициализации поля (т.е. когда уже создан объект \Qore\Form\Field\...)
            $_em->attach($componentField->getFieldEventName($this, 'init.after', self::LCL), function ($_event) use ($componentSubjects) {
                # - Беру объект поля
                $field = $_event->getTarget();
                # - Формирую коллекцию возможных вариантов заполнения поля
                $systemServices = Qore::collection(Page::getSystemServices())->map(function($_label, $_id) {
                    return [ 'id' => $_id, 'label' => $_label, ];
                })->toList();
                $options = $componentSubjects->map(function($_subject){
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
                    new Request([ 'url' => Qore::url($this->getRouteName('component-form')) ])
                ]);
            });
        }
    }

    /**
     * Подписываемся на события по инициализации вложенных форм Component#....
     *
     * @param \Qore\EventManager\EventManager $_em
     *
     * @return void
     */
    private function subscribeComponentForm(EventManager $_em)
    {
        # - Нахожу сущность, описывающую поле в форме под атрибут service
        $pageComponentForms = $this->entity->fields()->filter(function($_field) {
            return $_field->isForm() && $_field->relatedSubject()->serviceTo()->synapse()->name === 'PageComponent';
        });

        foreach ($pageComponentForms as $form) {
            $_em->attach($form->getFieldEventName($this, 'init.before', self::LCL), function ($_event) use ($form) {
                # - Отключаем все поля вложенных форм синапса PageComponent и оставляем поля
                # - только под выбранный сервис по соответствию значения page->component
                $page = $_event->getParam('entity', null);
                return isset($page['componentService']) && $page['componentService'] === $form->relatedSubject()->serviceTo()->getSynapseServiceName();
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
     * @return \Qore\Collection\CollectionInterface [TODO:description]
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
            case $routeResult->getMatchedRouteName() === $this->getRouteName('component-form'):
                return $this->componentForm();
            default:
                return $this->buildForm();
        }
    }

    /**
     * Generate component form
     *
     * @return void
     */
    private function componentForm()
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
        # - Mount form structure
        $this->mountFormStructure();
        # - Mount fields of related service forms
        $result = $this->next->process($this->model);
        # - Build form and mount submit field
        $this->mountSubmitField($fm = $this->marshalForm());

        return $this->getResponseResult(['form' => $fm]);
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
