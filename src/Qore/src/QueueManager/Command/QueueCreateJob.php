<?php

declare(strict_types=1);

namespace Qore\QueueManager\Command;

use Qore\Qore;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class QueueCreateJob extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'queue:create-job';

    /**
     * config
     *
     * @var mixed
     */
    private $config = [];

    /**
     * setConfig
     *
     * @param array $_config
     * @return void 
     */
    public function setConfig(array $_config) : void
    {
        $this->config = $_config;
    }

    /**
     * configure
     *
     * @return void 
     */
    protected function configure() : void
    {
        $this->setDescription('Создание и регистрация нового класса поручения')
            ->addArgument('action', InputArgument::REQUIRED, 'Действие: inspect-аннулирование регистрации несуществующих поручений | create-создание нового поручения')
            ->addArgument('name', InputArgument::OPTIONAL, 'Полное имя класса');
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);

        $action = $input->getArgument('action');
        if ($action == 'inspect') {
            return $this->inspect($input, $output);
        } elseif ($action == 'create') {
            return $this->create($input, $output);
        } else {
            $io->error(sprintf('Unknown action `%s`!', $action));
        }

        return 0;
    }

    /**
     * inspect
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function inspect(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Inspect jobs');

        $config = $this->getQueueConfig();

        foreach ($config['qore']['queue-manager']['jobs'] as $key => $job) {
            if (! class_exists($job)) {
                $output->writeln(sprintf("<comment>Unset: </comment> %s", $job));
                unset($config['qore']['queue-manager']['jobs'][$key]);
            }
        }

        $this->saveQueueConfig($config);
        $output->writeln("<info>Complete</info>");

        return 0;
    }

    /**
     * create
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function create(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create/Register new job');

        $file = $this->getFile($input->getArgument('name'));

        $namespace = $this->getNamespace($input->getArgument('name'));
        $class = $this->getClassName($input->getArgument('name'));

        if (file_exists($file)) {
            $io->note('A job with this name already exists. We will register it.');
        } else {
            $replacements = [
                '{namespace}' => $namespace,
                '{class}' => $class,
            ];

            file_put_contents($file, str_replace(
                array_keys($replacements),
                array_values($replacements),
                $this->getJobSnippet()
            ));
        }

        $output->writeln([
            "<info>Job name</info>\n" . $input->getArgument('name'),
            "<info>Job address</info>\n" . realpath($file),
        ]);

        $config = $this->getQueueConfig();
        $jobClass = '\\' . $namespace . '\\' . $class;

        if (array_search($jobClass, $config['qore']['queue-manager']['jobs']) === false) {
            array_push($config['qore']['queue-manager']['jobs'], '\\' . $namespace . '\\' . $class);
        }

        $this->saveQueueConfig($config);

        return 0;
    }

    /**
     * getConfig
     *
     * @return array
     */
    protected function getQueueConfig() : array
    {
        $config = $this->config['config_file'];
        if (file_exists($config)) {
            $config = (function() use ($config) {
                return require $config;
            })();
        } else {
            $config = [];
        }

        if (! isset($config['qore'])) {
            $config['qore'] = [];
        }

        if (! isset($config['qore']['queue-manager'])) {
            $config['qore']['queue-manager'] = [];
        }

        if (! isset($config['qore']['queue-manager']['jobs'])) {
            $config['qore']['queue-manager']['jobs'] = [];
        }

        return $config;
    }

    /**
     * saveQueueConfig
     *
     * @param array $_config
     * @return void
     */
    protected function saveQueueConfig(array $_config) : void
    {
        file_put_contents(
            $this->config['config_file'],
            str_replace('{config}', VarExporter::export($_config), $this->getConfigSnippet())
        );
    }

    /**
     * Get file name with full path
     *
     * @param string $_job
     * @return string|null
     */
    protected function getFile(string $_job) : ?string
    {
        $namespace = $this->getNamespace($_job);
        if (strpos($_job, '\\') !== false) {
            $_job = mb_substr($_job, mb_strlen($namespace) + 1);
        }

        $namespaces = $this->getNamespaces();
        $baseSegment = $segment = '';
        $sections = explode('\\', $namespace);
        foreach ($sections as $section) {
            $segment .= $section . '\\';
            if (isset($namespaces[$segment])) {
                $baseSegment = $segment;
            }
        }

        if (! $baseSegment) {
            return null;
        }

        if (! is_dir($directory = $namespaces[$baseSegment] . '/' . implode('/', explode('\\', mb_substr($namespace, mb_strlen($baseSegment)))))) {
            mkdir($directory, 0755, true);
        }

        return $directory . '/' . $_job . '.php';
    }

    /**
     * getNamespace
     *
     * @param string $_namespace
     */
    protected function getNamespace(string $_job) : string
    {
        if (strpos($_job, '\\') === false) {
            $_job = static::class;
        }

        $sections = explode('\\', $_job);
        array_pop($sections);
        return implode('\\', $sections);
    }

    /**
     * getClassName
     *
     * @param string $_job
     */
    protected function getClassName(string $_job) : string
    {
        if (strpos($_job, '\\') === false) {
            return $_job;
        }

        $sections = explode('\\', $_job);
        return array_pop($sections);
    }

    /**
     * getNamespaces
     *
     */
    protected function getNamespaces() : array
    {
        return Qore::collection(Qore::service('loader')->getPrefixesPsr4())->map(function($_el) {
            return array_shift($_el);
        })->toArray();
    }

    /**
     * getConfigSnippet
     *
     */
    protected function getConfigSnippet() : string
    {
        return <<<EOT
<?php

return {config};
EOT;
    }

    /**
     * getCommandSnippet
     *
     */
    protected function getJobSnippet() : string
    {
        return <<<EOT
<?php

namespace {namespace};

use Qore\QueueManager\JobAbstract;
use Qore\QueueManager\JobInterface;
use Qore\QueueManager\JobTrait;

/**
 * Class: {class}
 *
 * @see JobInterface
 * @see JobAbstract
 */
class {class} extends JobAbstract implements JobInterface
{
    use JobTrait;

    protected static \$name = null;
    protected static \$persistence = false;
    protected static \$acknowledgement = true;
    protected static \$workersNumber = 1;

    /**
     * process
     *
     */
    public function process() : bool
    {
        \dump(\$this->task);
    }

}
EOT;

    }

}

