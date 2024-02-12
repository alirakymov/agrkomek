<?php

namespace Qore\App\SynapseNodes\Components\Operation;

use Qore\ORM\Entity\EntityInterface;

class OperationLauncher
{
    /**
     * @var \Qore\ORM\Entity\EntityInterface
     */
    private EntityInterface $target;

    /**
     * @var Operation
     */
    private Operation $operation;

    /**
     * @var array 
     */
    private array $options;

    /**
     * Construct
     *
     * @param Operation $_operation 
     * @param \Qore\ORM\Entity\EntityInterface $_target 
     * @param array $_options 
     */
    public function __construct(Operation $_operation, EntityInterface $_target, array $_options)
    {
        $this->target = $_target;
        $this->operation = $_operation;
        $this->options = $_options;
    }

    /**
     * Launch operation
     *
     * @return void
     */
    public function launch(): void
    {
        $phases = $this->operation->phases();

        if (! $phases) {
            return;
        }

        # - Get sort array
        $sortOrder = $this->operation['__options']
            ? ($this->operation['__options']['OperationPhase-order'] ?? null)
            : null;
        
        # - Sort phases 
        $sortOrder && $phases = $phases->sortBy(function($_item) use ($sortOrder) {
            return (int)array_search($_item->id, array_values($sortOrder));
        }, SORT_ASC);

        # - launch phases
        foreach ($phases as $phase) {
            $phaseClass = '\\' . $phase->getIdentifierHash();
            $instance = new $phaseClass($this->operation, $this->target, $this->options);

            if (! $instance->execute()) {
                break;
            }
        }
    }

}
