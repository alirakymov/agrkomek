<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;


use Qore\SynapseManager;
use Qore\SynapseManager\Structure;
use Qore\DealingManager;
use Psr\Container\ContainerInterface;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

/**
 * Class: ArtificerFactory
 *
 * @see DealingManager\ScenarioClauseInterface
 */
class ArtificerScenarioClause implements DealingManager\ScenarioClauseInterface
{
    /**
     * subject
     *
     * @var mixed
     */
    private $subject = null;

    /**
     * artificer
     *
     * @var mixed
     */
    private $artificer = null;

    /**
     * options
     *
     * @var mixed
     */
    private $options = [];

    /**
     * __construct
     *
     * @param ArtificerInterface $_artificer
     * @param mixed $_subject
     * @param array $_options
     */
    public function __construct(ArtificerInterface $_artificer, $_subject = null, array $_options = [])
    {
        $this->artificer = $_artificer;
        $this->subject = $_subject;
        $this->options = $_options;
    }

    /**
     * processClause
     *
     * @param mixed $_model
     * @param ScenarioInterface $_nextHandler
     */
    public function processClause($_model, DealingManager\ScenarioInterface $_nextHandler) : DealingManager\ResultInterface
    {
        if (! $_model instanceof RequestModel) {
            throw new ServiceArtificerException(sprintf('Model of service artificer must be an instance of %s', Artificer\RequestModel::class));
        }

        return $this->wrapToClauseEnvironment($_model, function () use ($_model, $_nextHandler) {
            return $this->artificer->inEnvironment($_model, $_nextHandler, function($_artificer) {
                return $_artificer->dispatch();
            });
        });
    }

    /**
     * getArtificer
     *
     */
    public function getArtificer() : ?ArtificerInterface
    {
        return $this->artificer;
    }

    /**
     * getSubject
     *
     */
    public function getSubject() : ?SynapseServiceSubject
    {
        return $this->subject;
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
     * wrapToClauseEnvironment
     *
     * @param callable $_callback
     */
    protected function wrapToClauseEnvironment($_model, callable $_callback)
    {
        $currentSubjects = $_model->getSubjects();
        $currentArtificers = $_model->getArtificers();
        $currentOptions = $_model->getOptions();

        $_model->setSubjects($currentSubjects->append([$this->subject]));
        $_model->setArtificers($currentArtificers->append([$this->artificer]));
        $_model->setOptions($this->options);

        $result = $_callback();

        $_model->setSubjects($currentSubjects);
        $_model->setArtificers($currentArtificers);
        $_model->setOptions($currentOptions);

        return $result;
    }

}
