<?php

namespace Qore\App\SynapseNodes\Components\User\Register\Plugin\Operation;

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
        # - Generate code and send sms
        $this->generateCodeAndSendSms($request('phone'));
        # - Update form with new fields
        $this->setCodeField($form)
            # - Set form model data
            ->setData(array_merge($request->getParsedBody(), ['endpoint' => 'verify']));
        # - Return FormComponent of InterfaceGateway service
        return $this->result($form->decorate(['reload-fields', 'update-model']));
    }

    /**
     * Stage for resend code to user
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function stageResend(): ResultInterface
    {
        $request = $this->_model->getRequest();

        $form = $this->getForm();
        $form->setData($request->getParsedBody());

        if (! $form->isValid()) {
            return $this->result($this->setCodeField($form)->decorate('decorate'));
        }

        $isSended = $this->generateCodeAndSendSms($request('phone'));
        $form = $this->setCodeField($form);
        /** @var Text */
        $codeField = Qore::collection($form->getFields())
            ->filter(fn($_field) => $_field->getName() == 'code')->first();

        $codeField->setInfo(
            $isSended ? 'мы еще раз выслали код' : 'подождите немного, код можно высылать раз в две минуты'
        );

        return $this->result($form->decorate('decorate'));
    }

    /**
     * Generate code and create deferred task
     *
     * @param string $_phone
     *
     * @return bool
     */
    protected function generateCodeAndSendSms(string $_phone): bool
    {
        $local = ($this->_model)($this);

        # - Get last sended code
        $lastCode = Qore::collection($local('codes'))
            ->sortBy(fn($_code) => $_code['time']->getTimestamp(), SORT_DESC)
            ->first();

        if (! is_null($lastCode) && (time() - $lastCode['time']->getTimestamp() < $this->smsInterval)) {
            return false;
        }

        # - Generate random code and save it to state
        $this->_model->synchronized(function() use ($_phone) {
            $local = ($this->_model)($this);
            $local('codes')->merge([
                [
                    'code' => mt_rand(100000, 999999),
                    'phone' => $_phone,
                    'time' => new DateTime(),
                ],
            ]);
        });

        # - Defer task to send sms
        $this->defer(function(VerifyPhone $_phase) {
            $_phase->sendGeneratedCodes();
        });

        return true;
    }

    /**
     * Task executor for sending generated codes
     *
     * @return
     */
    protected function sendGeneratedCodes()
    {
        $local = ($this->_model)($this);
        /* \dump($local); */
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

        # - Get phone from local state
        $phone = Qore::collection($local('codes'))->firstMatch(['code' => $request('code')]);
        # - Save data to global state
        $mm = Qore::service(ModelManager::class);

        # - Get user by phone or create new
        $user = $mm('SM:User')->where(function($_where) use ($phone) {
            $_where(['@this.phone' => $phone['phone']]);
        })->one() ?? $mm(User::class, [
            'phone' => $phone['phone'],
        ]);

        # - Save user if is new
        $user->isNew() && $mm($user)->save();

        $global = ($this->_model)($this::global);
        $this->_model->synchronized(function () use ($phone, $global, $user) {
            $global[User::class] = $user;
            $global['phone'] = $phone['phone'];
            $global['phone-verify-fact'] = $phone;
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
            'placeholder' => 'Введите код смс',
            'info' => 'мы отправили код подтверждения на данный номер',
            'additional' => [
                'input-actions' => [
                    [
                        'label' => 'повторить',
                        'actionUri' => Qore::url($this->_model->getActionRoute(), [], ['resend' => 1]),
                    ],
                ],
            ],
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
                        'callback' => fn ($_value) => $this->validateCode($_value),
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
    protected function validateCode(string $_code): bool
    {
        $local = ($this->_model)($this);
        # - TODO: add datetime validation
        return Qore::collection($local('codes'))->firstMatch(['code' => $_code]) !== null;
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
