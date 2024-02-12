<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusSession\Executor;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Qore\DealingManager\ResultInterface;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;
use Qore\App\Services\Amadeus\Amadeus;

/**
 * Class: AmadeusSessionService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class AmadeusSessionService extends ServiceArtificer
{
    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = '';

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
        $_router->group('/api', null, function($_router) {
            $_router->post('/amadeus/session', 'register-session');
            $_router->post('/amadeus/command', 'register-command');
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

        list($method, $arguments) = $this->routingHelper->dispatch(['register-session' => 'session', 'register-command' => 'command']) ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ?ResultInterface
     */
    protected function session() : ?ResultInterface
    {
        $this->next->process($this->model);
        $request = $this->model->getRequest();
        $dataKeys = [
            'officeId' => 'data.officeId',
            'userAlias' => 'data.userAlias',
            'organization' => 'data.organization',
            'agentSign' => 'data.agentSign',
            'siteCode' => 'data.siteCode',
            'jsessionid' => 'jsessionid',
            'initiator' => 'initiator',
            'cookies' => 'cookies',
        ];

        $data = [];
        foreach ($dataKeys as $index => $path) {
            $requestData  = $request($path, null);
            if (is_null($requestData)) {
                return $this->response(new JsonResponse(['Status' => 'Error']));
            }
            $data[$index] = $requestData;
        }
        $user = $this->mm('SM:User')->where(['@this.agentSign' => $data['userAlias']])->one();
        if (is_null($user)) {
            return $this->response(new JsonResponse(['Status' => 'Error']));
        }

        /** @var Amadeus */
        $amadeus = Qore::service(Amadeus::class);
        /** @var ModelManager */
        $mm = Qore::service('mm');

        $session = $mm('SM:AmadeusSession', $data);

        $session->link('user', $user);

        $amadeus->init($session);
        $session->contextId = $amadeus->contextIdRequest();

        $mm($session)->save();
        return $this->response(new JsonResponse([
            'token' => $session['token'],
        ]));
    }

    /**
     * index
     *
     * @return ResultInterface
     */
    protected function command() : ?ResultInterface
    {
        $this->next->process($this->model);
        $request = $this->model->getRequest();
        $data = $request->getParsedBody();

        if(isset($data['response'], $data['session'])){
            /** @var ModelManager */
            $mm = Qore::service('mm');
            $command = $mm('SM:AmadeusCommand',[
                'data' => $data['response']['model'],
                'command' => $data['response']['model']['output']['crypticResponse']['command']
            ]);

            $session = $this->mm('SM:AmadeusSession')
                ->where(['@this.token' => $data['session']])
                ->with('user')
                ->one();

            if (is_null($session)) {
                return $this->response(new JsonResponse(['Status' => 'Error']));
            }

            $command['userId'] = $session->user()['id'];
            $command['sessionId'] = $session['id'];
            $command['officeId'] = $session['officeId'];
            $mm($command)->save();

            $decoderCollection = $mm("SM:AmadeusDecoder")->all();

            foreach ($decoderCollection as $decoderObject) {
                $pattern = $decoderObject->getRegex();
                $subject = $command['data']['output']['crypticResponse']['response'] ?? '';
                if(preg_match($pattern, $subject)) {
                    return $this->response(new JsonResponse([
                        'id-command' => $command->id,
                        'decoding-url' => Qore::urlWithHost(
                            $this->sm('AmadeusDecoder:Executor')->getRouteName('index'),
                            ['id' => $command->id]
                        )
                    ]));
                }
            }
            return $this->response(new JsonResponse([
                'Error' => 'No matches'
            ]));
        }

        return $this->response(new JsonResponse(['Status' => 'Error']));
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
