<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Decorator;

use Qore\InterfaceGateway\Component\Table;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Collection\CollectionInterface;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Mezzio\Helper\UrlHelper;
use Qore\InterfaceGateway\Component\ComponentInterface;
use Qore\SynapseManager\Artificer\Service\Filter;

/**
 * Class: ListComponent
 *
 * @see BaseDecorator
 */
class ListComponent extends BaseDecorator
{
    /**
     * buildDecoration
     *
     * @param mixed $_data
     */
    public function buildDecoration($_data = null)
    {
        $component = Qore::service(InterfaceGateway::class)(Table::class, $this->getListComponentName());

        $reports = $this->getOption('reports', []);
        if ($reports instanceof CollectionInterface) {
            $reports = $reports->map(fn ($_report) => $_report->toArray(true))->toList();
        }

        $component->setOption('reports', $reports);

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

            if ($this->artificer->isTreeStructure() && ! isset($filters['@this.__idparent'])) {
                $filters['@this.__idparent'] = 0;
            }

            $_data = $this->artificer->gateway($filters)->all();
        }

        // TODO: fix it! if (! isset($filters) && $this->artificer->isTreeStructure())
        if ($this->artificer->isTreeStructure()) {
            $filters = $this->artificer->normalizeFilters($this->model->getFilters(true));
            $component->setBreadcrumbs($this->getBreadcrumbs($filters['@this.__idparent'] ?? 0));
        }

        $routeResult = $this->model->getRouteResult();

        $queryParams = $this->model->getRequest()->getQueryParams();
        $options = [
            'url' => Qore::service(UrlHelper::class)->generate(
                $this->artificer->getRouteName('reload'),
                $routeResult->getMatchedParams(),
                $queryParams ?: $this->model->getSubjectFilters()
            ),
        ];

        if (is_array($sortOrder = $this->sortable())) {
            $options['sortable'] = Qore::service(UrlHelper::class)->generate(
                $this->artificer->getRouteName('reorder'),
                $routeResult->getMatchedParams(),
                $this->model->getSubjectFilters()
            );

            ! is_null($_data) && $_data = $_data->sortBy(function($_item) use ($sortOrder) {
                return (int)array_search($_item->id, array_values($sortOrder));
            }, SORT_ASC);
        }

        if (! is_null($pagination = $this->getPagination())) {
            $options['pagination'] = $pagination;
            $options['pagination']['url'] ??= Qore::service(UrlHelper::class)->generate(
                $this->artificer->getRouteName('reload'),
                $routeResult->getMatchedParams(),
            );
            $params = $this->model->getRequest()->getQueryParams();
            if (isset($params['page'])) unset($params['page']);
            $options['pagination']['url-params'] = $params ?: $this->model->getSubjectFilters();
        }

        if ($filterForm = $this->filterForm()) {
            $component->component($filterForm);
        }

        $reports = $this->getOption('reports', []);

        if ($reports instanceof CollectionInterface) {
            $reports = $reports->map(fn ($_report) => $_report->toArray(true))->toList();
        }

        return $component
            ->setActions($this->getComponentActions())
            ->setTableOptions($options)
            ->setTitle($this->getTitle())
            ->setTableData($this->getListColumns(), $_data->toList());
    }

    /**
     * getListColumns
     *
     */
    public function getListColumns()
    {
        if (! $return = $this->getOption('columns', false)) {
            $return = [
                'id' => [
                    'label' => '#',
                    'model-path' => 'id',
                    'class-header' => sprintf($this->getCenterColumnExpression(), $this->getIDColumnSize()),
                    'class-column' => sprintf($this->getCenterColumnExpression(), $this->getIDColumnSize()),
                ]
            ];

            $counter = 0;
            while ($column = $this->getColumn($counter++)) {
                $return = array_merge($return, $column);
            }
        }

        if ($this->getOption('actions', []) !== false) {
            $return['table-actions'] = [
                'label' => ' ... ',
                'class-header' => sprintf($this->getCenterColumnExpression(), $this->getActionsColumnSize()),
                'class-column' => sprintf($this->getCenterColumnExpression(), $this->getActionsColumnSize()),
                'actions' => $this->getListActions(),
            ];
        }

        return $return;
    }

    /**
     * getColumn
     *
     * @param int $_counter
     */
    private function getColumn(int $_counter)
    {
        $attrCount = ($attrCount = $this->artificer->getEntity()->synapse->attributes->count()) < $this->getMaxColumns()
            ? $attrCount
            : $this->getMaxColumns();

        if ($_counter >= $attrCount) {
            return false;
        }

        $attribute = $this->artificer->getEntity()->synapse->attributes
            ->filter(function($_attribute){
                return
                    is_null($availableColumns = $this->getOption('availableColumns', null))
                    || ! is_array($availableColumns)
                    || in_array($_attribute->name, $availableColumns);
            })
            ->take(1, $_counter)->first();

        if (is_null($attribute)) {
            return false;
        }

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
     * getTitle
     *
     */
    protected function getTitle()
    {
        $defaultTitle = $this->artificer->getEntity()->label;
        return $this->getOption('title', $defaultTitle);
    }

    /**
     * getBreadcrumbs
     *
     * @param mixed $_iParent
     */
    protected function getBreadcrumbs($_iParent)
    {
        if ($_iParent instanceof Filter) {
            $_iParent = $_iParent->getTypeInstance()->getValue();
        }

        if ((int)$_iParent === 0) {
            return [
                [
                    'label' => 'Корень',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->artificer->getRouteName('index'), [], array_merge($this->model->getSubjectFilters(), $this->artificer->getFilters($this->artificer, [
                        '__idparent' => 0
                    ])))
                ]
            ];
        } else {
            $result = $this->artificer->gateway([
                '@this.id' => $_iParent
            ])->one();

            $attributes = $this->artificer->getEntity()->synapse->attributes();
            $attribute = $attributes->firstMatch(['name' => 'title'])
                ?? $attributes->firstMatch(['name' => 'label'])
                ?? $attributes->firstMatch(['name' => 'name'])
                ?? $attributes->first();

            return array_merge(
                $this->getBreadcrumbs($result['__idparent']),
                [
                    [
                        'label' => $result[$attribute->name],
                        'actionUri' => Qore::service(UrlHelper::class)->generate($this->artificer->getRouteName('index'), [], array_merge($this->model->getSubjectFilters(), $this->artificer->getFilters($this->artificer, [
                            '__idparent' => $result['id']
                        ]))),
                    ]
                ]
            );
        }
    }

    /**
     * getComponentActions
     *
     */
    protected function getComponentActions()
    {
        $actions = $this->getOption('componentActions', []);

        if ($actions === false) {
            return [];
        }


        if (! $actions) {
            $actions = ['create', 'reload'];
        }

        $defaultActions = $this->getDefaultComponentActions();
        foreach ($actions as &$actionOptions) {
            if (is_string($actionOptions) && isset($defaultActions[$actionOptions])) {
                $actionOptions = $defaultActions[$actionOptions]();
            }
        }

        return array_values($actions);
    }

    /**
     * getDefaultComponentActions
     *
     */
    protected function getDefaultComponentActions()
    {
        $routeResult = $this->model->getRouteResult();
        return [
            'create' => function() use ($routeResult) {
                return [
                    'icon' => 'fa fa-plus',
                    'actionUri' => Qore::service(UrlHelper::class)->generate(
                        $this->artificer->getRouteName('create'),
                        $routeResult->getMatchedParams(),
                        $this->model->getSubjectFilters()
                    )
                ];
            },
            'reload' => function() use ($routeResult) {
                return [
                    'icon' => 'fa fa-sync',
                    'actionUri' => Qore::service(UrlHelper::class)->generate(
                        $this->artificer->getRouteName('reload'),
                        $routeResult->getMatchedParams(),
                        $this->model->getSubjectFilters()
                    )
                ];
            },
        ];
    }

    /**
     * getListComponentName
     *
     * @param string $_suffix
     */
    protected function getListComponentName() : string
    {
        return $this->artificer->getRoutesNamespace() . (! is_null($suffix = $this->getOption('suffix', null)) ? '.' . $suffix : '');
    }

    /**
     * getListActions
     *
     */
    protected function getListActions()
    {
        $actions = $this->getOption('actions', []);
        $defaultActions = $this->getDefaultListActions();

        if ($actions === false) {
            return [];
        } elseif ($actions === []) {
            return $defaultActions;
        }

        foreach ($actions as $key => $actionOptions) {
            if (is_string($actionOptions) && isset($defaultActions[$actionOptions])) {
                $actions[$actionOptions] = $defaultActions[$actionOptions];
                unset($actions[$key]);
            }
        }

        return $actions;
    }

    /**
     * getListActions
     *
     */
    protected function getDefaultListActions()
    {
        return $this->getOption('defaultActions', array_merge($this->getDefaultUpdateAction(), $this->getDefaultDeleteAction()));
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
                'icon' => 'fas fa-pencil-alt',
                'actionUri' => function($_data) {
                    $routeResult = $this->model->getRouteResult();
                    $queryParams = $this->model->getRequest()->getQueryParams();
                    return Qore::service(UrlHelper::class)->generate(
                        $this->artificer->getRouteName('update'),
                        array_merge($routeResult->getMatchedParams(), ['id' => $_data->id]),
                        $queryParams ?: $this->model->getSubjectFilters()
                    );
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
                    $queryParams = $this->model->getRequest()->getQueryParams();
                    return Qore::service(UrlHelper::class)->generate(
                        $this->artificer->getRouteName('delete'),
                        array_merge($routeResult->getMatchedParams(), ['id' => $_data->id]),
                        $queryParams ?: $this->model->getSubjectFilters()
                    );
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
        $result = $this->getOption(sprintf('columnTransforms.%s', $_attribute->name), []);
        if ($this->artificer->isTreeStructure() && $_counter === 0) {
            $result[] = function ($_item) use ($_attribute){
                return [
                    'label' => $_item[$_attribute->name],
                    'actionUri' => Qore::service(UrlHelper::class)->generate(
                        $this->artificer->getRouteName('index'),
                        $this->model->getRouteResult()->getMatchedParams(),
                        array_merge($this->model->getSubjectFilters(), $this->artificer->getFilters($this->artificer, [
                            '__idparent' => $_item['id']
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
        $transformCallback = $this->getOption('columnLabelTransform', function($_attribute){
            return $_attribute->label;
        });

        return $transformCallback($_attribute);
    }

    /**
     * Get sortable option
     *
     * @return EntityInterface|false 
     */
    protected function sortable()
    {
        return $this->getOption('sortable', false);
    }

    /**
     * Get filter form
     *
     * @return ComponentInterface|null
     */
    protected function filterForm(): ?ComponentInterface
    {
        return $this->getOption('filter-form', null);
    }

    /**
     * Return pagination options
     *
     * @return ?array
     */
    protected function getPagination() : ?array
    {
        return $this->getOption('pagination', null);
    }

    /**
     * getFreeColumns
     *
     */
    protected function getFreeColumns() : int
    {
        return $this->getOption('gridSize', 12) - $this->getIDColumnSize() - $this->getActionsColumnSize();
    }

    /**
     * getMaxAttributes
     *
     */
    protected function getMaxColumns() : int
    {
        return ! is_null($availableColumns = $this->getOption('availableColumns', null)) && is_array($availableColumns)
            ? count($availableColumns)
            : $this->getOption('maxColumns', 4);
    }

    /**
     * getIDColumnSize
     *
     */
    protected function getIDColumnSize() : int
    {
        return $this->getOption('columnSizes.id', 1);
    }

    /**
     * getActionsColumnWidth
     *
     */
    protected function getActionsColumnSize() : int
    {
        return $this->getOption('columnSizes.actions', 2);
    }

    /**
     * getCenterHeaderExpression
     *
     */
    protected function getCenterColumnExpression() : string
    {
        return $this->getOption('columnExpressions.center', 'col-sm-%s text-center');
    }

    /**
     * getDefaultColumnExpression
     *
     */
    protected function getDefaultColumnExpression() : string
    {
        return $this->getOption('columnExpressions.default', 'col-sm-%s');
    }

}
