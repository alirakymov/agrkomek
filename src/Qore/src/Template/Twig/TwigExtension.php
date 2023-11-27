<?php

namespace Qore\Template\Twig;

use Cake\Collection\CollectionInterface;
use Qore\Qore;
use Qore\UploadManager\UploadManager;
use Qore\ImageManager\ImageManager;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ServerRequestInterface;
use Qore\ORM\Entity\EntityInterface;

class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var ServerUrlHelper
     */
    protected $serverUrlHelper;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * uploadManager
     *
     * @var mixed
     */
    protected $uploadManager = null;

    /**
     * imageManager
     *
     * @var mixed
     */
    protected $imageManager = null;

    /**
     * @var null|string
     */
    protected $assetsUrl;

    /**
     * globalAssetsUrl
     *
     * @var mixed
     */
    protected $globalAssetsUrl;

    /**
     * @var null|string|int
     */
    protected $assetsVersion;

    /**
     * @var array
     */
    protected $globals;

    /**
     * @var \Twig\Environment
     */
    protected $renderer = null;

    /**
     * @var ServerRequestInterface|null 
     */
    private ?ServerRequestInterface $request;

    /**
     * __construct
     *
     * @param ServerUrlHelper $serverUrlHelper
     * @param UrlHelper $urlHelper
     * @param ?string $assetsUrl
     * @param ?string $globalAssetsUrl
     * @param mixed $assetsVersion
     * @param array $globals
     */
    public function __construct(
        ServerUrlHelper $serverUrlHelper,
        UrlHelper $urlHelper,
        UploadManager $uploadManager,
        ImageManager $imageManager,
        ?ServerRequestInterface $request,
        ?string $assetsUrl,
        ?string $globalAssetsUrl,
        $assetsVersion,
        array $globals = []
    ) {
        $this->serverUrlHelper = $serverUrlHelper;
        $this->urlHelper       = $urlHelper;
        $this->uploadManager   = $uploadManager;
        $this->imageManager    = $imageManager;
        $this->request         = $request;

        $this->assetsUrl       = $assetsUrl;
        $this->globalAssetsUrl = $globalAssetsUrl;
        $this->assetsVersion   = $assetsVersion;
        $this->globals         = $globals;
    }

    /**
     * Register twig environment
     *
     * @param \Twig\Environment $_renderer
     *
     * @return void
     */
    public function setRenderer(Environment $_renderer) : void
    {
        $this->renderer = $_renderer;
    }

    /**
     * getGlobals
     *
     */
    public function getGlobals() : array
    {
        return $this->globals;
    }

    /**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('request', [$this, 'request']),
            new TwigFunction('absolute_url', [$this, 'renderUrlFromPath']),
            new TwigFunction('asset', [$this, 'renderAssetUrl']),
            new TwigFunction('global_asset', [$this, 'renderGlobalAssetUrl']),
            new TwigFunction('uimage', [$this, 'renderUploadedImage']),
            new TwigFunction('ufile', [$this, 'renderUploadedFile']),
            new TwigFunction('collection_prepare', [$this, 'renderCollectionPrepare']),
            new TwigFunction('path', [$this, 'renderUri']),
            new TwigFunction('url', [$this, 'renderUrl']),
            new TwigFunction('render', [$this, 'render'], ['is_safe' => ['html']]),
            new TwigFunction('debug', [$this, 'debug']),
            new TwigFunction('dd', [$this, 'dd']),
            new TwigFunction('d', array($this, 'd')),
        ];
    }

    /**
     * getFilters
     *
     */
    public function getFilters()
    {
        return [
            new TwigFilter('addslashes', 'addslashes'),
            new TwigFilter('base64_encode', 'base64_encode'),
            new TwigFilter('base64_decode', 'base64_decode'),
            new TwigFilter('int', 'intval'),
            new TwigFilter('group', [$this, 'filterGroup']),
            new TwigFilter('chunk', [$this, 'filterChunk']),
        ];
    }

    /**
     * Group filter
     *
     * @param  $_items
     * @param  $_criteria
     *
     * @return CollectionInterface
     */
    public function filterGroup($_items, $_criteria): CollectionInterface
    {
        return Qore::collection($_items)->groupBy($_criteria);
    }

    /**
     * Chunk filter
     *
     * @param $_items
     * @param int $_size
     *
     * @return CollectionInterface
     */
    public function filterChunk($_items, int $_size): CollectionInterface
    {
        return Qore::collection($_items)->chunk($_size);
    }

    /**
     * function for access request interface
     *
     * @param ?string $_attribute (optional)
     * @param $_default (optional) 
     *
     * @return mixed 
     */
    public function request(string $_attribute = null, $_default = null)
    {
        $this->request;

        if (is_null($this->request) || is_null($_attribute)) {
            return $this->request;
        }

        return $this->request->getAttribute($_attribute, $_default);
    }

    /**
     * Render relative uri for a given named route
     *
     * Usage: {{ path('article_show', {'id': '3'}) }}
     * Generates: /article/3
     *
     * Usage: {{ path('article_show', {'id': '3'}, {'foo': 'bar'}, 'fragment') }}
     * Generates: /article/3?foo=bar#fragment
     *
     * @param array $options Can have the following keys:
     *     - reuse_result_params (bool): indicates if the current
     *       RouteResult parameters will be used, defaults to true
     */
    public function renderUri(
        ?string $route = null,
        array $routeParams = [],
        array $queryParams = [],
        ?string $fragmentIdentifier = null,
        array $options = []
    ) {
        return $this->urlHelper->generate($route, $routeParams, $queryParams, $fragmentIdentifier, $options);
    }

    /**
     * Render absolute url for a given named route
     *
     * Usage: {{ url('article_show', {'slug': 'article.slug'}) }}
     * Generates: http://example.com/article/article.slug
     *
     * Usage: {{ url('article_show', {'id': '3'}, {'foo': 'bar'}, 'fragment') }}
     * Generates: http://example.com/article/3?foo=bar#fragment
     *
     * @param array $options Can have the following keys:
     *     - reuse_result_params (bool): indicates if the current
     *       RouteResult parameters will be used, defaults to true
     */
    public function renderUrl(
        ?string $route = null,
        array $routeParams = [],
        array $queryParams = [],
        ?string $fragmentIdentifier = null,
        array $options = []
    ) {
        return $this->serverUrlHelper->generate(
            $this->renderUri($route, $routeParams, $queryParams, $fragmentIdentifier, $options)
        );
    }

    /**
     * Render absolute url from a path
     *
     * Usage: {{ absolute_url('path/to/something') }}
     * Generates: http://example.com/path/to/something
     */
    public function renderUrlFromPath(string $path = null) : string
    {
        return $this->serverUrlHelper->generate($path);
    }

    /**
     * Render asset url, optionally versioned
     *
     * Usage: {{ asset('path/to/asset/name.ext', version=3) }}
     * Generates: path/to/asset/name.ext?v=3
     */
    public function renderAssetUrl(string $path, string $version = null) : string
    {
        $assetsVersion = $version !== null && $version !== '' ? $version : $this->assetsVersion;

        // One more time, in case $this->assetsVersion was null or an empty string
        $assetsVersion = $assetsVersion !== null && $assetsVersion !== '' ? '?v=' . $assetsVersion : '';

        return $this->assetsUrl . $path . $assetsVersion;
    }
    /**
     * renderGlobalAssetUrl
     *
     * @param string $path
     * @param string $version
     */
    public function renderGlobalAssetUrl(string $path, string $version = null) : string
    {
        $assetsVersion = $version !== null && $version !== '' ? $version : $this->assetsVersion;

        // One more time, in case $this->assetsVersion was null or an empty string
        $assetsVersion = $assetsVersion !== null && $assetsVersion !== '' ? '?v=' . $assetsVersion : '';

        return $this->globalAssetsUrl . $path . $assetsVersion;
    }

    /**
     * renderUploadedFile
     *
     * @param string $_fileUniqid
     */
    public function renderUploadedFile(string $_fileUniqid) : string
    {
        return $this->uploadManager->getFile($_fileUniqid);
    }

    /**
     * renderUploadedImageUrl
     *
     * @param string $_imageUniqid
     */
    public function renderUploadedImage(string $_imageUniqid) : ImageManager
    {
        return $this->imageManager->init($this->uploadManager->getFile($_imageUniqid));
    }

    /**
     * Prepare json
     *
     * @param $_data
     *
     * @return
     */
    public function renderCollectionPrepare($_data)
    {
        $result = [];
        foreach ($_data as $entity) {
            $result[] = $entity->toArray(true);
        }

        return Qore::collection($result);
    }

    /**
     * setAssetsUrl
     *
     * @param string $_assetsUrl
     */
    public function setAssetsUrl(string $_assetsUrl) : TwigExtension
    {
        $this->assetsUrl = $_assetsUrl;
        return $this;
    }

    /**
     * setGlobalAssetsUrl
     *
     * @param string $_assetsUrl
     */
    public function setGlobalAssetsUrl(string $_assetsUrl) : TwigExtension
    {
        $this->globalAssetsUrl = $_assetsUrl;
        return $this;
    }

    /**
     * setAssetsVersion
     *
     * @param mixed $_assetsVersion
     */
    public function setAssetsVersion($_assetsVersion) : TwigExtension
    {
        $this->assetsVersion = $_assetsVersion;
        return $this;
    }

    /**
     * Render some data with template
     *
     * @param string $_template
     * @param $_options (optional)
     *
     * @return string
     */
    public function render(string $_template, $_options = []) : string
    {
        $mutated = [];
        if ($_options instanceof EntityInterface) {
            foreach ($_options as $key => $value) {
                $mutated[$key] = $_options[$key];
            }
        } else {
            $mutated = $_options;
        }

        return $this->renderer->render(sprintf('@frontapp%s', $_template), $mutated);
    }

    /**
     * debug
     *
     * @param mixed $var
     */
    public function debug($var)
    {
        Qore::debug($var);
    }

    /**
     * dd
     *
     */
    public function dd($var)
    {
        dd($var);
    }

    /**
     * d
     *
     * @param mixed $var
     */
    public function d($var)
    {
        dump($var);
    }

}
