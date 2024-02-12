<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authentication;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use Qore\App\SynapseNodes\Components\EducationPlatform\Executor\EducationPlatformService;
use Qore\App\SynapseNodes\Components\User\Authentication\Adapter\AuthenticationInterface;
use Qore\App\SynapseNodes\Components\User\Authentication\InterfaceGateway\SigninComponent;
use Qore\App\SynapseNodes\Components\User\PhoneVerifiableInterface as PhoneVerifiableInterface;
use Qore\DealingManager\ResultInterface;
use Qore\Form\Field\Button;
use Qore\Form\Field\ButtonGroup;
use Qore\Form\Field\Password;
use Qore\Form\Field\Text;
use Qore\Form\FormManager;
use Qore\Form\Validator\Regex;
use Qore\Form\Validator\Required;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: User authentication service
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class UserService extends ServiceArtificer implements PhoneVerifiableInterface
{

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
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        $_router->group('/signin', null, function($_router) {
            $_router->any('', 'signin');
            $_router->get('/signout', 'signout');
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
        list($method, $arguments) = $this->routingHelper->dispatch(['signin' => 'index', 'signout']) ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ?ResultInterface
     */
    protected function index() : ?ResultInterface
    {
        /** @var EducationPlatformService */
        $artificer = ($this->sm)('Office:Executor');
        /** @var AuthenticationInterface */
        $authentication = Qore::service(AuthenticationInterface::class);
        # - Redirect if user is authenticated
        if ($authentication->isAuthenticated()) {
            return $this->response(
                new RedirectResponse(Qore::url($artificer->getRouteName('index')))
            );
        }

        /** @var ServiceArtificer */
        $restoreArtificer = ($this->sm)('User:Restore');
        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);
        $form = $this->getForm();
        $form->setField(new Password('password', [
            'label' => 'Пароль',
            'placeholder' => 'Пароль',
            'validators' => [
                [
                    'type' => Required::class,
                    'message' => 'вы забыли пароль ввести',
                ]
            ],
            'additional' => [
                'input-actions' => [
                    /* [ */
                    /*     'label' => 'я не помню', */
                    /*     'actionUri' => Qore::url($restoreArtificer->getRouteName('index')), */
                    /* ], */
                ]
            ],
        ]))->setField($this->getButtonGroup());

        $request = $this->model->getRequest();

        if ($request->isXmlHttpRequest()) {

            $form->setData($request->getParsedBody());

            if (! $form->isValid()) {
                return $this->response($form->decorate('decorate'));
            }


            $user = $authentication->authenticate($request);
            return $this->response(
                is_null($user)
                ? $form->decorate('decorate')->setErrors(['phone' => ['неправильный телефон или пароль']])
                : $ig('signin')->execute('redirect', ['url' => Qore::url($artificer->getRouteName('index'))])
            );
        } else {
            /** @var SigninComponent */
            $signin = $ig(SigninComponent::class, 'signin');
            /** @var TemplateRendererInterface */
            $renderer = Qore::service(TemplateRendererInterface::class);
            return $this->response(new HtmlResponse($renderer->render('frontapp::erp-platform/cabinet', [
                'title' => 'Вход в личный кабинет',
                'interface-gateway' => $signin
                    ->setParent('qore-app')
                    ->component($form->decorate('decorate'))
                    ->compose(),
            ])));
        }
    }

    /**
     *
     * @return \Qore\Form\Field\ButtonGroup
     */
    protected function getButtonGroup(): ButtonGroup
    {
        /** @var ServiceArtificer */
        $signupService = ($this->sm)('User:RegisterOtp');

        return new ButtonGroup('buttons-group', [
            'buttons' => [
                new Button('submit', [
                    'label' => 'Войти',
                    'action' => Button::ACTION_SUBMIT,
                ]),
                new Button('separator', [
                    'label' => 'или',
                    'action' => Button::ACTION_SEPARATOR,
                ]),
                new Button('signup', [
                    'label' => 'Зарегистрироваться',
                    'action' => Button::ACTION_REDIRECT,
                    'actionUri' => Qore::url($signupService->getRouteName('index')),
                    'options' => ['class' => 'btn-alt-success']
                ]),
            ]
        ]);
    }

    /**
     * Dispatch signout route
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function signout(): ResultInterface
    {
        /** @var AuthenticationInterface */
        $authentication = Qore::service(AuthenticationInterface::class);
        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);

        # - Signout
        $authentication->signout();

        $request = $this->model->getRequest();
        $signinUrl = Qore::url($this->getRouteName('signin'));

        if ($request->isXmlHttpRequest()) {
            return $this->response($ig('qore-app')->execute('redirect', [
                'url' => $signinUrl,
            ]));
        } else {
            return $this->response(new RedirectResponse($signinUrl));
        }
    }

    /**
     * Get authentication form
     *
     * @return \Qore\Form\FormManager
     */
    public function getForm() : FormManager
    {
        $fm = Qore::service(FormManager::class)
            ->setName('signup-form')
            ->setAction(Qore::url($this->getRouteName('signin')))
            ->setField($phone = new Text('phone', [
                'label' => 'Номер телефона',
                'placeholder' => 'Телефон',
                'validators' => [
                    [
                        'type' => Regex::class,
                        'message' => 'Номер неправильный',
                        'options' => [
                            'pattern' => '/^\+7\s\(\d{3}\)\s\d{3}\-\d{2}-\d{2}$/',
                        ]
                    ]
                ]
            ]));

        $phone->setAdditional([
            'mask' => '+7 (###) ###-##-##',
            'input-mode' => 'numeric',
        ]);

        return $fm;
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

}
