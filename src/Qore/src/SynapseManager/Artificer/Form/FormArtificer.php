<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Form;


use Laminas\Diactoros\Response\JsonResponse;
use Qore\Qore;
use Qore\Collection\Collection;
use Qore\DealingManager;
use Qore\Form\Decorator\QoreFront;
use Qore\Form\Protector\TightnessProtector;
use Qore\ORM\Entity\EntityInterface;
use Qore\Form\Field;
use Qore\Form\FormManager;
use Qore\SynapseManager\Artificer;
use Qore\SynapseManager\Artificer\Form\DataSource\RequestDataSource;
use Qore\SynapseManager\Artificer\RequestModel;
use Qore\SynapseManager\Artificer\Service\Filter;
use Qore\SynapseManager\Structure\Entity;
use Qore\Router\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\SynapseManager\Structure\Entity\SynapseServiceFormField;
use Laminas\Db\Sql\Predicate\PredicateInterface;
use Qore\SynapseManager\Artificer\Form\DataSource\EntityDataSource;

/**
 * Class: FormArtificer
 *
 * @see ServiceArtificerInterface
 */
class FormArtificer extends Artificer\ArtificerBase implements FormArtificerInterface
{
    /**
     * entity
     *
     * @var mixed
     */
    protected $entity = null;

    /**
     * @var \Qore\SynapseManager\Artificer\RequestModel|null
     */
    protected ?RequestModel $model = null;

    /**
     * next
     *
     * @var mixed
     */
    protected $next = null;

    /**
     * routesNamespace
     *
     * @var mixed
     */
    protected $routesNamespace = null;

    /**
     * fieldsNamespace
     *
     * @var mixed
     */
    protected $fieldsNamespace = null;

    /**
     * gateway
     *
     * @var mixed
     */
    protected $gateway = null;

    /**
     * form
     *
     * @var mixed
     */
    protected $form = null;

    /**
     * synapseEntity
     *
     * @var mixed
     */
    protected $synapseEntity = null;

    /**
     * requestEntity
     *
     * @var mixed
     */
    protected $requestEntity = null;

    /**
     * requestFilters
     *
     * @var mixed
     */
    protected $requestFilters = [];

    /**
     * validators
     *
     * @var mixed
     */
    protected $validators = [];

    /**
     * @var
     */
    protected $mountParentField = true;

    /**
     * __construct
     *
     * @param Entity\SynapseService $_entity
     */
    public function __construct(Entity\SynapseServiceForm $_entity)
    {
        $this->entity = $_entity;
    }

    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        # - For register some routes
    }

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param RequestHandlerInterface $_handler
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        # - Generate RequestModel instance
        $model = $this->getRequestModel($_request);
        # - Initialize data source if method is post
        if ($_request->getMethod() === 'POST') {
            $model->setDataSource(new RequestDataSource($_request, $this->sm, $this));
        }
        # - Launch dealing manager
        $result = $this->dm()->setModel($model)->launch();
        # - Return response
        return $result['response'] ?? new JsonResponse([]);
    }

    /**
     * compile
     *
     */
    public function compile() : ?DealingManager\ResultInterface
    {
        # - Mount fields of this service form
        $this->model->initOnly() || $this->mountFormStructure();
        return null;
    }

    /**
     * mountFormStructure
     *
     */
    public function mountFormStructure()
    {
        if (is_null($data = $this->model->getFormEntities($this->fieldsNamespace))) {
            $data = ! is_null($dataSource = $this->model->getDataSource())
                ? $dataSource->extractData()
                : [$this->sm->mm($this->entity->service->synapse->name, [])];
        }

        $data = is_array($data) ? new Collection($data) : $data;
        if ($this->getFormType() === Entity\SynapseServiceForm::FORM_MULTIPLE_SELECTION) {
            $this->setupMultipleSelectionFields($data);
        } else {
            foreach ($data as $formEntity) {
                if ($this->getFormType() === Entity\SynapseServiceForm::FORM_HIDDEN_SELECTION) {
                    $this->setupHiddenSelectionFields($formEntity);
                } else {
                    $this->setupAttributeFields($formEntity);
                }
            }
        }
    }

    /**
     * marshalForm
     *
     */
    public function marshalForm()
    {
        $this->marshalFormFieldsByNamespace($fm = $this->model->getFormManager(), $this->getNameIdentifier());
        $fm->setName($this->getFormName());
        return $fm;
    }

    /**
     * dispatch
     *
     */
    public function dispatch() : ?DealingManager\ResultInterface
    {
        # - Dispatch route result with actions of this subject
        if (! $this->model->initOnly() && ! is_null($result = $this->compile())) {
            return $result;
        }

        # - Dispatch route result with actions of related subjects
        if (! is_null($result = $this->next->process($this->model))) {
            return $result;
        }

        return null;
    }

    /**
     * getNameIdentifier
     *
     */
    public function getNameIdentifier() : string
    {
        return $this->entity->service->synapse->name . ':' . $this->entity->service->name . '#' . $this->entity->name;
    }

    /**
     * getIdentifier
     *
     */
    public function getIdentifier()
    {
        return $this->entity->id;
    }

    /**
     * getRoutesNamespace
     *
     */
    public function getRoutesNamespace()
    {
        return $this->routesNamespace;
    }

    /**
     * getFieldsNamespace
     *
     */
    public function getFieldsNamespace()
    {
        return $this->fieldsNamespace;
    }
/**
     * initEnvironment
     *
     * @param callable $_callback
     */
    public function inEnvironment($_model, DealingManager\ScenarioInterface $_nextHandler, callable $_callback)
    {
        $currentState = $this->fixState();

        # - Set new environment
        $this->model = $_model;
        $this->next = $_nextHandler;

        # - Initialize environment
        $this->initializeEnvironment();

        $result = null;
        # - Execute application in wrapped local events registry
        $this->sm->getEventManager()->wrapWithRegistry(function($_em, $_registry) use (&$result, $_callback) {
            $this->subscribe($_em);
            $result = $_callback($this);
        });

        # - Rollback to current state
        $this->setState($currentState);

        # - Reinitialize current environment
        $this->initializeEnvironment(true);

        return $result;
    }

    /**
     * getSubjectsArtificers
     *
     */
    public function getSubjectsArtificers() : array
    {
        return $this->sm->getServicesRepository()
            ->findByID($this->entity->service->id)
            ->getSubjectsArtificers();
    }

    /**
     * getFormsArtificers
     *
     */
    public function getFormsArtificers() : array
    {
        if (! $this->entity->fields) {
            return [];
        }

        $return = [];
        foreach ($this->entity->fields as $field) {
            if ($field->isForm()) {
                $return[] = $this->sm->getFormsRepository()->findByID($field->iSynapseServiceSubjectForm);
            }
        }

        return $return;
    }

    /**
     * getSynapseReferenceName
     *
     * @param Entity\SynapseRelation $_relation
     * @param Entity\SynapseService $_service
     */
    public function getSynapseReferenceName(Entity\SynapseRelation $_relation, Entity\SynapseService $_service = null)
    {
        $synapse = is_null($_service) ? $this->entity->service->synapse : $_service->synapse;
        return (int)$synapse->id === (int)$_relation->iSynapseTo
            ? $_relation->synapseAliasTo
            : $_relation->synapseAliasFrom;
    }

    /**
     * getFormType
     *
     */
    public function getFormType()
    {
        return (int)$this->entity->type;
    }

    /**
     * isClearSelectionForm
     *
     */
    public function isClearSelectionForm() : bool
    {
        return $this->getFormType() === Entity\SynapseServiceForm::FORM_MULTIPLE_SELECTION;
    }

    /**
     * setupAttributeFields
     *
     * @param FormManager $_fm
     * @param mixed $_data
     */
    protected function setupAttributeFields($_entity)
    {
        # - Register reference field
        $this->setupReferenceField($_entity);
        # - Register id parent field if synapse has tree structure
        (int)$this->entity->service->synapse->tree == 1
            && $this->mountParentField
            && $this->setupIDParentField($_entity);

        /** @var array<SynapseServiceFormField> */
        $fields = $this->entity->fields;
        if (! $fields) {
            return;
        }

        # - Sort fields collection
        if (isset($this->entity['__options']['fields-order'])) {
            $fieldsOrder = array_values($this->entity['__options']['fields-order']);
            $fields = $fields->sortBy(function($_item) use ($fieldsOrder) {
                return (int)array_search($_item->id, $fieldsOrder);
            }, SORT_ASC);
        }

        # - Register attribute fields of this form
        $formFields = [];
        foreach ($fields as $fieldEntity) {
            # - Setup attribute field
            if ($fieldEntity->isAttribute()) {
                $this->triggerEventsForSetupField($fieldEntity, $_entity, function() use ($fieldEntity, $_entity) {
                    # - Register field in model field list
                    /** @var Field\FieldInterface */
                    $field = new $fieldEntity->attributeFieldType($fieldName = $fieldEntity->getFormFieldName($this, $_entity), [
                        'type' => $fieldEntity->attributeFieldType,
                        'label' => $fieldEntity->label,
                        'placeholder' => $fieldEntity->placeholder ?: $fieldEntity->label,
                        'info' => $fieldEntity->description,
                        'validators' => $this->getFieldValidators($fieldEntity, $_entity),
                    ]);

                    $this->model->registerFormField($this->fieldsNamespace, $field);
                    # - Register field order
                    $this->model->registerFieldOrder($this->fieldsNamespace, $fieldName);
                    # - Set data to field
                    if (isset($_entity[$fieldEntity->relatedAttribute->name])) {
                        $field->setData($_entity[$fieldEntity->relatedAttribute->name]);
                    }

                    return $field;
                });
            # - Setup related form fields
            } else {
                $this->triggerEventsForSetupField($fieldEntity, $_entity, function() use ($fieldEntity, $_entity) {
                    # - Get related form artificer
                    $artificer = $this->sm->getFormsRepository()->findByID($fieldEntity->iSynapseServiceSubjectForm);
                    $artificerNamespace = $this->fieldsNamespace . '/' . $artificer->getNameIdentifier();
                    /* $referenceName = $this->getSynapseReferenceName($fieldEntity->relatedSubject->relation); */
                    $referenceName = $fieldEntity->relatedSubject->getReferenceName();
                    # - Set artificer entities
                    if (isset($_entity[$referenceName])
                        && ( $_entity[$referenceName] instanceof Collection
                            && $_entity[$referenceName]->count()
                            || $_entity[$referenceName] instanceof EntityInterface)
                    ) {
                        if ($_entity[$referenceName] instanceof Collection) {
                            $_entity[$referenceName]->each(function($_referenceEntity) use ($artificerNamespace, $_entity) {
                                $_referenceEntity['_sm_reference'] = $_entity->id;
                                $this->model->setFormEntity($artificerNamespace, $_referenceEntity);
                            });
                        } else {
                            $_entity[$referenceName]['_sm_reference'] = $_entity->id;
                            $this->model->setFormEntity($artificerNamespace, $_entity[$referenceName]);
                        }
                    } else {
                        $this->model->setFormEntity($artificerNamespace, $artificer->mm(null, ['_sm_reference' => $_entity->id]));
                    }
                    # - Register field position for sorting after processing all clauses
                    $this->model->registerFieldOrder($this->fieldsNamespace, $artificerNamespace);

                    return null;
                });
            }
        }
    }

    /**
     * getFieldValidators
     *
     * @param SynapseServiceFormField $_field
     */
    protected function getFieldValidators(SynapseServiceFormField $_field, $_entity = null)
    {
        $validators = $this->getValidators();
        $fieldValidators = $validators[$_field->getValidatorIndex() ?? 'default'] ?? $validators['default'] ?? [];
        if (is_callable($fieldValidators)) {
            $fieldValidators = $fieldValidators($_field, $_entity);
        }
        return $fieldValidators;
    }

    /**
     * getValidators
     *
     */
    protected function getValidators() : array
    {
        return $this->validators;
    }

    /**
     * triggerEventsForSetupField
     *
     * @param mixed $_fieldEntity
     * @param callable $_callback
     */
    protected function triggerEventsForSetupField($_fieldEntity, $_entity, callable $_callback)
    {
        # - Flush global event (without considering namespace)
        $globalEventResponse = $this->sm->getEventManager()->trigger($_fieldEntity->getFieldEventName($this, 'init.before', self::GLB), null, [
            'artificer' => $this,
            'field-entity' => $_fieldEntity,
            'model' => $this->model,
            'entity' => $_entity,
        ]);

        # - Flush local event (considering namespace)
        $localEventResponse = $this->sm->getEventManager()->trigger($_fieldEntity->getFieldEventName($this, 'init.before', self::LCL), null, [
            'artificer' => $this,
            'field-entity' => $_fieldEntity,
            'model' => $this->model,
            'entity' => $_entity,
            'global-event-response' => $globalEventResponse,
        ]);

        if ($globalEventResponse->contains(false) || $localEventResponse->contains(false)) {
            return;
        }

        $field = $_callback();

        # - Flush global event (without considering namespace)
        $globalEventResponse = $this->sm->getEventManager()->trigger($_fieldEntity->getFieldEventName($this, 'init.after', self::GLB), $field, [
            'artificer' => $this,
            'field-entity' => $_fieldEntity,
            'model' => $this->model,
            'entity' => $_entity,
        ]);

        # - Flush local event (considering namespace)
        $this->sm->getEventManager()->trigger($_fieldEntity->getFieldEventName($this, 'init.after', self::LCL), $field, [
            'artificer' => $this,
            'field-entity' => $_fieldEntity,
            'model' => $this->model,
            'entity' => $_entity,
            'global-event-response' => $globalEventResponse,
        ]);
    }

    /**
     * setupMultipleSelectionFields
     *
     * @param mixed $_data
     */
    protected function setupMultipleSelectionFields($_data)
    {
        $this->triggerEventsForSetupMultipleSelectionFields($_data, function($_fieldEntity, $_data) {
            # - Exit if referenced entities is absent
            if (is_null($entities = $this->model->getFormEntities($this->fieldsNamespace))) {
                return;
            }
            $referenceEntity = (new Collection($entities))->first();

            # - Register reference field
            $this->setupReferenceField(['id' => '*', '_sm_reference' => $referenceEntity['_sm_reference'] ?? null]);

            $items = $this->getItemsForSelectField();

            $fieldName = $this->fieldsNamespace . '/id[*]';
            $isO2O = $_fieldEntity->relatedSubject->isToOne();

            # - Register id field
            $this->model->registerFormField($this->fieldsNamespace, $field = new Field\TreeSelect($fieldName, [
                'type' => Field\TreeSelect::class,
                'label' => $_fieldEntity->label,
                'placeholder' => $_fieldEntity->placeholder ?: $_fieldEntity->label,
                'info' => $_fieldEntity->description,
                'options' => $items,
                'additional' => [
                    'multi' => ! $isO2O,
                    'flat' => $isO2O,
                ]
            ]));

            # - Register field order
            $this->model->registerFieldOrder($this->fieldsNamespace, $fieldName);

            # - Prepare selected items
            $_data = $_data->filter(function($_entity){
                return ! $_entity->isNew();
            })->map(function($_entity){
                return $_entity['id'];
            });

            if ($_data->count()) {
                $_data = $_data->toList();
            } elseif (isset($this->requestFilters['id'])) {
                $_data = is_array($this->requestFilters['id'])
                    ? $this->requestFilters['id']
                    : [$this->requestFilters['id']];
            } else {
                $_data = [];
            }

            # - Prepare reference value
            foreach ($_data as &$item) {
                if ($item instanceof Filter) {
                    $item = $item->getTypeInstance()->valueToString();
                }
            }

            # - Set reference value
            $field->setData($isO2O ? array_shift($_data) : $_data);

            return $field;
        });
    }

    /**
     * Get items for multiple select field
     *
     * @return array
     */
    protected function getItemsForSelectField(): array
    {
        $filters = $this->model->getFilters(true)->reduce(function($_result, $_artificerFilter){
            foreach ($_artificerFilter['filters'] as $attribute => $value) {
                $index = is_object($value) && $value instanceof PredicateInterface
                    ? count($_result)
                    : sprintf('@this.%s', $attribute);
                $_result[$index] = $value;
            }
            return $_result;
        }, []);

        # - Берем gateway от сервиса, а не от формы,
        # т.к. данная форма может не работать с необходимым набором субъектов
        $service = $this->sm($this->entity->service->getSynapseServiceName());
        $gw = $service->getLocalGateway($filters);

        # - Get and prepare items
        $items = $gw->all()->map(function($_item){
            $search = $replace = [];
            foreach ($_item as $key => $value) {
                if (is_scalar($_item[$key])) {
                    $search[] = '$' . $key;
                    $replace[] = $_item[$key];
                }
            }
            $_item['title'] = str_replace($search, $replace, $this->entity->template);
            return [
                'id' => $_item['id'],
                '__idparent' => $_item['__idparent'] ?? 0,
                'label' => $_item['title'] ?: 'item: ' . $_item['id'],
            ];
        })->nest('id', '__idparent');

        return $items->toList();
    }

    /**
     * triggerEventsForSetupMultipleSelectionFields
     *
     * @param mixed $_data
     * @param callable $_callback
     */
    protected function triggerEventsForSetupMultipleSelectionFields($_data, callable $_callback)
    {
        # - Convert selected data to collection
        if (! $_data instanceof Collection) {
            $_data = new Collection($_data);
        }

        # - Get field entity of parent form
        $fieldEntity = $this->model->getSubjects()->last();

        # - Flush global event (without considering namespace)
        $globalEventPreffix = $this->getPreffix(self::GLB) . '/id[*]';
        $globalEventResponse = $this->sm->getEventManager()->trigger($globalEventPreffix . '@init.before', null, [
            'artificer' => $this,
            'field-entity' => $fieldEntity,
            'model' => $this->model,
            'data' => $_data,
        ]);

        # - Flush local event (considering namespace)
        $localEventPreffix = $this->getPreffix(self::LCL) . '/id[*]';
        $localEventResponse = $this->sm->getEventManager()->trigger($localEventPreffix . '@init.before', null, [
            'artificer' => $this,
            'field-entity' => $fieldEntity,
            'model' => $this->model,
            'data' => $_data,
            'global-event-response' => $globalEventResponse,
        ]);

        $field = $_callback($fieldEntity, $_data);

        # - Flush global event (without considering namespace)
        $globalEventResponse = $this->sm->getEventManager()->trigger($globalEventPreffix . '@init.after', $field, [
            'artificer' => $this,
            'field-entity' => $fieldEntity,
            'model' => $this->model,
            'data' => $_data,
        ]);

        # - Flush local event (considering namespace)
        $this->sm->getEventManager()->trigger($localEventPreffix . '@init.after', $field, [
            'artificer' => $this,
            'field-entity' => $fieldEntity,
            'model' => $this->model,
            'data' => $_data,
            'global-event-response' => $globalEventResponse,
        ]);
    }

    /**
     * setupHiddenSelectionFields
     *
     * @param mixed $_entity
     */
    protected function setupHiddenSelectionFields($_entity)
    {
        # - No reference with new entity
        if ($_entity->isNew() && ! isset($this->requestFilters['id'])) {
            return;
        } elseif ($_entity->isNew()) {
            $_entity->id = (string)$this->requestFilters['id'];
        }
        # - Register reference field
        $this->setupReferenceField($_entity);
    }

    /**
     * setupSystemFields
     *
     * @param mixed $_entity
     */
    protected function setupReferenceField($_entity)
    {
        $this->triggerEventsForSetupReferenceField($_entity, function($_entity){
            # - Register reference field
            $this->model->registerFormField($this->fieldsNamespace, $field = new Field\Hidden($fieldName = $this->fieldsNamespace . '/_sm_reference[' . $_entity['id'] . ']', [
                'type' => Field\Hidden::class,
            ]));

            # - Register field order
            $this->model->registerFieldOrder($this->fieldsNamespace, $fieldName);
            # - Set reference value

            $field->setData($_entity['_sm_reference'] ?? null);

            return $field;
        });
    }

    /**
     * triggerEventsForSetupReferenceField
     *
     * @param mixed $_data
     * @param callable $_callback
     */
    protected function triggerEventsForSetupReferenceField($_data, callable $_callback)
    {
        # - Get field entity of parent form
        $fieldEntity = $this->model->getSubjects()->last();

        # - Flush global event (without considering namespace)
        $globalEventPreffix = $this->getPreffix(self::GLB) . '/_sm_reference';
        $globalEventResponse = $this->sm->getEventManager()->trigger($globalEventPreffix . '@init.before', null, [
            'artificer' => $this,
            'field-entity' => $fieldEntity,
            'model' => $this->model,
            'data' => $_data,
        ]);

        # - Flush local event (considering namespace)
        $localEventPreffix = $this->getPreffix(self::LCL) . '/_sm_reference';
        $localEventResponse = $this->sm->getEventManager()->trigger($localEventPreffix . '@init.before', null, [
            'artificer' => $this,
            'field-entity' => $fieldEntity,
            'model' => $this->model,
            'data' => $_data,
            'global-event-response' => $globalEventResponse,
        ]);

        $field = $_callback($_data);

        # - Flush global event (without considering namespace)
        $globalEventResponse = $this->sm->getEventManager()->trigger($globalEventPreffix . '@init.after', $field, [
            'artificer' => $this,
            'field-entity' => $fieldEntity,
            'model' => $this->model,
            'data' => $_data,
        ]);

        # - Flush local event (considering namespace)
        $this->sm->getEventManager()->trigger($localEventPreffix . '@init.after', $field, [
            'artificer' => $this,
            'field-entity' => $fieldEntity,
            'model' => $this->model,
            'data' => $_data,
            'global-event-response' => $globalEventResponse,
        ]);
    }

    /**
     * setupIDField
     *
     * @param mixed $_entity
     */
    protected function setupIDField($_entity)
    {
        # - Register id field
        $this->model->registerFormField($this->fieldsNamespace, $field = new Field\Hidden($fieldName = $this->fieldsNamespace . '/id[' . $_entity->id . ']', [
            'type' => Field\Hidden::class,
        ]));
        # - Register id order
        $this->model->registerFieldOrder($this->fieldsNamespace, $fieldName);
        # - Set id value
        $field->setData($_entity->id);
    }

    /**
     * setupIDParentField
     *
     * @param mixed $_entity
     */
    protected function setupIDParentField($_entity)
    {
        $value = null;
        if ($_entity->isNew() && ! isset($_entity['__idparent'])) {
            $value = isset($this->requestFilters['__idparent']) ? $this->requestFilters['__idparent'] : 0;
        } else {
            $value = $_entity['__idparent'];
        }

        $filters = $this->model->getFilters()->reduce(function($_result, $_artificerFilter) {
            foreach ($_artificerFilter['filters'] as $attribute => $value) {
                $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
            }
            return $_result;
        }, []);

        $gw = $this->getLocalGateway($this->model['temp-filters'] ?? null, $model = clone $this->model)
            ->where(function($_where) use ($_entity, $filters) {
                is_numeric($_entity->id) && $_where->notEqualTo('@this.id', $_entity->id);
                foreach ($filters as $attribute => $value) {
                    if ($attribute !== '@this.__idparent') {
                        if ($value instanceof Filter) {
                            $value->apply(fn($_v) => $_where([$attribute => $_v]));
                        } else {
                            $_where([$attribute => $value]);
                        }
                    }
                }
            });

        # - Get and prepare items
        $items = $gw->all()->map(function($_item){
            $replace = [];
            foreach ($_item as $key => $value) {
                if (is_scalar($value)) {
                    $replace['$' . $key] = $value;
                }
            }
            $_item['title'] = str_replace(array_keys($replace), array_values($replace), $this->entity->template);
            return [
                'id' => (int)$_item['id'],
                '__idparent' => (int)$_item['__idparent'],
                'label' => $_item['title'] ?: $_item['name'] ?? 'item: ' . $_item['id']
            ];
        })->prependItem([
            'id' => 0,
            '__idparent' => null,
            'label' => 'Корень'
        ])->nest('id', '__idparent');

        # - Register id field
        $this->model->registerFormField($this->fieldsNamespace, $field = new Field\TreeSelect($fieldName = $this->fieldsNamespace . '/__idparent[' . $_entity->id . ']', [
            'type' => Field\TreeSelect::class,
            'label' => 'Родительский объект',
            'placeholder' => 'Выберите родительский объект',
            'info' => 'объекты данного синапса имеют вложенную структуру',
            'options' => $items->toList(),
            'additional' => [
                'multi' => false # - TODO: fix this with type of reference
            ]
        ]));

        # - Register id parent order
        $this->model->registerFieldOrder($this->fieldsNamespace, $fieldName);
        # - Set id parent value
        $field->setData((string)$value);
    }

    /**
     * marshalFormFieldsByNamespace
     *
     */
    protected function marshalFormFieldsByNamespace(FormManager $_fm, string $_namespace)
    {
        $fieldsOrder = $this->model->getFieldsOrder();
        if (! isset($fieldsOrder[$_namespace])) {
            return;
        }

        $formFields = $this->model->getFormFields();
        foreach ($fieldsOrder[$_namespace] as $subjectName) {
            if (isset($formFields[$_namespace][$subjectName])) {
                $_fm->setField($formFields[$_namespace][$subjectName]);
            } elseif (isset($fieldsOrder[$subjectName])) {
                $this->marshalFormFieldsByNamespace($_fm, $subjectName);
            }
        }
    }

    /**
     * isFirstArtificer
     *
     */
    protected function isFirstArtificer() : bool
    {
        return $this->fieldsNamespace === $this->getNameIdentifier();
    }

    /**
     * getStateSigns
     *
     */
    protected function getStateSigns() : array
    {
        return ['model', 'next', 'gateway', 'form'];
    }

    /**
     * initializeEnvironment
     *
     * @param mixed $_restoreState
     */
    protected function initializeEnvironment($_restoreState = false)
    {
        # - Initialize environment
        $this->initRoutesNamespace($_restoreState);
        # - Initialize environment
        $this->initFieldsNamespace($_restoreState);
        # - Initialize gateway
        $this->initGateway($_restoreState);
        # - Initialize filters
        $this->initFilters($_restoreState);
        # - Initialize form manager
        $this->initFormManager($_restoreState);
    }

    /**
     * initFieldsNamespace
     *
     */
    protected function initFieldsNamespace($_restoreState)
    {
        if (is_null($this->model)) {
            $this->fieldsNamespace = null;
            return;
        }

        $artificers = $this->model->getArtificers()->filter(function($_artificer){
            return $_artificer instanceof FormArtificerInterface;
        });

        if (! $artificers->count()) {
            $this->fieldsNamespace = null;
            return;
        }

        $this->fieldsNamespace = $artificers->first()->getNameIdentifier();

        if ($artificers->count() > 1) {
            $this->fieldsNamespace .= '/' . $artificers->skip(1)->reduce(function ($_ns, $artificer) {
                return ($_ns ? $_ns . '/' : '') . $artificer->getNameIdentifier();
            }, '');
        }
    }

    /**
     * initFilters
     *
     */
    protected function initFilters($_restoreState)
    {
        if (is_null($this->model)) {
            $this->requestFilters = [];
            return;
        }

        $filters = $this->model->getFiltersForSubject();
        $attributes = $this->entity->service->synapse->attributes;

        $subjectFilters = [];
        foreach ($attributes as $attribute) {
            if (! isset($filters[$attribute->id])) {
                continue;
            }
            $subjectFilters[$attribute->name] = $filters[$attribute->id];
        }

        if (isset($filters[0])) {
            $subjectFilters['id'] = $filters[0];
        }

        if (isset($filters['p'])) {
            $subjectFilters['__idparent'] = $filters['p'];
        }

        $this->requestFilters = $subjectFilters;

        # - Register filters in model if is initialize of state
        $_restoreState || $this->model->registerFilters($this->requestFilters);
    }

    /**
     * initForm
     *
     */
    protected function initFormManager($_restoreState)
    {
        # - Quit if model is null
        if (is_null($this->model)) {
            return;
        }

        # - Initialize form
        if (is_null($form = $this->model->getFormManager())) {
            $this->model->setFormManager($form = $this->sm->getContainer()->get(FormManager::class)(
                $this->getFormName()
            ));

            $form->setProtectors([Qore::service(TightnessProtector::class)])
                 ->setDefaultDecorator($this->sm->getContainer()->get(QoreFront::class))
                 ->setRequest($this->model->getRequest());
        }

        $this->form = $form;
    }

    /**
     * getFormName
     *
     */
    protected function getFormName()
    {
        $entity = ! is_null($dataSource = $this->model->getDataSource())
            ? $dataSource->extractData()->first()
            : null;

        if (is_null($entity)) {
            $entity = $this->mm([]);
            $this->model->setDataSource(new EntityDataSource($entity));
        }

        return sprintf(
            '%s.%s%s',
            $this->getRoutesNamespace(),
            $this->getNameIdentifier(),
            $entity ? '@' . $entity->id : ''
        );
    }

    /**
     * getArtificerScenarioClauses
     *
     */
    protected function getArtificerScenarioClauses()
    {
        if (! $this->entity->fields) {
            return [];
        }

        $return = [];
        foreach ($this->entity->fields as $field) {
            if ($field->isForm()) {
                $return[] = new Artificer\ArtificerScenarioClause(
                    $this->sm->getFormsRepository()->findByID($field->iSynapseServiceSubjectForm),
                    $field
                );
            }
        }

        return $return;
    }

}
