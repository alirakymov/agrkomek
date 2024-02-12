<?php

namespace Qore\App\SynapseNodes\Components\User\Restore\Plugin\Operation;

use Qore\App\SynapseNodes\Components\User\Restore\InterfaceGateway\RestoreComponent;
use Qore\App\SynapseNodes\Components\User\Restore\UserService;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\DealingManager\ResultInterface;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\SessionManager\SessionManager;
use Qore\SynapseManager\Plugin\Operation\AbstractPhase;
use Qore\SynapseManager\Plugin\Operation\ModelInterface;
use Qore\SynapseManager\SynapseManager;

class PasswordChanged extends AbstractPhase
{
    /**
     * @inheritdoc
     */
    public function initialize(ModelInterface $_model): void
    {
        $_model->next();
    }

    /**
     * @inheritdoc
     */
    public function process(ModelInterface $_model): ResultInterface
    {
        /** @var ServerRequest */
        $request = $this->_model->getRequest();
        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);
        /** @var SynapseManager */
        $sm = Qore::service(SynapseManager::class);
        /** @var SessionContainer */
        $session = Qore::service(SessionManager::class)(User::class);
        $session->clear();

        $component = $ig('alert')->setType('qc-alert')->setOptions([
            'content' => 'Пароль успешно изменен',
            'actions' => [
                [
                    'label' => 'Перейти в кабинет',
                    'class' => 'btn-alt-success',
                    'redirect' => true,
                    'actionUri' => Qore::url($sm('EducationPlatform:Executor')->getRouteName('index')),
                ],
            ]
        ]);

        /** @var RegisterLayout */
        $layout = $ig(RestoreComponent::class, 'restore');
        return $request->isXmlHttpRequest()
            ? $this->result( $layout->strategy('replace')->component($component))
            : $this->result($component);
    }
}
