<?php

namespace Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation;

use Exception;
use Qore\App\SynapseNodes\Components\User\PhoneVerifiableInterface;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\DealingManager\ResultInterface;
use Qore\Form\Field\Submit;
use Qore\Form\Field\Text;
use Qore\Form\FormManager;
use Qore\Form\Validator\Regex;
use Qore\ORM\ModelManager;
use Qore\SynapseManager\Plugin\Operation\AbstractPhase;
use Qore\SynapseManager\Plugin\Operation\ModelInterface;
use Qore\Qore;
use Qore\SynapseManager\SynapseManager;

class GetAdditionalData extends AbstractPhase
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
        # - if current phase already processed then go to the next phase
        if ($_model->isProcessed($this)) {
            return $_model->next();
        }

        # - Dispatch stage
        foreach ($this->getStages() as $stage => $condition) {
            if ($condition()) {
                return $this->{$stage}();
            }
        }

        return $this->result();
    }

    /**
     * Get stages array with condition closures
     *
     * @return array<Closure>
     */
    protected function getStages(): array
    {
        $request = $this->_model->getRequest();
        return [
            'stageRequestFullname' => function() use ($request) {
                return ! is_null($request) && is_null($request('fullname'));
            },
            'stageSaveFullname' => function() use ($request) {
                return ! is_null($request) && ! is_null($request('fullname'));
            },
        ];
    }

    /**
     * Build form for request user fullname
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function stageRequestFullname(): ResultInterface
    {
        $form = $this->setFormFields($this->getForm());
        return $this->result($form->decorate('decorate'));
    }

    /**
     * Validate fullname and save it to user profile
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function stageSaveFullname(): ResultInterface
    {
        $form = $this->setFormFields($this->getForm());

        $request = $this->_model->getRequest();
        $form->setData($request->getParsedBody());
        # - Validate password
        if (! $form->isValid()) {
            return $this->result($form->decorate('decorate'));
        }
        # - Get global state
        $global = ($this->_model)($this::global);
        # - Get user entity and save password
        $user = $global[User::class];
        $user->fullname = preg_replace('/\s+/', ' ', $request('fullname'));

        $mm = Qore::service(ModelManager::class);
        $mm($user)->save();

        $this->_model->synchronized(function() use ($global, $user) {
            $global[User::class] = $user;
        });

        return $this->_model->next($this);
    }

    /**
     * Set password fields to form
     *
     * @param \Qore\Form\FormManager $_form
     *
     * @return \Qore\Form\FormManager
     */
    protected function setFormFields(FormManager $_form): FormManager
    {
        return $_form->setField(new Text('fullname', [
            'label' => 'Фамилия и Имя',
            'placeholder' => 'Введитe фамилию и имя',
            'validators' => [
                [
                    'type' => Regex::class,
                    'message' => 'неверно заполнено поле, фамилия и имя должны содержать только буквы',
                    'options' => [
                        'pattern' => '/[\w\-]+\s+[\w\-]+/iu'
                    ]
                ]
            ]
        ]))->setField(new Submit('submit', [
            'label' => 'Сохранить'
        ]));
    }

    /**
     * Get form component
     *
     * @return FormManager
     */
    protected function getForm(): FormManager
    {
        $sm = Qore::service(SynapseManager::class);
        $artificer = $sm($this->_model->getArtificerName());

        if (! $artificer instanceof PhoneVerifiableInterface) {
            throw new Exception(sprintf(
                'Service %s must implement PhoneVerifiableInterface',
                $this->_model->getArtificerName()
            ));
        }

        return $artificer->getForm()->resetFields();
    }

}
