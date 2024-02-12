<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Operation;

use Generator;
use Laminas\ConfigAggregator\GlobTrait;

class OperationPhasesProvider implements OperationPhasesProviderInterface 
{
    use GlobTrait;

    /**
     * @var string
     */
    private string $_storagePath;

    /**
     * Construct
     *
     * @param string $_storagePath
     */
    public function __construct(string $_storagePath)
    {
        $this->_storagePath = $_storagePath;
    }

    /**
     * @inheritdoc
     */
    public function getPhases(): Generator
    {
        $pattern = sprintf('%s/phase_*', $this->_storagePath);

        foreach ($this->glob($pattern) as $file) {
            yield $file;
        }
    }

}

