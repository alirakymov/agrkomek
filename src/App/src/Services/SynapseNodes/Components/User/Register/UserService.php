<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Register;

use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Qore\App\SynapseNodes\Components\TrackerPoint\TrackerPoint;
use Qore\App\SynapseNodes\Components\User\PhoneVerifiableInterface;
use Qore\App\SynapseNodes\Components\User\Register\InterfaceComponent\RegisterLayout;
use Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation\GetAdditionalData;
use Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation\GetPassword;
use Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation\Register;
use Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation\VerifyPhone;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\DealingManager\ResultInterface;
use Qore\Diactoros\ServerRequest;
use Qore\Form\Field\Button;
use Qore\Form\Field\ButtonGroup;
use Qore\Form\Field\Submit;
use Qore\Form\Field\Text;
use Qore\Form\FormManager;
use Qore\Form\Validator\Regex;
use Qore\InterfaceGateway\Component\Form;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SessionManager\SessionManager;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;
use Qore\SynapseManager\Plugin\Operation\Operation;
use Qore\SynapseManager\Plugin\Operation\StorageInterface;

/**
 * Class: UserService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class UserService extends ServiceArtificer implements PhoneVerifiableInterface
{
    /**
     * @var string
     */
    const STORAGE_IDENTIFIER = 'storage-identifier';

    /**
     * @var \Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper
     */
    private RoutingHelper $routingHelper;

    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/signup', null, function($_router) {
            $_router->get('', 'index');
            $_router->any('/process', 'process');
        });
        # - Register related subjects routes
        $this->registerSubjectsRoutes($_router);
        # - Register this service forms routes
        $this->registerFormsRoutes($_router);
    }

    /**
     * Execute current service
     *
     * @return ?ResultInterface
     */
    public function compile() : ?ResultInterface
    {
        /** @var RoutingHelper */
        $this->routingHelper = $this->plugin(RoutingHelper::class);

        list($method, $arguments) = $this->routingHelper->dispatch(['index', 'process' => 'processOperation']) ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ?ResultInterface
     */
    protected function index() : ?ResultInterface
    {
        # - Записываем в сессию факт обращения
        $session = Qore::service(SessionManager::class)(User::class);
        if (isset($session[$this::STORAGE_IDENTIFIER])) {
            return $this->response(
                new RedirectResponse(Qore::url($this->getRouteName('process')))
            );
        }

        $form = $this->getForm();
        # - Add submit field
        $form->setField($this->getButtonGroup());
        /** @var Form - преобразуем в компонент InterfaceGateway */
        $form = $form->decorate('decorate');
        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);
        /** @var RegisterLayout */
        $signup = $ig(RegisterLayout::class, 'signup');
        /** @var TemplateRendererInterface */
        $renderer = Qore::service(TemplateRendererInterface::class);
        return $this->response(new HtmlResponse($renderer->render('frontapp::erp-platform/cabinet', [
            'title' => 'Регистрация',
            'interface-gateway' => $signup
                ->setParent('qore-app')
                ->component($form)
                ->compose(),
        ])));
    }

    /**
     *
     * @return \Qore\Form\Field\ButtonGroup
     */
    protected function getButtonGroup(): ButtonGroup
    {
        /** @var ServiceArtificer */
        $signinService = ($this->sm)('User:Authentication');

        return new ButtonGroup('buttons-group', [
            'buttons' => [
                new Button('submit', [
                    'label' => 'Зарегистрироваться',
                    'action' => Button::ACTION_SUBMIT,
                    'options' => ['class' => 'btn-alt-success']
                ]),
                new Button('separator', [
                    'label' => 'или',
                    'action' => Button::ACTION_SEPARATOR,
                ]),
                new Button('signup', [
                    'label' => 'Войти',
                    'action' => Button::ACTION_REDIRECT,
                    'actionUri' => Qore::url($signinService->getRouteName('signin')),
                ]),
            ]
        ]);
    }

    /**
     * Process operation
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    public function processOperation() : ResultInterface
    {
        /** @var ServerRequest */
        $request = $this->model->getRequest();
        # - Записываем в сессию факт обращения
        $session = Qore::service(SessionManager::class)(User::class);

        if (! isset($session[$this::STORAGE_IDENTIFIER])) {
            $session->clear();
            # - Redirect on direct request without registered storage identifier in session
            if (! $request->isXmlHttpRequest()) {
                return $this->response(new RedirectResponse(Qore::url($this->getRouteName('index'))));
            }
            /** @var StorageInterface */
            $storage = $this->mm(TrackerPoint::class);
            # - Get form component
            $form = $this->getForm()
                ->setData($request->getParsedBody());
            # - Validate form data
            if (! $form->isValid()) {
                return $this->response($form->decorate('decorate'));
            }
        } else {
            $storage = $this->mm('SM:TrackerPoint')->where(function($_where) use ($session) {
                $_where(['@this.identifier' => $session[$this::STORAGE_IDENTIFIER]]);
            })->one() ?? $this->mm(TrackerPoint::class);
        }

        $session[$this::STORAGE_IDENTIFIER] = $storage->getIdentifier();
        /** @var Operation */
        $operation = $this->plugin(Operation::class);

        $operation->setChain([
            # - First phase: get phone number and verify it
            VerifyPhone::class,
            # - Second phase: get user password
            GetPassword::class,
            # - Third phase: get user name
            GetAdditionalData::class,
            # - Register information
            Register::class,
        ]);

        $operation->setStorage($storage);
        $result = (array)$operation->launch();

        if ($request->isXmlHttpRequest()) {
            return $this->response(count($result) > 1 ? $result : (current($result) ?: []));
        } else {
            /** @var InterfaceGateway */
            $ig = Qore::service(InterfaceGateway::class);
            /** @var RegisterLayout */
            $signup = $ig(RegisterLayout::class, 'signup');
            /** @var TemplateRendererInterface */
            $renderer = Qore::service(TemplateRendererInterface::class);
            return $this->response(new HtmlResponse($renderer->render('frontapp::education-platform/cabinet', [
                'title' => 'Регистрация',
                'interface-gateway' => $signup
                    ->setParent('qore-app')
                    ->component(current($result))
                    ->compose(),
            ])));
        }
    }

    /**
     * Not Found
     *
     * @return ?ResultInterface
     */
    protected function notFound() : ?ResultInterface
    {
        return $this->response(new HtmlResponse('Not Found', 404));
    }

    /**
     * @inheritdoc
     */
    public function getForm() : FormManager
    {
        $fm = Qore::service(FormManager::class)
            ->setName('signup-form')
            ->setAction(Qore::url($this->getRouteName('process')))
            ->setField(new Text('phone', [
                'label' => 'Номер телефона',
                'placeholder' => 'Введите номер телефона',
                'validators' => [
                    [
                        'type' => Regex::class,
                        'message' => 'номер неправильный только 10 цифр, например 7771234567',
                        'options' => [
                            'pattern' => '/\d{10}/',
                        ]
                    ]
                ]
            ]));

        return $fm;
    }

}
