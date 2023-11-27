<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;

use Psr\Container\ContainerInterface;
use Qore\Collection\Collection;
use Qore\ORM\ModelManager;

class RepositoryCollectionLoader
{
    /**
     * servicesCollection
     *
     * @var mixed
     */
    private $servicesCollection = null;

    /**
     * formsCollection
     *
     * @var mixed
     */
    private $formsCollection = null;

    /**
     * @var ContainerInterface
     */
    private $_container;

    /**
     * Constructor
     *
     * @param \Psr\Container\ContainerInterface $_container
     */
    public function __construct(ContainerInterface $_container)
    {
        $this->_container = $_container;
    }

    /**
     * getCollectionOfServiceRepository
     *
     * @return \Qore\Collection\Collection
     */
    public function getCollectionOfServiceRepository() : Collection
    {
        if (is_null($this->servicesCollection)) {
            $this->loadServicesCollection();
        }

        return $this->servicesCollection;
    }

    /**
     * loadServicesCollection
     *
     * @return void
     */
    private function loadServicesCollection() : void
    {
        $config = $this->_container->get('config');
        $collectionFile = $config['qore']['synapse-configs']['services-collection-cache-file'] ?? false;
        if ($collectionFile && is_file($collectionFile)) {
            $this->servicesCollection = unserialize(file_get_contents($collectionFile));
        } else {
            $mm = $this->_container->get(ModelManager::class);
            $services = $mm('QSynapse:SynapseServices')
                ->with('subjectsFrom', function($_gw) {
                    $_gw->with('serviceFrom')
                        ->with('serviceTo')
                        ->with('relation');
                })
                ->with('synapse', function($_gw) {
                    $_gw->with('attributes');
                })
                ->with('forms')
                ->all();

            $artificers = [];
            foreach ($services as $service) {
                $artificers[] = $this->_container->build(Service\ServiceArtificer::class, [
                    'service' => $service,
                ]);
            }

            $this->servicesCollection = new Collection($artificers);
            $collectionFile && file_put_contents($collectionFile, serialize($this->servicesCollection));
        }
    }

    /**
     * getCollectionOfFormRepository
     *
     * @return \Qore\Collection\Collection
     */
    public function getCollectionOfFormRepository() : Collection
    {
        if (is_null($this->formsCollection)) {
            $this->loadFormsCollection();
        }

        return $this->formsCollection;
    }

    /**
     * loadFormsCollection
     *
     * @return void
     */
    private function loadFormsCollection() : void
    {
        $config = $this->_container->get('config');
        $collectionFile = $config['qore']['synapse-configs']['forms-collection-cache-file'] ?? false;
        if ($collectionFile && is_file($collectionFile)) {
            $this->formsCollection = unserialize(file_get_contents($collectionFile));
        } else {
            $mm = $this->_container->get(ModelManager::class);
            $forms = $mm('QSynapse:SynapseServiceForms')
                ->with('service', function($_gw) {
                    $_gw->with('synapse');
                })
                ->with('fields', function($_gw) {
                    $_gw->with('relatedForm')
                        ->with('relatedAttribute')
                        ->with('relatedSubject', function($_gw){
                            $_gw->with('relation');
                        });
                })
                ->all();

            $artificers = [];
            foreach ($forms as $form) {
                $artificers[] = $this->_container->build(Form\FormArtificer::class, [
                    'form' => $form,
                ]);
            }

            $this->formsCollection = new Collection($artificers);
            $collectionFile && file_put_contents($collectionFile, serialize($this->formsCollection));
        }
    }

}
