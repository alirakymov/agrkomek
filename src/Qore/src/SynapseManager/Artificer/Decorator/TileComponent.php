<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Decorator;

use Qore\Qore;
use Qore\App\SynapseNodes\System\Settings\Settings;
use Qore\Collection\CollectionInterface;
use Qore\Front\Protocol\Component\QCTile;
use Qore\ORM\Entity\EntityInterface;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Mezzio\Helper\UrlHelper;
use Qore\InterfaceGateway\Component\Tile;
use Qore\InterfaceGateway\InterfaceGateway;

/**
 * Class: TileComponent
 *
 * @see BaseDecorator
 */
class TileComponent extends BaseDecorator
{
    /**
     * buildDecoration
     *
     * @param mixed $_data
     */
    public function buildDecoration($_data = null)
    {
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

        $routeResult = $this->model->getRouteResult();

        $component = Qore::service(InterfaceGateway::class)(Tile::class, $this->getComponentName());

        $tileOptions = [
            'id' => $this->getAttributeCallback('id'),
            'label' => $this->getAttributeCallback('label'),
            'content' => $this->getAttributeCallback('content'),
            'data' => $this->getAttributeCallback('data'),
            'routes' => $this->getAttributeCallback('routes'),
            'tile-actions' => $this->getTileActions(),
        ];

        $options = [
            'url' => Qore::service(UrlHelper::class)->generate(
                $this->artificer->getRouteName('reload'),
                $routeResult->getMatchedParams(),
                $this->model->getSubjectFilters()
            ),
        ];

        if ($this->isSortable() && ! is_null($synapse = $this->getOptionsSynapse())) {
            $options['sortable'] = Qore::service(UrlHelper::class)->generate(
                $this->artificer->getRouteName('reorder'),
                $routeResult->getMatchedParams(),
                $this->model->getSubjectFilters()
            );

            $itemsOrder = $synapse['__options'][$this->getOrderOptionName()] ?? [];
            ! is_null($_data) && $_data = $_data->sortBy(function($_item) use ($itemsOrder){
                return (int)array_search($_item->id, array_values($itemsOrder));
            }, SORT_ASC);
        }

        # - Cropping options
        $croppingSettings = $this->getCroppingSettingsSynapse();
        $options['croppingSizes'] = Qore::collection($croppingSettings->getParam('sizes', []))->filter(function($_item) {
            return isset($_item['w'], $_item['h']);
        })->toList();

        $actions = $this->getActions();

        return $component
            ->setActions($actions)
            ->setTileOptions($options)
            ->setTitle($this->getTitle())
            ->setTileData($tileOptions, is_null($_data) ? [] : $_data->toList());
    }

    /**
     * getListComponentName
     *
     * @param string $_suffix
     */
    protected function getComponentName() : string
    {
        return $this->artificer->getNameIdentifier() . (! is_null($suffix = $this->getOption('suffix', null)) ? '.' . $suffix : '');
    }

    /**
     * getOrderOptionName
     *
     */
    protected function getOrderOptionName()
    {
        return str_replace('\\', '-', $this->getComponentName() . '-positions');
    }

    /**
     * getTitle
     *
     */
    protected function getTitle()
    {
        $defaultTitle = $this->artificer->getEntity()->synapse->name . ': ' . $this->artificer->getEntity()->label;
        return $this->getOption('title', $defaultTitle);
    }

    /**
     * isSortable
     *
     */
    protected function isSortable() : bool
    {
        return $this->getOption('sortable', false);
    }

    /**
     * getOptionsSynapse - get synapse that is storage for positions of ordered Entities
     *
     */
    protected function getOptionsSynapse() : ?EntityInterface
    {
        return $this->getOption('optionsSynapse', null);
    }

    /**
     * Find or generate cropping settings synapse entity
     *
     * @return Settings
     */
    public function getCroppingSettingsSynapse() : Settings
    {
        $settingsName = sprintf('%s.cropping', $this->artificer->getEntity()->synapse()->name);
        $settings = $this->artificer->mm('SM:Settings')->where(function($_where) use ($settingsName) {
            $_where(['@this.name' => $settingsName]);
        })->one() ?? $this->artificer->mm('SM:Settings', [
            'name' => $settingsName,
        ]);

        return $settings;
    }

    /**
     * getActions
     *
     */
    protected function getActions()
    {
        $routeResult = $this->model->getRouteResult();
        return array_merge(
           $this->getOption('defaultActions', [
                'create' => [
                    'icon' => 'fa fa-plus',
                    'actionUri' => Qore::service(UrlHelper::class)->generate(
                        $this->artificer->getRouteName('create'),
                        $routeResult->getMatchedParams(),
                        $this->model->getSubjectFilters()
                    )
                ],
            ]),
            $this->getOption('actions', [])
        );
    }

    /**
     * getActions
     *
     */
    protected function getTileActions()
    {
        $routeResult = $this->model->getRouteResult();
        return array_merge(
            $this->getOption('tileActions', []),
            $this->getOption('defaultTileActions', [
                'update' => [
                    'label' => 'Редактировать',
                    'icon' => 'fa fa-pencil',
                    'actionUri' => function($_item) use ($routeResult) {
                        return Qore::service(UrlHelper::class)
                            ->generate(
                                $this->artificer->getRouteName('update'),
                                array_merge($routeResult->getMatchedParams(), ['id' => $_item->id]),
                                $this->model->getSubjectFilters()
                            );
                    },
                ],
                'delete' => [
                    'label' => 'Удалить',
                    'icon' => 'fa fa-trash',
                    'actionUri' => function($_item) use ($routeResult) {
                        return Qore::service(UrlHelper::class)->generate(
                            $this->artificer->getRouteName('delete'),
                            array_merge($routeResult->getMatchedParams(), ['id' => $_item->id]),
                            $this->model->getSubjectFilters()
                        );
                    },
                    'confirm' => function($_item) {
                        return [
                            'title' => 'Удаление объекта',
                            'message' => sprintf('Вы действительно хотите удалить "%s"?', strip_tags($_item['label'] ?? $_item['name'] ?? $_item['id'])),

                        ];
                    },
                ],
            ])
        );
    }

    /**
     * getAttributeCallback
     *
     * @param mixed $_attribute
     */
    protected function getAttributeCallback($_attribute)
    {
        return $this->getOption('callbacks.' . $_attribute, $this->getDefaultAttributeCallback($_attribute));
    }

    /**
     * getDefaultAttributeCallback
     *
     * @param mixed $_attribute
     */
    protected function getDefaultAttributeCallback($_attribute)
    {
        switch(true) {
            case $_attribute === 'id':
                return $this->getDefaultIDAttributeCallback();
            case $_attribute === 'label':
                return $this->getDefaultLabelAttributeCallback();
            case $_attribute === 'content':
                return $this->getDefaultContentAttributeCallback();
            case $_attribute === 'data':
                return $this->getDefaultDataAttributeCallback();
            default:
                return null;
        }
    }

    /**
     * getDefaultIDAttributeCallback
     *
     */
    protected function getDefaultIDAttributeCallback()
    {
        return function($_item) {
            return $_item->id;
        };
    }

    /**
     * getDefaultLabelAttributeCallback
     *
     */
    protected function getDefaultLabelAttributeCallback()
    {
        return function($_item) {
            $routeResult = $this->model->getRouteResult();
            return [
                'title' => $_item['label'] ?? $_item['name'] ?? $_item['id'],
                'actionUri' => Qore::service(UrlHelper::class)->generate(
                    $this->artificer->getRouteName('update'),
                    array_merge($routeResult->getMatchedParams(), ['id' => $_item->id])
                ),
            ];
        };
    }

    /**
     * getDefaultContentAttributeCallback
     *
     */
    protected function getDefaultContentAttributeCallback()
    {
        return function($_item) {
            $routeResult = $this->model->getRouteResult();
            return [
                'type' => 'image',
                'source' => [
                    'thumb' => $this->getOption('content.thumb', null),
                    'full-image' => $this->getOption('content.full-image', null),
                ],
                'title' => $_item['label'] ?? $_item['name'] ?? $_item['id'],
                'actionUri' => Qore::service(UrlHelper::class)->generate(
                    $this->artificer->getRouteName('update'),
                    array_merge($routeResult->getMatchedParams(), ['id' => $_item->id])
                ),
            ];
        };
    }

    /**
     * getDefaultDataAttributeCallback
     *
     */
    protected function getDefaultDataAttributeCallback()
    {
        return function($_item) {
            return $_item;
        };
    }

}
