<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Operation;

use Qore\App\SynapseNodes\Components\OperationPhase\OperationPhase;
use Qore\ORM\Entity\EntityInterface;
use Qore\QScript\QScript;
use Throwable;

class OperationConstructor implements OperationConstructorInterface 
{
    /**
     * @var string - path of boostraping file for parallel
     */
    private string $_bootstrap;

    /**
     * @var string - cache directory
     */
    private string $_cacheDirectory;

    /**
     * @var QScript - qscript compiler
     */
    private QScript $_qscript;

    /**
     * Constructor
     *
     * @param string $_bootstrap - path of boostraping file for parallel
     * @param string $_cacheDirectory
     * @param \Qore\QScript\QScript $_qscript
     */
    public function __construct(
        string $_bootstrap, 
        string $_cacheDirectory,
        QScript $_qscript
    ) {
        $this->_bootstrap = $_bootstrap;
        $this->_cacheDirectory = $_cacheDirectory;
        $this->_qscript = $_qscript;
    }

    /**
     * Build operation thread
     *
     * @param Operation $_operation 
     * @param \Qore\ORM\Entity\EntityInterface $_target 
     * @param array $_options (optional)
     *
     * @return OperationRuntime|null 
     */
    public function build(Operation $_operation, EntityInterface $_target, array $_options = []): ?OperationRuntime
    {
        $runtime = new OperationRuntime($this->_bootstrap, $_operation, $_options);

        foreach ($_operation->phases() as $phase) {
            $this->preparePhaseCode($phase);
            $phaseFile = sprintf('%s/%s.php', $this->_cacheDirectory, $phase->getIdentifierHash());
            if ($phase->isModified() || ! file_exists($phaseFile)) {
                $code = $this->preparePhaseCode($phase);
                if (is_null($code)) {
                    return null;
                }
                file_put_contents($phaseFile, $code);
            }
        }

        return $runtime;
    }

    /**
     * Prepare phase code
     *
     * @param \Qore\App\SynapseNodes\Components\OperationPhase\OperationPhase $_phase
     *
     * @return string|null
     */
    protected function preparePhaseCode(OperationPhase $_phase): ?string
    {
        $template = $this->getCodeTemplate();

        try {
            # - Parse phase code
            $code = $this->_qscript->parse($_phase->script);
            # - Generate array with replacements
            $replace = [
                '{{ classname }}' => $_phase->getIdentifierHash(),
                '{{ phase-code }}' => $code->compile(),
            ];

            return str_replace(array_keys($replace), array_values($replace), $template);

        } catch(Throwable $e) {
            dump($e);
        }

        return null;
    }

    /**
     * Return template
     *
     * @return string
     */
    protected function getCodeTemplate(): string
    {
        return <<<PHASE
        <?php

        declare(strict_types=1);

        use Qore\\App\\SynapseNodes\\Components\\Operation\\OperationPhaseExecutor;

        class {{ classname }} extends OperationPhaseExecutor
        {
            /**
             * @inheritdoc
             */
            public function execute(): bool
            {
                \$target = \$this->getTarget();
                \$options = \$this->getOptions();

                {{ phase-code }}
            }
        }
        PHASE;
    }

}
