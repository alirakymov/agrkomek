<?php

declare(strict_types=1);

namespace Qore\Console\Commands;

use Qore\Qore;
use Qore\Core\Exceptions\CommandException;
use Qore\ORM\ModelManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class DataSynchronizer extends SymfonyCommand
{
    /**
     * targets
     *
     * @var mixed
     */
    private $targets = [];

    /**
     * targetsReferencesMap
     *
     * @var mixed
     */
    private $targetsReferencesMap = [];

    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'data:synch';

    /**
     * configure
     *
     */
    protected function configure()
    {
        $this->setDescription('Синхронизация данных через файловую систему')
            ->addArgument('action', InputArgument::REQUIRED, 'list | export | import');
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->parseTargets();

        if ($input->getArgument('action') === 'export') {
            $this->export($output);
        } elseif ($input->getArgument('action') === 'import') {
            $this->import($output);
        } elseif ($input->getArgument('action') === 'list') {
            if (! $config = Qore::config('qore.data-synchronizer.targets', [])) {
                $output->writeln("<warning>No targets for synchronize</warning>");
            }
            foreach ($config as $namespace => $targets) {
                foreach ($targets as $name => $options) {
                    if (is_string($options)) {
                        $name = $options;
                    }
                    $output->writeln(sprintf("<info>Namespace</info>: %s <info>Target</info>: %s", $namespace, $name));
                }
            }
        }

        return 0;
    }

    /**
     * export
     *
     */
    protected function export($_output)
    {
        $exportSource = [];
        $this->targetsReferencesMap = [];

        $mm = Qore::service(ModelManager::class);
        # Save targets by namespace
        foreach ($this->targets as $target => $options) {
            $_output->writeln(sprintf("<info>Export</info>: %s", $target));
            $mapper = $mm->getMapper($target);
            $table = $mapper->getTable($target);

            $referenceFields = [];
            $references = Qore::collection($table->getReferences());
            $references->each(function($_reference) use (&$referenceFields) {
                $referenceMap = Qore::collection($_reference->getReferenceMap());
                if ($referenceMap->first()->getName() !== 'id') {
                    $referenceFields[$referenceMap->getOne(0)->getName()] = $referenceMap->getOne(1)->getTable();
                }
            });

            $result = [];
            $options['referenceFields'] = $referenceFields;
            $objects = $mm($target)->all();

            foreach ($objects as $object) {
                $array = $object->getArrayCopy();
                foreach ($referenceFields as $field => $referenceTarget) {
                    $array[$field] = sprintf(
                        '%s(%s)',
                        $referenceTarget->getEntityName(),
                        $this->targetsReferencesMap[sprintf(
                            '%s(%s)',
                            $referenceTarget->getEntityName(),
                            $array[$field]
                        )] ?? null
                    );
                }

                $uniqueIndex = $this->getUniqueIndex($options, $array);
                $this->targetsReferencesMap[sprintf('%s(%s)', $table->getEntityName(), $array['id'])] = $uniqueIndex;
                $result[sprintf('%s(%s)', $table->getEntityName(), $uniqueIndex)] = $this->prepareTargetResult($array, $options);
            }

            ksort($result);
            $exportSource[$target] = $result;
        }

        # - Apply specific mappings
        foreach ($this->targets as $target => $options) {
            if (! isset($options['specific-mapping'])) {
                continue;
            }
            foreach ($exportSource[$target] as &$source) {
                $source = $this->applySpecificMapping($source, $options['specific-mapping']);
            }
        }

        # - Save to files
        foreach ($this->targets as $target => $options) {
            $directory = $this->getNamespaceDirectory($options['namespace']);
            file_put_contents(
                sprintf('%s/%s.php', $directory, $target),
                str_replace('{config}', VarExporter::export($exportSource[$target] ?? []), $this->getDumpSnippet())
            );
        }
    }

    /**
     * import
     *
     */
    protected function import($_output)
    {
        $importSource = [];
        $this->targetsReferencesMap = [];

        $mm = Qore::service(ModelManager::class);
        # Save targets by namespace
        foreach ($this->targets as $target => $options) {
            $_output->writeln(sprintf("<info>Import</info>: %s", $target));
            $mapper = $mm->getMapper($target);
            $table = $mapper->getTable($target);

            $referenceFields = [];
            $references = Qore::collection($table->getReferences());
            $references->each(function($_reference) use (&$referenceFields) {
                $referenceMap = Qore::collection($_reference->getReferenceMap());
                if ($referenceMap->first()->getName() !== 'id') {
                    $referenceFields[$referenceMap->getOne(0)->getName()] = $referenceMap->getOne(1)->getTable();
                }
            });

            $result = [];
            $options['referenceFields'] = $referenceFields;
            $objects = $mm($target)->all();

            foreach ($objects as $object) {
                $object['unique'] = $this->getUniqueIndex($options, $object->getArrayCopy());
            }

            $directory = $this->getNamespaceDirectory($options['namespace']);
            $targetObjects = require sprintf('%s/%s.php', $directory, $target);
            $foundObjects = [];

            $isTreeStructure = false;
            foreach ($targetObjects as $key => $array) {
                unset($array['id']);
                $isTreeStructure = isset($array['iParent']);
                $uniqueIndex = $this->getUniqueIndex($options, $array);
                $original = $array;
                foreach ($referenceFields as $field => $referenceTarget) {
                    $array[$field] = $this->targetsReferencesMap[$array[$field]] ?? null;
                }

                if ($foundObject = $objects->firstMatch(['unique' => $this->getUniqueIndex($options, $array)])) {
                    $object = $foundObject->combine($array);
                    $foundObjects[] = $foundObject->id;
                } else {
                    $object = $mm($target, $array);
                }

                if ($isTreeStructure) {
                    $object['iParent'] = 0;
                }

                $mm($object)->save();

                if ($isTreeStructure) {
                    $object['iParent'] = $array['iParent'];
                }

                $targetObjects[$key] = $object;
                $this->targetsReferencesMap[sprintf('%s(%s)', $table->getEntityName(), $uniqueIndex)] = (int)$object['id'];
            }

            $importSource[$target] = $targetObjects;

            $destroyObjects = $objects->filter(function($_object) use ($foundObjects){
                return ! in_array($_object->id, $foundObjects, true);
            })->compile();

            $destroyObjects->count() && $mm($destroyObjects)->delete();
        }

        # - Apply specific mappings
        foreach ($this->targets as $target => $options) {
            if (! isset($options['specific-mapping'])) {
                continue;
            }

            foreach ($importSource[$target] as $source) {
                $this->applySpecificMapping($source, $options['specific-mapping']);
                $mm($source)->save();
            }
        }
    }

    /**
     * applySpecificMapping
     *
     * @param mixed $_source
     * @param array $_mappings
     */
    protected function applySpecificMapping($_source, array $_mappings)
    {
        foreach ($_mappings as $path => $mappingSource) {
            $expPath = explode('.', $path);
            if (is_null($source = $_source[$firstKey = array_shift($expPath)] ?? null)) {
                continue;
            }

            $mappingTarget = &$source;
            foreach ($expPath as $key) {
                if (is_array($mappingTarget) && isset($mappingTarget[$key])) {
                    $mappingTarget = &$mappingTarget[$key];
                } else {
                    continue 2;
                }
            }

            if (is_null($mappingTarget)) {
                continue;
            }

            if (is_array($mappingTarget)) {
                foreach ($mappingTarget as $key => $identifier) {
                    $targetKey = sprintf('%s(%s)', $mappingSource, $identifier);
                    if (isset($this->targetsReferencesMap[$targetKey])) {
                        $mappingTarget[$key] = $this->targetsReferencesMap[$targetKey];
                    } else {
                        unset($mappingTarget[$key]);
                    }
                }
            } else {
                $mappingTarget = $this->targetsReferencesMap[sprintf('%s(%s)', $mappingSource, $mappingTarget)] ?? null;
            }

            $_source[$firstKey] = $source;
        }

        return $_source;
    }

    /**
     * parse configs
     *
     */
    protected function parseTargets()
    {
        $targets = Qore::config('qore.data-synchronizer.targets', []);

        $this->targets = [];
        foreach ($targets as $namespace => $names) {
            foreach ($names as $key => $value) {
                $options = [];

                if (is_array($value)) {
                    $options = $value;
                    $value = $key;
                }

                if (preg_match('/([a-z]+)\(([a-z0-9, ]*)\)/i', str_replace(' ', '', $value), $matches) )  {
                    $name = $matches[1];
                    $unique = explode(',', $matches[2]);
                } else {
                    throw new CommandException(sprintf('Can not parse DataSynchronizer targets %s', $value));
                }

                $this->targets[$namespace . ':' . $name] = array_merge([
                    'unique' => $unique,
                    'namespace' => $namespace,
                ], $options);
            }
        }
    }

    /**
     * getNamespaceDirectory
     *
     * @param string $_namespace
     */
    protected function getNamespaceDirectory(string $_namespace)
    {
        $dumpDir = Qore::config('qore.data-synchronizer.dump-dir', false);
        $namespaceDir = $dumpDir . '/'. $_namespace;
        is_dir($namespaceDir) || mkdir($namespaceDir, 0755, true);

        return $namespaceDir;
    }

    /**
     * getUniqueIndex
     *
     * @param array $_unique
     * @param array $_array
     */
    protected function getUniqueIndex(array $_options, array $_array) : string
    {
        foreach ($_options['unique'] as &$index) {
            $index = $_array[$index] ?? 'null';
        }

        return implode(';', $_options['unique']);
    }

    /**
     * prepareTargetResult
     *
     * @param array $_result
     * @param array $_options
     */
    protected function prepareTargetResult(array $_result, array $_options) : array
    {
        unset($_result['id']);

        if (! isset($_options['exclude'])) {
            return $_result;
        }

        foreach ($_options['exclude'] as $key) {
            if (in_array($key, array_keys($_result))) {
                unset($_result[$key]);
            }
        }

        return $_result;
    }

    /**
     * getConfigSnippet
     *
     */
    protected function getDumpSnippet()
    {
        return <<<EOT
<?php

return {config};
EOT;
    }

}
