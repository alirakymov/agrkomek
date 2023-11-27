<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Service\ManagerTrait;


use Qore\Front as QoreFront;
use Qore\Qore;
use Qore\ORM\Entity;
use Qore\Collection\Collection;
use Qore\SynapseManager\Structure\Entity\SynapseAttribute;
use Mezzio\Helper\UrlHelper;

/**
 * Trait: ListDecorator
 */
trait ListDecorator
{
    /**
     * getListComponent
     *
     * @param mixed $_data
     */
    public function getListComponent($_data = null)
    {
        $component = QoreFront\Protocol\Component\QCTable::get($this->getListComponentName());

        if (is_null($_data)) {
            return $component;
        }

        if ($_data === true) {
            $filters = $this->model->getFilters(true)->reduce(function($_result, $_artificerFilter){
                foreach ($_artificerFilter['filters'] as $attribute => $value) {
                    $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
                }
                return $_result;
            }, []);

            if ($this->isTreeStructure() && ! isset($filters['@this.__idparent'])) {
                $filters['@this.__idparent'] = 0;
            }

            $_data = $this->gateway($filters)->all();
        }

        if ($this->isTreeStructure()) {
            $filters = $this->normalizeFilters($this->model->getFilters(true));
            $component->setBreadcrumbs($this->getBreadcrumbs($filters['@this.__idparent'] ?? 0));
        }

        $routeResult = $this->model->getRouteResult();

        $options = [
            'url' => Qore::service(UrlHelper::class)->generate(
                $this->getRouteName('reload'),
                $routeResult->getMatchedParams(),
                $this->model->getSubjectFilters()
            ),
        ];

        if ($this->isSortable() && ! is_null($synapse = $this->getOptionsSynapse())) {
            $options['sortable'] = Qore::service(UrlHelper::class)->generate(
                $this->getRouteName('reorder'),
                $routeResult->getMatchedParams(),
                $this->model->getSubjectFilters()
            );

            $itemsOrder = $synapse['__options'][$this->getListOrderOptionName()] ?? [];
            $_data = $_data->sortBy(function($_item) use ($itemsOrder){
                return (int)array_search($_item->id, array_values($itemsOrder));
            }, SORT_ASC);
        }

        $actions = [
            'create' => [
                'icon' => 'fa fa-plus',
                'actionUri' => Qore::service(UrlHelper::class)->generate(
                    $this->getRouteName('create'),
                    $routeResult->getMatchedParams(),
                    $this->model->getSubjectFilters(),
                )
            ],
        ];

        return $component
            ->setActions($actions)
            ->setTableOptions($options)
            ->setTitle($this->entity->label)
            ->setTableData($this->getListColumns(), $_data->toList());
    }

    /**
     * getListColumns
     *
     */
    public function getListColumns()
    {
        $return = [
            'id' => [
                'label' => '#',
                'model-path' => 'id',
                'class-header' => sprintf($this->getCenterColumnExpression(), $this->getIDColumnWidth()),
                'class-column' => sprintf($this->getCenterColumnExpression(), $this->getIDColumnWidth()),
            ]
        ];

        $counter = 0;
        while ($column = $this->getColumn($counter++)) {
            $return = array_merge($return, $column);
        }

        $return['table-actions'] = [
            'label' => ' ... ',
            'class-header' => sprintf($this->getCenterColumnExpression(), $this->getActionsColumnWidth()),
            'class-column' => sprintf($this->getCenterColumnExpression(), $this->getActionsColumnWidth()),
            'actions' => $this->getListActions(),
        ];

        return $return;
    }

    /**
     * getColumn
     *
     * @param int $_counter
     */
    private function getColumn(int $_counter)
    {
        $attrCount = ($attrCount = $this->entity->synapse->attributes->count()) < $this->getMaxAttributes()
            ? $attrCount
            : $this->getMaxAttributes();

        if ($_counter >= $attrCount) {
            return false;
        }

        $attribute = $this->entity->synapse->attributes
            ->filter(function($_attribute){
                return
                    is_null($this->availableColumns)
                    || ! is_array($this->availableColumns)
                    || in_array($_attribute->name, $this->availableColumns);
            })
            ->take(1, $_counter)->first();

        $columnWidth = $_counter !== $attrCount - 1
            ? floor($this->getFreeColumns()/$attrCount)
            : ceil($this->getFreeColumns()/$attrCount);

        return [
            $attribute->name => [
                'label' => $this->prepareColumnLabel($attribute),
                'model-path' =>  $attribute->name,
                'class-header' => sprintf($this->getDefaultColumnExpression(), $columnWidth),
                'class-column' => sprintf($this->getDefaultColumnExpression(), $columnWidth),
                'transform' => $this->prepareColumnTransform($attribute, $_counter),
            ]
        ];
    }

    /**
     * getBreadcrumbs
     *
     * @param mixed $_iParent
     */
    protected function getBreadcrumbs($_iParent)
    {
        if ((int)$_iParent === 0) {
            return [
                [
                    'label' => 'Корень',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->getRouteName('index'), [], array_merge($this->model->getSubjectFilters(), $this->getFilters($this, [
                        '__idparent' => 0
                    ])))
                ]
            ];
        } else {
            $attribute = $this->entity->synapse->attributes->first();
            $result = $this->gateway([
                '@this.id' => $_iParent
            ])->one();
            return array_merge(
                $this->getBreadcrumbs($result['__idparent']),
                [
                    [
                        'label' => $result[$attribute->name],
                        'actionUri' => Qore::service(UrlHelper::class)->generate($this->getRouteName('index'), [], array_merge($this->model->getSubjectFilters(), $this->getFilters($this, [
                            '__idparent' => $result['id']
                        ]))),
                    ]
                ]
            );
        }
    }

    /**
     * reorder
     *
     */
    protected function reorder()
    {
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        if ($request->getMethod() === 'POST'
            && $this->isSortable() && ! is_null($synapse = $this->getOptionsSynapse())) {
            # - Save data
            $requestData = $request->parseJsonBody();
            if (isset($requestData['data'])) {
                $synapse['__options'] = array_merge($synapse['__options'] ?? [], [
                    $this->getListOrderOptionName() => $requestData['data']
                ]);
                Qore::service(\Qore\ORM\ModelManager::class)($synapse)->save();
            }
        }

        return $this->getResponseResult([
            'response' => QoreFront\ResponseGenerator::get()
        ]);
    }

    /**
     * getListComponentName
     *
     * @param string $_suffix
     */
    protected function getListComponentName(string $_suffix = null) : string
    {
        return $this->getNameIdentifier() . (! is_null($_suffix) ? '.' . $_suffix : '');
    }

    /**
     * getListOrderOptionName
     *
     */
    protected function getListOrderOptionName()
    {
        return str_replace('\\', '-', $this->getListComponentName()) . '-positions';
    }

    /**
     * getListActions
     *
     */
    protected function getListActions()
    {
        return $this->getDefaultListActions();
    }

    /**
     * getListActions
     *
     */
    protected function getDefaultListActions()
    {
        return array_merge($this->getDefaultUpdateAction(), $this->getDefaultDeleteAction());
    }

    /**
     * getDefaultUpdateAction
     *
     */
    protected function getDefaultUpdateAction()
    {
        return [
            'update' => [
                'label' => 'Редактировать',
                'icon' => 'fa fa-pencil',
                'actionUri' => function($_data) {
                    $routeResult = $this->model->getRouteResult();
                    return Qore::service(UrlHelper::class)->generate($this->getRouteName('update'), array_merge($routeResult->getMatchedParams(), ['id' => $_data->id]));
                },
            ],
        ];
    }

    /**
     * getDefaultDeleteAction
     *
     */
    protected function getDefaultDeleteAction()
    {
        return [
            'delete' => [
                'label' => 'Удалить',
                'icon' => 'fa fa-trash',
                'actionUri' => function($_data) {
                    $routeResult = $this->model->getRouteResult();
                    return Qore::service(UrlHelper::class)->generate($this->getRouteName('delete'), array_merge($routeResult->getMatchedParams(), ['id' => $_data->id]));
                },
                'confirm' => function($_data) {
                    return [
                        'title' => 'Удаление элемента',
                        'message' => sprintf('Вы действительно хотите удалить элемент "%s"?', $_data['name'] ?? $_data['title'] ?? $_data->id)
                    ];
                },
            ],
        ];
    }

    /**
     * prepareColumnTransforms
     *
     * @param mixed $_attribute
     * @param mixed $_counter
     */
    protected function prepareColumnTransform($_attribute, $_counter) : ?array
    {
        $result = $this->getColumnTransform($_attribute) ?? [];

        if (! $result) {
            $result[] = function ($_item) use ($_attribute) {
                return $_item[$_attribute->name] && mb_strlen($_item[$_attribute->name]) > 100
                    ? sprintf('%s ...', mb_substr($_item[$_attribute->name] ?? '', 0, 100))
                    : $_item[$_attribute->name] ?? '';
            };
        }

        if ($this->isTreeStructure() && $_counter === 0) {
            $result[] = function ($_item, $_original) use ($_attribute){
                return [
                    'label' => is_object($_item) ? $_item[$_attribute->name] : $_item,
                    'actionUri' => Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName('index'),
                        $this->model->getRouteResult()->getMatchedParams(),
                        array_merge($this->model->getSubjectFilters(), $this->getFilters($this, [
                            '__idparent' => $_original['id']
                        ]))
                    ),
                ];
            };
        }

        return $result ?: null;
    }

    /**
     * prepareColumnLabel
     *
     * @param mixed $_attribute
     */
    protected function prepareColumnLabel($_attribute)
    {
        return $this->getColumnLabel($_attribute);
    }

    /* ------------------------------------------------- */
    /* Abstract methods which define the output settings */
    /* ------------------------------------------------- */
    /**
     * isSortable
     *
     */
    abstract protected function isSortable() : bool;

    /**
     * getOptionsSynapse
     *
     */
    abstract protected function getOptionsSynapse() : ?Entity\EntityInterface;

    /**
     * getFreeColumns
     *
     */
    abstract protected function getFreeColumns() : int;

    /**
     * getMaxAttributes
     *
     */
    abstract protected function getMaxAttributes() : int;

    /**
     * getIDColumnWidth
     *
     */
    abstract protected function getIDColumnWidth() : int;

    /**
     * getActionsColumnWidth
     *
     */
    abstract protected function getActionsColumnWidth() : int;

    /**
     * getCenterColumnExpression
     *
     */
    abstract protected function getCenterColumnExpression() : string;

    /**
     * getDefaultColumnExpression
     *
     */
    abstract protected function getDefaultColumnExpression() : string;

    /**
     * getColumnTransforms
     *
     */
    abstract protected function getColumnTransform(SynapseAttribute $_attribute) : ?array;

    /**
     * getColumnLabel
     *
     * @param SynapseAttribute $_attribute
     */
    abstract protected function getColumnLabel(SynapseAttribute $_attribute) : string;

}
