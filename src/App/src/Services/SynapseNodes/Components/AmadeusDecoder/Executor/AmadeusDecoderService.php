<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusDecoder\Executor;

use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Qore\App\SynapseNodes\Components\AmadeusCommand\AmadeusCommand;
use Qore\App\SynapseNodes\Components\AmadeusDecoderPattern\AmadeusDecoderPattern;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\Collection\CollectionInterface;
use Qore\DealingManager\ResultInterface;
use Qore\InterfaceGateway\Component\ComponentInterface;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;
use Throwable;

/**
 * Class: AmadeusDecoderService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class AmadeusDecoderService extends ServiceArtificer
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
        $_router->group('/amadeus-decoder', null, function($_router) {
            $_router->any('/{id:\d+}', 'index');
            $_router->any('/apply-template/{id:\d+}', 'apply-template');
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
        $routeResult = $this->model->getRouteResult();

        /** @var RoutingHelper */
        $this->routingHelper = $this->plugin(RoutingHelper::class);

        list($method, $arguments) = $this->routingHelper->dispatch() ?? ['notFound', null];
        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ?ResultInterface
     */
    protected function index() : ?ResultInterface
    {
        $request = $this->model->getRequest();
        $ig = Qore::service(InterfaceGateway::class);

        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $decoder = $ig(DecoderComponent::class, 'command-decoder');

        $user = $request->getAttribute(User::class);

        $command = $this->mm('SM:AmadeusCommand')
            ->where(['@this.id' => $routeParams['id']])
            ->one();

        if (is_null($command)) {
            $decoder->setOption('command', null);
            return $this->getHtmlResponse($decoder);
        }

        $decoder->setOption('command', $command);
        $decoders = $this->getDecoders($command);

        $decoder->setOption('decoders', $decoders->map(
            fn($_decoder) => $_decoder->extract(['id', 'title', 'icon', 'result', 'templates'])
        )->toList());

        if ($request->isXmlHttpRequest()) {
            return $this->response($ig(Layout::class, 'layout')->execute('redirect', [
                'url' => Qore::url(
                    $this->getRouteName('index'),
                    ['id' => $routeParams['id']]
                ),
                'blank' => true,
            ]));
        } else {
            return $this->response(new HtmlResponse(
                Qore::service(TemplateRendererInterface::class)->render('frontapp::erp-platform/cabinet.twig', [
                    'title' => 'Декодер',
                    'interface-gateway' => $ig(Layout::class, 'layout')
                        ->setType('ql-decoder')
                        ->component($decoder)
                        ->compose(),
                ])
            ));
        }
    }

    /**
     * Get decoders
     *
     * @param \Qore\App\SynapseNodes\Components\AmadeusCommand\AmadeusCommand $_command
     *
     * @return \Qore\Collection\CollectionInterface
     */
    protected function getDecoders(AmadeusCommand $_command): CollectionInterface
    {
        $commandResponse = $_command['data']['output']['crypticResponse']['response'] ?? null;

        if (is_null($commandResponse)) {
            return Qore::collection([]);
        }

        $decoders = $this->mm('SM:AmadeusDecoder')
            ->with('templates')
            ->with('patterns')
            ->all();

        $decoders->each(function($_d) {
            $_d->nested = $_d->patterns()->nest('id', '__idparent')->toList();
        })->toList();

        $result = [];
        foreach ($decoders as $decoder) {
            try {
                if (preg_match($decoder->getRegex(), $commandResponse) && $decoder->templates->count()) {
                    foreach ($decoder->templates as $template) {
                        $template['route'] = Qore::url(
                            $this->getRouteName('apply-template'),
                            ['id' => $template['id']],
                        );
                    }
                    $matched = [];
                    foreach ($decoder->nested as $pattern) {
                        $matched[$pattern->name] = $this->matchDecoderPatterns($pattern, $_command);
                    }

                    $decoder['result'] = $matched;
                    $sortOrder = $decoder->__options['AmadeusDecoderTemplate-order'] ?? []; 
                    ! is_null($decoder->templates()) && $decoder->templates = $decoder->templates()->sortBy(function($_item) use ($sortOrder){
                        return (int)array_search($_item->id, array_values($sortOrder));
                    }, SORT_ASC)->toList();

                    $result[] = $decoder;

                }
            } catch(Throwable $e) {
                #-Nothing
            }
        }
        return Qore::collection($result);
    }

    /**
     * Match decoder patterns to command
     *
     * @param \Qore\App\SynapseNodes\Components\AmadeusDecoderPattern\AmadeusDecoderPattern $_pattern
     * @param \Qore\App\SynapseNodes\Components\AmadeusCommand\AmadeusCommand $_command
     *
     * @return array
     */
    protected function matchDecoderPatterns(AmadeusDecoderPattern $_pattern, $_command): array
    {
        $response = $_command['data']['output']['crypticResponse']['response'] ?? null;

        if (is_null($response)) {
            return [];
        }

        $result = [];
        preg_match_all($_pattern->getRegex(), $response, $result, PREG_SET_ORDER);

        if ($_pattern->groups) {
            $result = $this->matchResultGroups($_pattern, $result);
        }

        if ($_pattern->children) {
            $result = $this->matchNestedPatterns($_pattern->children, $result);
        }

        return $result;
    }
    
    /**
     * matchNestedPatterns
     *Match nested pattern to result
     * @param  mixed $_patterns
     * @param  mixed $_result
     * @return array
     */
    protected function matchNestedPatterns($_patterns, $_result): array
    {
        foreach ($_result as &$item) {
            foreach ($_patterns as $pattern) {
                $subject = $pattern->targetGroup && isset($item[$pattern->targetGroup])
                    ? $item[$pattern->targetGroup]
                    : $item[0];

                $resultChild = [];
                preg_match_all($pattern->getRegex(), $subject, $resultChild, PREG_SET_ORDER);

                if ($pattern->children) {
                    $this->matchNestedPatterns($pattern->children, $resultChild);
                }

                if ($pattern->groups) {
                    $resultChild = $this->matchResultGroups($pattern, $resultChild);
                }

                $item[$pattern->name] = $resultChild;
            }
        }
        return $_result;
    }

    /**
     * Match result groups with glossaries items
     *
     * @param \Qore\App\SynapseNodes\Components\AmadeusDecoderPattern\AmadeusDecoderPattern $_pattern
     * @param array $_result
     *
     * @return array
     */
    private function matchResultGroups(AmadeusDecoderPattern $_pattern, array $_result): array
    {
        $groupCodes = [];
        foreach ($_pattern->groups as $group => $glossary ) {
            if (! $glossary) {
                continue;
            }

            foreach ($_result as $item) {
                $groupCodes[$glossary] = array_merge($groupCodes[$glossary] ?? [], [$item[$group]]);
            }

            $groupCodes[$glossary] = array_unique($groupCodes[$glossary]);
        }

        if (! $groupCodes) {
            return $_result;
        }

        $glossaryItems = $this->mm('SM:AmadeusGlossaryItem')->where(function($_where) use ($groupCodes) {
            foreach ($groupCodes as $glossary => $values) {
                $_where->or([
                    '@this.idGlossary' => $glossary,
                    '@this.code' => $values,
                ]);
            }
        })->all();

        if ($glossaryItems->count()) {
            foreach ($_pattern->groups as $group => $glossary ) {
                if (! $glossary) {
                    continue;
                }
                foreach ($_result as &$item) {
                    $glossaryItem = $glossaryItems->firstMatch(['idGlossary' => $glossary, 'code' => $item[$group]]);
                    $item[sprintf('%s_d', $group)] = $glossaryItem->data ?? $item[$group];

                }
            }
        }
        return $_result;
    }

    /**
     * Return html response
     *
     * @param \Qore\InterfaceGateway\Component\ComponentInterface $_component (optional)
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function getHtmlResponse(ComponentInterface $_component = null): ResultInterface
    {
        return $this->response(new HtmlResponse(
            Qore::service(TemplateRendererInterface::class)->render('frontapp::erp-platform/cabinet.twig', [
                'title' => 'Декодер',
                'interface-gateway' => $ig(Layout::class, 'layout')
                    ->setType('ql-decoder')
                    ->component($_component)
                    ->compose(),
            ])
        ));
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
