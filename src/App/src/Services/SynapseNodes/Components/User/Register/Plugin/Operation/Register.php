<?php

namespace Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation;

use Qore\App\SynapseNodes\Components\User\Register\InterfaceComponent\RegisterLayout;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\DealingManager\ResultInterface;
use Qore\Diactoros\ServerRequest;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\SessionManager\SessionManager;
use Qore\SynapseManager\Plugin\Operation\AbstractPhase;
use Qore\SynapseManager\Plugin\Operation\ModelInterface;
use Qore\SynapseManager\SynapseManager;

class Register extends AbstractPhase
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

        $component = $ig('alert')->setType('qc-alert')->setOptions([
            'content' => 'Поздравляем вы успешно зарегистрированы!',
            'actions' => [
                [
                    'label' => 'Войти в кабинет',
                    'class' => 'btn-alt-success',
                    'redirect' => true,
                    'actionUri' => Qore::url($sm('Office:Executor')->getRouteName('index')),
                ],
            ]
        ]);

        $session = Qore::service(SessionManager::class)(User::class);
        $session->clear();

        /** @var RegisterLayout */
        $layout = $ig(RegisterLayout::class, 'signup');
        return $request->isXmlHttpRequest()
            ? $this->result( $layout->strategy('replace')->component($component))
            : $this->result($component);
    }

}
