<?php

namespace Qore\App\SynapseNodes\System\Page;

use ArrayAccess;
use Mezzio\Helper\UrlHelper;
use Qore\App\SynapseNodes\System\PageWidget\PageWidget;
use Qore\App\SynapseNodes\System\Page\Executor\PageService;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity;
use Qore\SynapseManager\SynapseManager;


/**
 * Class: SynapseEntity
 *
 * @see Entity\SynapseBaseEntity
 */
class Page extends Entity\SynapseBaseEntity
{
    const COMPONENT_DATA = 'pageComponentData';
    const WIDGET_DATA = 'widgetData';

    /**
     * @var array system services
     */
    private static $systemServices = [
        'empty-service' => 'Простая информационная страница',
    ];

    /**
     * Return system service label
     *
     * @return ?string
     */
    public function getSystemServiceLabel() : ?string
    {
        return $this->isSystemService() ? static::$systemServices[$this->componentService] : null;
    }

    /**
     * Check service type for non exsits system services
     *
     * @return bool
     */
    public function isSystemService() : bool
    {
        return in_array($this->componentService, array_keys(static::$systemServices));
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::after('initialize', function($_e) {
            $page = $_e->getTarget();
            $page['route'] = Qore::url(
                sprintf('%s.%s', PageService::class, 'index'),
                isset($page['url']) && $page['url'] !== '/' ? ['page' => $page->url] : []
            );
        });

        parent::subscribe();
    }

    /**
     * getRoute
     *
     */
    public function generateRoute()
    {
        if ($this->url === '/') {
            return $this->url;
        }

        $pageExecutor = Qore::service(SynapseManager::class)('Pages:Executor');
        return Qore::service(UrlHelper::class)->generate(
            $pageExecutor->getRouteName(get_class($pageExecutor), 'index'),
            ['page' => $this->url]
        );
    }

    /**
     * Set component data
     *
     * @param string $_index
     * @param mixed $_value
     *
     * @return Page
     */
    public function setComponentData(string $_index, $_value) : Page
    {
        $this[static::COMPONENT_DATA] = array_merge($this[static::COMPONENT_DATA] ?? [], [$_index => $_value]);
        return $this;
    }

    /**
     * Return registered component data
     *
     * @param string|null $_index (optional)
     *
     * @return mixed
     */
    public function getComponentData(string $_index = null)
    {
        return is_null($_index)
            ? ($this[static::COMPONENT_DATA] ?? [])
            : ($this[static::COMPONENT_DATA][$_index] ?? null);
    }

    /**
     * Set widget data
     *
     * @param mixed $_index
     * @param mixed $_value
     *
     * @return Page
     */
    public function setWidgetData(PageWidget $_widget, $_value) : Page
    {
        $_index = $_widget->name;
        if (is_array($_value) || $_value instanceof ArrayAccess) {
            $_value['__widget'] = $_widget;
            $_widget['__target'] = $_value;
        }

        $this[static::WIDGET_DATA] = array_merge($this[static::WIDGET_DATA] ?? [], [$_index => $_value]);
        return $this;
    }

    /**
     * Return registered widget data
     *
     * @param string|null $_index (optional)
     *
     * @return mixed
     */
    public function getWidgetData(string $_index = null)
    {
        return is_null($_index)
            ? ($this[static::WIDGET_DATA] ?? [])
            : ($this[static::WIDGET_DATA][$_index] ?? null);
    }

    /**
     * return all defined system services
     *
     * @return
     */
    public static function getSystemServices() : array
    {
        return static::$systemServices;
    }

}
