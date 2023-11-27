<?php

declare(strict_types=1);

namespace Qore\Console\Commands;

use Qore\Qore;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class: Command
 *
 * @see SymfonyCommand
 */
class Command extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'create:command';

    /**
     * configure
     *
     */
    protected function configure()
    {
        $this->setDescription('Создание новой команды.')
            ->addArgument('name', InputArgument::REQUIRED, 'Название класса команды');
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create/Register new command');

        $file = $this->getFile($input->getArgument('name'));

        $namespace = $this->getNamespace($input->getArgument('name'));
        $class = $this->getClassName($input->getArgument('name'));

        if (file_exists($file)) {
            $io->note('A command with this name already exists. We will register it.');
        } else {
            $replacements = [
                '{namespace}' => $namespace,
                '{class}' => $class,
            ];

            file_put_contents(
                $file,
                str_replace(array_keys($replacements), array_values($replacements), $this->getCommandSnippet())
            );
        }

        $output->writeln([
            "<info>Command name</info>\n" . $input->getArgument('name'),
            "<info>Command address</info>\n" . realpath($file),
        ]);

        $config = $this->getConsoleConfig();
        $commandClass = '\\' . $namespace . '\\' . $class;

        if (array_search($commandClass, $config['dependencies']['invokables']) === false) {
            array_push($config['dependencies']['invokables'], '\\' . $namespace . '\\' . $class);
        }

        if (array_search($commandClass, $config['console']['commands']) === false) {
            array_push($config['console']['commands'], '\\' . $namespace . '\\' . $class);
        }

        file_put_contents(
            Qore::config('qore.paths.console_config_file'),
            str_replace('{config}', VarExporter::export($config), $this->getConfigSnippet())
        );

        return 0;
    }

    /**
     * getConfig
     *
     */
    protected function getConsoleConfig() : array
    {
        $config = Qore::config('qore.paths.console_config_file');
        if (file_exists($config)) {
            $config = (function() use ($config) {
                return require $config;
            })();
        } else {
            $config = [];
        }

        if (! isset($config['dependencies']) || ! isset($config['dependencies']['invokables'])) {
            $config['dependencies'] = array_merge($config['dependencies'] ?? [], [
                'invokables' => []
            ]);
        }

        if (! isset($config['console']) || ! isset($config['console']['commands'])) {
            $config['console'] = array_merge($config['console'] ?? [], [
                'commands' => []
            ]);
        }

        return $config;
    }

    /**
     * getFile
     *
     * @param string $_command
     */
    protected function getFile(string $_command) : ?string
    {
        $namespace = $this->getNamespace($_command);
        if (strpos($_command, '\\') !== false) {
            $_command = mb_substr($_command, mb_strlen($namespace) + 1);
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

        if (! is_dir($directory = $namespaces[$baseSegment]
            . '/' . implode('/', explode('\\', mb_substr($namespace, mb_strlen($baseSegment)))))) {
            mkdir($directory, 0755, true);
        }

        return $directory . '/' . $_command . '.php';
    }

    /**
     * getNamespace
     *
     * @param string $_namespace
     */
    protected function getNamespace(string $_command) : string
    {
        if (strpos($_command, '\\') === false) {
            $_command = static::class;
        }

        $sections = explode('\\', $_command);
        array_pop($sections);
        return implode('\\', $sections);
    }

    /**
     * getClassName
     *
     * @param string $_command
     */
    protected function getClassName(string $_command) : string
    {
        if (strpos($_command, '\\') === false) {
            return $_command;
        }

        $sections = explode('\\', $_command);
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
    protected function getConfigSnippet()
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
    protected function getCommandSnippet()
    {
        return <<<EOT
<?php

declare(strict_types=1);

namespace {namespace};

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class {class} extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static \$defaultName = '{class}';

    /**
     * configure
     *
     */
    protected function configure()
    {
        # - Command configure
        # - see https://symfony.com/doc/current/console.html
    }

    /**
     * execute
     *
     * @param InputInterface \$input
     * @param OutputInterface \$output
     */
    protected function execute(InputInterface \$input, OutputInterface \$output)
    {
        # - Command logic
        # - see https://symfony.com/doc/current/console.html
        return 0;
    }
}
EOT;

    }

}
