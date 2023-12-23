<?php

declare(strict_types=1);

namespace Qore\Daemon\Supervisor;

use Qore\Qore;
use Qore\Core\Entities\QSystemService;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Supervisor\Configuration\Configuration;
use Supervisor\Configuration\Section\Supervisord;
use Supervisor\Configuration\Section\Program;
use Indigo\Ini\Renderer;

/**
 * Class: SupervisorConfigurator
 *
 */
class SupervisorConfigurator
{
    /**
     * __construct
     *
     */
    public function __construct()
    {
        # --
    }

    /**
     * clear
     *
     */
    public function clear()
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->getServicesDirectory(),
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if (is_dir($fileinfo->getRealPath())) {
                rmdir($fileinfo->getRealPath());
            } elseif (is_file($fileinfo->getRealPath())) {
                unlink($fileinfo->getRealPath());
            }
        }
    }

    /**
     * build
     *
     */
    public function build(QSystemService $_service)
    {
        $config = new Configuration();

        $command = $this->prepareCommand($_service->command);

        $config->addSection(new Program($_service->name(), [
            'command' => $command['command'],
            'autostart' => (bool)$_service['autostart'],
            'autorestart' => (bool)$_service['autorestart'],
            'numprocs' => (int)$_service['numprocs'] ?? 1,
            'user' => trim(exec('whoami')),
            'stdout_logfile' => $command['stdout'] ?: $this->getServiceLogsDirectory($_service) . DS . 'stdout_logfile',
            'stderr_logfile' => $this->getServiceLogsDirectory($_service) . DS . 'stderr_logfile',
        ]));

        $renderer = new Renderer();

        is_file($fileName = $this->getServicesDirectory() . DS . $this->getServiceFileName($_service->name)) && unlink($fileName);

        file_put_contents($fileName, $renderer->render($config->toArray()));

        $this->clearServicesDirectory();
    }

    /**
     * prepareCommand
     *
     * @param string $_command
     */
    private function prepareCommand(string $_command)
    {
        preg_match_all('/\$\(\'(.+?)\'\)/u', $_command, $result);

        $replace = [];
        foreach ($result[1] as $key => $configPath) {
            $replace[$result[0][$key]] = Qore::config($configPath, null);
        }

        $parts = explode('>', str_replace(array_keys($replace), array_values($replace), $_command));

        return [
            'command' => trim($parts[0]),
            'stdout' => isset($parts[1]) ? trim($parts[1]) : false,
        ];
    }

    /**
     * clearServiceDirectory
     *
     */
    private function clearServicesDirectory()
    {
        $mm = Qore::service('mm');
        $services = $mm('QSystem:Services')->all()->map(function($_service){
            $_service['filename'] = $this->getServiceFileName($_service->name);
            return $_service;
        });

        Qore::collection(scandir($this->getServicesDirectory()))
            ->filter(function($_file){
                return is_file($this->getServicesDirectory() . DS . $_file);
            })->each(function($_file) use ($services) {
                if (is_null($services->firstMatch(['filename' => $_file]))) {
                    unlink($this->getServicesDirectory() . DS . $_file);
                }
            });
    }

    /**
     * getServiceFileName
     *
     * @param mixed $_service
     */
    private function getServiceFileName($_service)
    {
        return $_service . '.conf';
    }

    /**
     * getServiceDirectory
     *
     */
    private function getServicesDirectory()
    {
        return Qore::config('qore.paths.services-files');
    }

    /**
     * getServiceLogsDirectory
     *
     * @param QSystemService $_service
     */
    private function getServiceLogsDirectory(QSystemService $_service)
    {

        $logsDirectory = Qore::config('qore.paths.logs-files');

        if (! is_dir($logsDirectory = $logsDirectory . DS . 'service')) {
            mkdir($logsDirectory);
        }

        if (! is_dir($logsDirectory = $logsDirectory . DS . $_service->name)) {
            mkdir($logsDirectory);
        }

        return $logsDirectory;
    }

}
