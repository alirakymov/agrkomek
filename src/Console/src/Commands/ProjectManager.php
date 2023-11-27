<?php

declare(strict_types=1);

namespace Qore\Console\Commands;

use Qore\Qore;
use Qore\Database\Adapter\Adapter;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class: ProjectManager
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class ProjectManager extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'project:manager';

    /**
     * configure
     *
     */
    protected function configure()
    {
        # - Command configure - see https://symfony.com/doc/current/console.html
        $this->setDescription('Project manager command allows you to manage projects')
            ->addArgument('name', InputArgument::OPTIONAL, 'Project name which used for names of database and directory')
            ->addArgument('type', InputArgument::OPTIONAL, 'Type of project (App | CMS | CRM)');
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
        $io->title('Manage projects');

        $name = strtolower($input->getArgument('name'));
        $type = strtoupper($input->getArgument('type') ?? 'App');

        if (! is_null($name) && ! preg_match('/^[a-z0-9\-\.]+$/', $name)) {
            $io->caution('Sorry man! Project name is bad! /^[a-z0-9\-\.]+$/');
            return -1;
        }

        if (! in_array($type, ['App', 'CMS', 'CRM'])) {
            $io->caution('Sorry man! Project type is bad! [App, CMS, CRM]');
            return -1;
        }

        if (! is_null($name)) {
            $this->createProject($name, $type, $output);
        } else {
            # - interactive project management
        }

        return 0;
    }

    /**
     * createPRoject
     *
     */
    protected function createProject($_name, $_type, OutputInterface $_output)
    {
        $_output->writeln('<info>Creating project:</info> ' . $_name);

        $dbName = Qore::config('qore.prefixes.database', 'qore_') . $_name;
        $_output->writeln('<info>Creating database:</info> ' . $dbName . '...');

        $db = Qore::service(Adapter::class);
        # - TODO check database existing
        # - $db->query("DROP DATABASE IF EXISTS `{$dbName}`")->execute(); # - DELETE, !!!!
        $db->query("CREATE DATABASE `{$dbName}`")->execute();

        $sqlFile = Qore::config('qore.paths.projects_samples') . '/' . $_type . '/sample.sql';

        $dbConfig = $this->getDBConfig();
        exec(sprintf('mysql -u %s -p%s -h %s %s < %s',
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['hostname'],
            $dbName,
            $sqlFile
        ));

        $_output->writeln('<info>Copying base files of sample project</info> ...');

        $sampleFiles = Qore::config('qore.paths.projects_samples') . '/' . $_type . '/*';
        $projectDirectory = Qore::config('qore.paths.projects') . '/' . $_name;

        if (! is_dir($projectDirectory)) {
            mkdir($projectDirectory, 0755, true);
            exec(sprintf('cp -r %s %s', $sampleFiles, $projectDirectory));
        }

        $_output->writeln('<info>Configure project</info> ...');
        $dbConfig['database'] = $dbName;
        $configFile = $projectDirectory . '/config/db.global.php';
        file_put_contents($configFile, str_replace('{config}', VarExporter::export(['db' => $dbConfig]), $this->getConfigSnippet()));

        $_output->writeln('<info>Project created successfully!</info>');
    }

    /**
     * getDBConfig
     *
     */
    protected function getDBConfig()
    {
        return Qore::config('db');
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

}
