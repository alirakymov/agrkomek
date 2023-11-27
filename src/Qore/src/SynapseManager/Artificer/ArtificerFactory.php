<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;


use Qore\SynapseManager;
use Psr\Container\ContainerInterface;

/**
 * Class: SynapseFactory
 *
 */
class ArtificerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     * @param string $_requestedName
     * @param array $_options
     */
    public function __invoke(ContainerInterface $_container, string $_requestedName, array $_options = []) : ArtificerInterface
    {
        switch (true) {
            case $_requestedName === Service\ServiceArtificer::class:
                return $this->createServiceArtificer($_container, $_options);
            case $_requestedName === Form\FormArtificer::class:
                return $this->createFormArtificer($_container, $_options);
        }
    }

    /**
     * createSynapseArtificer
     *
     * @param ContainerInterface $_container
     * @param array $_options
     */
    private function createServiceArtificer(ContainerInterface $_container, array $_options = []) : Service\ServiceArtificer
    {
        $config = $_container->get('config');

        if (! isset($_options['service'])) {
            throw new ArtificerException('Undefined service option on build service artificer object');
        } elseif (! $_options['service'] instanceof SynapseManager\Structure\Entity\SynapseService) {
            throw new ArtificerException(sprintf('Synapse option must be instance of %s class', SynapseManager\Structure\Entity\SynapseService::class));
        } else {
            $service = $_options['service'];
        }

        $className = null;
        if ($namespaces = $config['qore']['synapse-configs']['namespaces'] ?? []) {
            foreach ($namespaces as $namespace) {
                $class = $namespace
                    . '\\' . $service->synapse->name
                    . '\\' . $service->name
                    . '\\' . $service->synapse->name . 'Service';

                if (class_exists($class)) {
                    $className = $class;
                    break;
                }

                $class = $namespace
                    . '\\' . $service->synapse->name
                    . '\\' . $service->name
                    . '\\SynapseService';

                if (class_exists($class)) {
                    $className = $class;
                    break;
                }
            }

        }

        if (! $className) {
            $className = Service\ServiceArtificer::class;
        }

        return new $className($service);
    }

    /**
     * createFormArtificer
     *
     * @param ContainerInterface $_container
     * @param array $_options
     */
    private function createFormArtificer(ContainerInterface $_container, array $_options = []) : Form\FormArtificer
    {
        $config = $_container->get('config');

        if (! isset($_options['form'])) {
            throw new ArtificerException('Undefined service option on build service artificer object');
        } elseif (! $_options['form'] instanceof SynapseManager\Structure\Entity\SynapseServiceForm) {
            throw new ArtificerException(sprintf('Synapse option must be instance of %s class', SynapseManager\Structure\Entity\SynapseServiceForm::class));
        } else {
            $form = $_options['form'];
        }

        $className = null;
        if ($namespaces = $config['qore']['synapse-configs']['namespaces'] ?? []) {
            foreach ($namespaces as $namespace) {
                if (! class_exists($className = $namespace
                    . '\\' . $form->service->synapse->name
                    . '\\' . $form->service->name
                    . '\\Forms'
                    . '\\' . ucfirst($form->name)
                )) {
                    $className = null;
                } else {
                    break;
                }
            }
        }

        if (! $className) {
            $className = Form\FormArtificer::class;
        }

        return new $className($form);
    }

}
