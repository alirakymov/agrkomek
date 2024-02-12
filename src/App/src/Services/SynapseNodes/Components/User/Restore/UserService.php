<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Restore;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use Qore\App\SynapseNodes\Components\TrackerPoint\TrackerPoint;
use Qore\App\SynapseNodes\Components\User\PhoneVerifiableInterface;
use Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation\GetPassword;
use Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation\VerifyPhone;
use Qore\App\SynapseNodes\Components\User\Restore\InterfaceGateway\RestoreComponent;
use Qore\App\SynapseNodes\Components\User\Restore\Plugin\Operation\PasswordChanged;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\DealingManager\ResultInterface;
use Qore\Form\Field\Submit;
use Qore\Form\Field\Text;
use Qore\Form\FormManager;
use Qore\Form\Validator\Regex;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SessionManager\SessionManager;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;
use Qore\SynapseManager\Plugin\Operation\Operation;

/**
 * Class: Password restore service
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class UserService extends ServiceArtificer implements PhoneVerifiableInterface
{
    /**
     * @var string - index of restore identifier
     */
    const RESTORE_IDENTIFIER = 'restore-process-identifier';

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
        $_router->group('/restore', null, function($_router) {
            $_router->any('', 'index');
            $_router->any('/process', 'restore');
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
        list($method, $arguments) = $this->routingHelper->dispatch(['index', 'restore']) ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ResultInterface
     */
    protected function index() : ResultInterface
    {
        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);

        $request = $this->model->getRequest();
        if ($request->isXmlHttpRequest()) {
            return $this->response($ig('qore-app')->execute('redirect', [
                'url' => Qore::url($this->getRouteName('restore')),
            ]));
        }

        /** @var SessionContainer */
        $session = Qore::service(SessionManager::class)(User::class);
        if (isset($session[$this::RESTORE_IDENTIFIER])) {
            return $this->response(new RedirectResponse(Qore::url($this->getRouteName('restore'))));
        }

        /** @var FormManager */
        $form = $this->getForm();
        $form->setAction(Qore::url($this->getRouteName('restore')));
        $form->setField(new Submit('submit', [ 'label' => 'Восстановить' ]));

        /** @var RestoreComponent */
        $restore = $ig(RestoreComponent::class, 'restore');
        /** @var TemplateRendererInterface */
        $renderer = Qore::service(TemplateRendererInterface::class);
        return $this->response(new HtmlResponse($renderer->render('frontapp::erp-platform/cabinet', [
            'title' => 'Восстановление пароля',
            'interface-gateway' => $restore
                ->setParent('qore-app')
                ->component($form->decorate('decorate'))
                ->compose(),
        ])));
    }

    /**
     * Dispatch password restore process
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function restore(): ResultInterface
    {
        $request = $this->model->getRequest();
        # - Записываем в сессию факт обращения
        /** @var SessionManagerFactory */
        $session = Qore::service(SessionManager::class)(User::class);

        if (! isset($session[$this::RESTORE_IDENTIFIER])) {
            /** @var StorageInterface */
            $storage = $this->mm(TrackerPoint::class);
            $session->clear();
        } else {
            $storage = $this->mm('SM:TrackerPoint')->where(function($_where) use ($session) {
                $_where(['@this.identifier' => $session[$this::RESTORE_IDENTIFIER]]);
            })->one() ?? $this->mm(TrackerPoint::class);
        }

        $session[$this::RESTORE_IDENTIFIER] = $storage->getIdentifier();
        /** @var Operation */
        $operation = $this->plugin(Operation::class);
        $operation->setActionRoute($this->getRouteName('restore'));
        $operation->setStorage($storage);
        $operation->setChain([
            # - First phase: get phone number and verify it
            VerifyPhone::class,
            # - Second phase: get user password
            GetPassword::class,
            # - Info phase
            PasswordChanged::class,
        ]);

        $result = (array)$operation->launch();

        if ($request->isXmlHttpRequest()) {
            return $this->response(count($result) > 1 ? $result : (current($result) ?: []));
        } else {
            /** @var InterfaceGateway */
            $ig = Qore::service(InterfaceGateway::class);
            /** @var RestoreComponent */
            $restore = $ig(RestoreComponent::class, 'restore');
            /** @var TemplateRendererInterface */
            $renderer = Qore::service(TemplateRendererInterface::class);
            return $this->response(new HtmlResponse($renderer->render('frontapp::education-platform/cabinet', [
                'title' => 'Восстановление пароля',
                'interface-gateway' => $restore
                    ->setParent('qore-app')
                    ->component(current($result))
                    ->compose(),
            ])));
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
            ->setAction(Qore::url($this->getRouteName('restore')))
            ->setField(new Text('phone', [
                'label' => 'Номер телефона',
                'placeholder' => 'Телефон',
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
