<?php

namespace Qore\App\SynapseNodes\Components\User\RegisterOtp\Plugin\Operation;

use DateTime;
use Exception;
use Qore\App\SynapseNodes\Components\User\PhoneVerifiableInterface;
use Qore\Qore;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\DealingManager\ResultInterface;
use Qore\Form\Field\Hidden;
use Qore\Form\Field\Submit;
use Qore\Form\Field\Text;
use Qore\Form\FormManager;
use Qore\Form\Validator\Callback;
use Qore\Form\Validator\Regex;
use Qore\ORM\ModelManager;
use Qore\SynapseManager\Plugin\Operation\AbstractPhase;
use Qore\SynapseManager\Plugin\Operation\ModelInterface;
use Qore\SynapseManager\SynapseManager;

class VerifyPhone extends AbstractPhase
{

    /**
     * @var int - resend sms interval (seconds)
     */
    protected int $smsInterval = 120;

    /**
     * @var ?User
     */
    protected ?User $user = null;

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
        if ($_model->isProcessed($this)) {
            return $_model->next();
        }

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
     * @return array
     */
    protected function getStages() : array
    {
        $request = $this->_model->getRequest();

        return [
            'stageVerifyForm' => function () use ($request) {
                $queryParams = $request->getQueryParams();
                return ! is_null($request) && is_null($request('endpoint')) && ! isset($queryParams['resend']);
            },
            'stageResend' => function () use ($request) {
                $queryParams = $request->getQueryParams();
                return ! is_null($request) && isset($queryParams['resend']);
            },
            'stageConfirmPhone' => function () use ($request) {
                return ! is_null($request) && ! is_null($request('endpoint'))
                    && ! is_null($request('code'));
            },
        ];
    }

    /**
     * Stage for update form with new fields (code, endpoint, and submit)
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function stageVerifyForm(): ResultInterface
    {
        $request = $this->_model->getRequest();
        # - Get form with phone field
        $form = $this->getForm();

        if (is_null($request('phone'))) {
            $form->setField(new Submit('submit', [
                'label' => 'Подтвердить',
            ]));
            return $this->result($form->decorate('decorate'));
        }

        $form->setData($request->getParsedBody());
        # - Validate data
        if ($request->getMethod() === 'POST' && ! $form->isValid()) {
            $form->setField(new Submit('submit', [
                'label' => 'Подтвердить',
            ]));
            return $this->result($form->decorate('decorate'));
        }
        # - Update form with new fields
        $this->setCodeField($form)
            # - Set form model data
            ->setData(array_merge($request->getParsedBody(), ['endpoint' => 'verify']));
        # - Return FormComponent of InterfaceGateway service
        return $this->result($form->decorate(['reload-fields', 'update-model']));
    }

    /**
     * Check code and confirm phone
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function stageConfirmPhone(): ResultInterface
    {
        $local = ($this->_model)($this);

        $request = $this->_model->getRequest();
        $form = $this->setCodeField($this->getForm());

        $form->setData($request->getParsedBody());
        if (! $form->isValid()) {
            return $this->result($form->decorate('decorate'));
        }

        $global = ($this->_model)($this::global);
        $this->_model->synchronized(function () use ($global) {
            $global[User::class] = $this->user;
            $global['phone'] = $this->user['phone'];
        });

        # - Go to next phase
        return $this->_model->next($this);
    }

    /**
     * Set code field to form
     *
     * @param \Qore\Form\FormManager $_form
     *
     * @return \Qore\Form\FormManager
     */
    protected function setCodeField(FormManager $_form): FormManager
    {
        return $_form->setField(new Text('code', [
            'label' => 'Введите код',
            'placeholder' => 'Введите одноразовый код',
            'info' => 'мы отправили код подтверждения',
            'validators' => [
                [
                    'type' => Regex::class,
                    'message' => 'код должен состоять из 6 цифр',
                    'break' => true,
                    'options' => [
                        'pattern' => '/\d{6}/',
                    ]
                ],
                [
                    'type' => Callback::class,
                    'message' => 'неверный код или время действия кода истекло',
                    'options' => [
                        'callback' => fn ($_value) => $this->validateCode(),
                    ],
                ],
            ]
        ]))->setField(new Hidden('endpoint'))
            ->setField(new Submit('submit', [ 'label' => 'Подтвердить' ]));
    }

    /**
     * Validation code
     *
     * @param string $_code
     *
     * @return bool
     */
    protected function validateCode(): bool
    {
        $request = $this->_model->getRequest();
        $data = $request->getParsedBody();
        # - Save data to global state
        $mm = Qore::service(ModelManager::class);
        # - Get user by phone or create new
        $this->user = $mm('SM:User')->where(function($_where) use ($data) {
            $_where([
                '@this.phone' => $this->preparePhone($data['phone']),
                '@this.otp' => $data['code'],
            ]);
        })->one();

        return ! is_null($this->user);
    }

    /**
     * Prepare phone
     *
     * @param  $_value
     *
     * @return string
     */
    protected function preparePhone($_value): string
    {
        $_value = preg_replace('/[^0-9]/', '', $_value);
        return mb_strlen($_value) > 10 ? mb_substr($_value, -10) : $_value;
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

        $form = $artificer->getForm();
        $form->setAction(Qore::url($this->_model->getActionRoute()));

        return $form;
    }

}
