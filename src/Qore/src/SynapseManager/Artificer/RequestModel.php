<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;

use Qore\Collection;
use Qore\Qore;
use Qore\DealingManager\Model;
use Qore\Form\Field;
use Qore\Form\FormManager;
use Qore\ORM\Entity\Entity;
use Qore\ORM\Gateway\Gateway;
use Qore\SynapseManager\Artificer\Service\Filter;
use Qore\SynapseManager\Structure;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;
use Qore\Diactoros\ServerRequest;
use Qore\SynapseManager\Artificer\Service\Filter\TypeInterface;

/**
 * Class: RequestModel
 *
 * @see Model
 */
class RequestModel extends Model
{
    /**
     * initOnly
     *
     * @var mixed
     */
    protected $initOnly = false;

    /**
     * request
     *
     * @var mixed
     */
    protected $request = null;

    /**
     * pipelineArtificers
     *
     * @var mixed
     */
    protected $pipelineArtificers = null;

    /**
     * artificers
     *
     * @var mixed
     */
    protected $artificers = null;

    /**
     * options
     *
     * @var mixed
     */
    protected $options = [];

    /**
     * subjects
     *
     * @var mixed
     */
    protected $subjects = null;

    /**
     * form
     *
     * @var mixed
     */
    protected $form = null;

    /**
     * formData
     *
     * @var mixed
     */
    protected $formData = null;

    /**
     * formEntities
     *
     * @var mixed
     */
    protected $formEntities = [];

    /**
     * formFields
     *
     * @var mixed
     */
    protected $formFields = [];

    /**
     * fieldsOrder
     *
     * @var mixed
     */
    protected $fieldsOrder = [];

    /**
     * dataSource
     *
     * @var mixed
     */
    protected $dataSource = null;

    /**
     * subjectFilters - scanned filters details for each subject
     *
     * @var mixed
     */
    protected $subjectFilters = null;

    /**
     * filters - collection of initialized filters from each artificer
     *
     * @var mixed
     */
    protected $filters = null;

    /**
     * gateways
     *
     * @var mixed
     */
    protected $gateways = null;

    /**
     * isFormRoute
     *
     * @var mixed
     */
    protected $isFormRoute = false;

    /**
     * cloneAsSnapshot
     *
     * @var mixed
     */
    protected $cloneAsSnapshot = false;

    /**
     * __construct
     *
     * @param mixed $_input
     * @param int $_flags
     * @param string $_iteratorClass
     */
    public function __construct($_input = [], int $_flags=0, string $_iteratorClass='ArrayIterator')
    {
        $this->initialize();
        parent::__construct($_input, $_flags, $_iteratorClass);
    }

    /**
     * __clone
     *
     */
    public function __clone()
    {
        if (! $this->cloneAsSnapshot) {
            $this->initialize();
            $this->cloneAsSnapshot = false;
        }
    }

    /**
     * snapshot
     *
     */
    public function snapshot() : RequestModel
    {
        $this->cloneAsSnapshot = true;
        $result = clone $this;
        $this->cloneAsSnapshot = false;
        return $result;
    }

    /**
     * initialize
     *
     */
    protected function initialize()
    {
        $this->artificers = new Collection\Collection([]);
        $this->pipelineArtificers = new Collection\Collection([]);
        $this->subjects = new Collection\Collection([]);
        $this->subjectFilters = null;
        $this->dataSource = null;
        $this->gateways = null;
        $this->form = null;
        $this->options = [];
    }

    /**
     * initOnly - установка флага, указывающего на то,
     * что цепочка должна быть полностью проинициализирована
     * (см Qore\SynapseManager\Artificer\ArtificerBaseTrait::getLocalGateway())
     *
     * @param bool $_bool
     */
    public function initOnly(bool $_bool = null)
    {
        if (is_null($_bool)) {
            return $this->initOnly;
        }

        $this->initOnly = $_bool;
        return $this;
    }

    /**
     * setRequest
     *
     * @param ServerRequestInterface|null $_request
     */
    public function setRequest(ServerRequestInterface $_request = null) : RequestModel
    {
        $this->request = $_request;
        return $this;
    }

    /**
     * getRequest
     *
     */
    public function getRequest() : ?ServerRequest
    {
        return $this->request;
    }

    /**
     * Get route result
     *
     * @return ?RouteResult
     */
    public function getRouteResult() : ?RouteResult
    {
        if (is_null($this->request)) {
            return null;
        }

        return $this->request->getAttribute(RouteResult::class);
    }

    /**
     * getRelativeRouteName
     *
     */
    public function getRelativeRouteName(ArtificerInterface $_artificer)
    {
        $routeNamespace = $_artificer->getRoutesNamespace();
        if (is_null($routeResult = $this->getRouteResult())) {
            return '';
        }

        $routeName = $routeResult->getMatchedRouteName();
        return Qore::string()->substr($routeName, Qore::string()->strlen($routeNamespace)+1);
    }

    /**
     * isFormRoute
     *
     */
    public function formRoute(callable $_callback)
    {
        $currentRouteType = $this->isFormRoute;
        $this->isFormRoute = true;
        $result = $_callback();
        $this->isFormRoute = $currentRouteType;
        return $result;
    }

    /**
     * isFormRoute
     *
     */
    public function isFormRoute()
    {
        return $this->isFormRoute;
    }

    /**
     * getArtificers
     *
     */
    public function getArtificers() : ?Collection\CollectionInterface
    {
        return $this->artificers;
    }

    /**
     * hasArtificer
     *
     * @param string $artificer
     */
    public function hasArtificer(string $_artificerName) : bool
    {
        return $this->artificers->filter(function($_artificer) use ($_artificerName) {
            return $_artificer->getNameIdentifier() === $_artificerName;
        })->count() > 0;
    }

    /**
     * setArttificers
     *
     * @param Collection\CollectionInterface $_artificers
     */
    public function setArtificers(Collection\CollectionInterface $_artificers)
    {
        $this->artificers = $_artificers;
        if ($this->artificers->count() > $this->pipelineArtificers->count()) {
            $this->pipelineArtificers = $this->artificers;
        }
    }

    /**
     * setOptions
     *
     */
    public function setOptions(array $_options)
    {
        $this->options = $_options;
    }

    /**
     * getOptions
     *
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * getPipelineArtificers
     *
     */
    public function getPipelineArtificers()
    {
        return $this->pipelineArtificers;
    }

    /**
     * getSubjects
     *
     */
    public function getSubjects() : ?Collection\CollectionInterface
    {
        return $this->subjects;
    }

    /**
     * setSubjects
     *
     * @param Collection\CollectionInterface $_artificers
     */
    public function setSubjects(Collection\CollectionInterface $_subjects)
    {
        $this->subjects = $_subjects;
    }

    /**
     * getGateways
     *
     * @param mixed $_withAttributes
     */
    public function getGateways($_withAttributes = true)
    {
        return $_withAttributes || is_null($this->gateways)
            ? $this->gateways
            : $this->gateways->map(function($_gw){
                return $_gw['gateway'];
            });
    }

    /**
     * registerGateway
     *
     * @param mixed $_referenceName
     * @param mixed $_gateway
     */
    public function registerGateway(?string $_referenceName, $_gateway)
    {
        if (is_null($this->gateways)) {
            $this->gateways = new Collection\Collection([]);
        }

        $this->gateways = $this->gateways->appendItem([
            'namespace' => $this->artificers->reduce(function($_ns, $_artificer){
                return ($_ns ? $_ns . '.' : '') . $_artificer->getNameIdentifier();
            }, ''),
            'gateway' => $_gateway,
            'referenceName' => $_referenceName,
        ]);
    }

    /**
     * getFormManager
     *
     */
    public function getFormManager() : ?FormManager
    {
        return $this->form;
    }

    /**
     * setFormManager
     *
     * @param FormManager $_fm
     */
    public function setFormManager(FormManager $_fm = null)
    {
        $this->form = $_fm;
    }

    /**
     * getDataSource
     *
     */
    public function getDataSource() : ?Form\DataSource\DataSourceInterface
    {
        return $this->dataSource;
    }

    /**
     * setDataSource
     *
     * @param Form\DataSource\DataSourceInterface $_dataSource
     */
    public function setDataSource(Form\DataSource\DataSourceInterface $_dataSource = null) : RequestModel
    {
        $this->dataSource = $_dataSource;
        return $this;
    }

    /**
     * getFormEntities
     *
     * @param string $_namespace
     */
    public function getFormEntities(string $_namespace)
    {
        return $this->formEntities[$_namespace] ?? null;
    }

    /**
     * setFormEntity
     *
     * @param string $_namespace
     * @param mixed $_entity
     */
    public function setFormEntity(string $_namespace, $_entity)
    {
        $this->formEntities[$_namespace] = isset($this->formEntities[$_namespace])
            ? array_merge($this->formEntities[$_namespace], [$_entity])
            : [$_entity];
    }

    /**
     * registerFormField
     *
     * @param string $_namespace
     * @param mixed $_field
     */
    public function registerFormField(string $_namespace, Field\FieldInterface $_field)
    {
        $this->formFields[$_namespace] = isset($this->formFields[$_namespace])
            ? array_merge($this->formFields[$_namespace], [$_field->getName() => $_field])
            : [$_field->getName() => $_field];
    }

    /**
     * getFormFields
     *
     */
    public function getFormFields()
    {
        return $this->formFields;
    }

    /**
     * registerFieldPosition
     *
     */
    public function registerFieldOrder(string $_namespace, $_fieldName)
    {
        $this->fieldsOrder[$_namespace] = isset($this->fieldsOrder[$_namespace])
            ? array_merge($this->fieldsOrder[$_namespace], [$_fieldName])
            : [$_fieldName];
    }

    /**
     * getFieldPositions
     *
     */
    public function getFieldsOrder(){
        return $this->fieldsOrder;
    }

    /**
     * getSubjectFilters
     *
     * @param array $_subjectFilters
     */
    public function getSubjectFilters(array $_subjectFilters = null)
    {
        $_subjectFilters = $_subjectFilters ?? $this->subjectFilters ?? [];

        $return = [];
        foreach ($_subjectFilters as $subjectID => $attributes) {
            foreach ($attributes as $attributeID => $value) {
                $suffix = $value instanceof Filter ? $value->getTypeInstance()->getTypeSuffix() : '';
                $return[sprintf('_%s_%s%s', $subjectID, $attributeID, $suffix)] = $value instanceof Filter
                    ? $value->getTypeInstance()->valueToString()
                    : $value;
            }
        }

        return $return;
    }

    /**
     * getFiltersForSubject
     *
     * @param mixed $_subject
     */
    public function getFiltersForSubject($_subject = null)
    {
        if (is_null($_subject)) {
            $_subject = $this->getSubjects()->last();
            if ($_subject instanceof Structure\Entity\SynapseServiceFormField) {
                $_subject = $_subject->relatedSubject;
            }
        }

        if (is_null($this->subjectFilters)) {
            $this->scanSubjectsFiltersFromRequest();
        }

        return $this->subjectFilters[! is_null($_subject) ? $_subject['id'] : 0] ?? [];
    }

    /**
     * getFilters
     *
     * @param mixed $_relative
     */
    public function getFilters($_relative = false)
    {
        if ($_relative) {
            $currentNamespace = $this->artificers->reduce(function($_ns, $_artificer){
                return ($_ns ? $_ns . '.' : '') . $_artificer->getNameIdentifier();
            }, '');
            $currentArtificerNameIdentifier = $this->artificers->last()->getNameIdentifier();
            $filters = Qore::collection($this->filters->map(function($_filters) use ($currentNamespace, $currentArtificerNameIdentifier) {
                return array_merge($_filters, [
                    'namespace' => $currentNamespace === mb_substr($_filters['namespace'], 0, mb_strlen($currentNamespace))
                        ? $currentArtificerNameIdentifier . (
                            $_filters['namespace'] === $currentNamespace ? '' : '.' . mb_substr($_filters['namespace'], mb_strlen($currentNamespace)+1)
                        ) : null,
                ]);
            })->filter(function($_filters) {
                return ! is_null($_filters['namespace']);
            }));
        } else {
            $filters = $this->filters;
        }

        return $filters;
    }

    /**
     * registerFilters
     *
     * @param mixed $_filters
     * @param mixed $_namespace
     */
    public function registerFilters($_filters)
    {
        if (is_null($this->filters)) {
            $this->filters = new Collection\Collection([]);
        }

        $referencePath = '@this';
        $namespace = $this->artificers->reduce(function($_ns, $_artificer) use (&$referencePath){
            $namespace = ($_ns ? $_ns . '.' : '') . $_artificer->getNameIdentifier();
            $section = $this->gateways->match(['namespace' => $namespace])->extract('referenceName')->first();
            if ($_ns !== '' && ! is_null($section)) {
                $referencePath .= '.' . $section;
            }
            return $namespace;
        }, '');

        $this->filters = $this->filters->appendItem([
            'namespace' => $namespace,
            'filters' => $_filters,
            'referencePath' => $referencePath,
            'subject' => $this->getSubjects()->last(),
        ]);
    }

    /**
     * Set subject filters
     *
     * @param array $_subjectsFilters
     *
     * @return
     */
    public function setSubjectFilters(array $_subjectsFilters) : RequestModel
    {
        $this->subjectFilters = $this->scanSubjectsFilters($_subjectsFilters);
        return $this;
    }

    /**
     * Scan subjects filters from request
     *
     * @return void
     */
    protected function scanSubjectsFiltersFromRequest() : void
    {
        if (is_null($this->request)) {
            return;
        }

        $params = array_merge($this->request->getQueryParams(), $this->request->getParsedBody() ?? []);
        $this->subjectFilters = $this->scanSubjectsFilters($params);
    }

    /**
     * Scan subject fiters from array
     *
     * @param array $_source
     *
     * @return array
     */
    protected function scanSubjectsFilters(array $_source) : array
    {
        $result = [];
        foreach ($_source as $key => $value) {
            if (! $details = $this->getSubjectFilterDetails($key)) {
                continue;
            }
            $result[$details['subject']] = array_replace(
                $result[$details['subject']] ?? [],
                [$details['attribute'] => new Filter($value, $details['type'])]
            );
        }

        return $result;
    }

    /**
     * getSubjectFilterDetails
     *
     * @param string $_param
     */
    protected function getSubjectFilterDetails(string $_param)
    {
        $details = [];
        if (! preg_match('/^_([0-9]+)_([0-9]+|[a-z])([a-z])?$/', $_param, $details)) {
            return false;
        }

        return [
            'subject' => $details[1],
            'attribute' => $details[2],
            'type' => $details[3] ?? null,
        ];
    }

}
