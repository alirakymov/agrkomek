<?php

declare(strict_types=1);

namespace Qore\Console\Commands;

use Qore\Qore;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;
use Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor;
use Symfony\Component\VarDumper\Command\ServerDumpCommand;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;
use Symfony\Component\VarDumper\Server\DumpServer as VarDumpServer;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class DumpServer extends SymfonyCommand
{
    /**
     * locker
     *
     * @var mixed
     */
    protected $locker = null;

    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'server:dump';

    /**
     * server
     *
     * @var mixed
     */
    protected $server;

    /** @var DumpDescriptorInterface[] */
    protected $descriptors;

    /**
     * __construct
     *
     * @param DumpServer $server
     * @param array $descriptors
     */
    public function __construct(VarDumpServer $server, array $descriptors = [])
    {
        $this->server = $server;
        $this->descriptors = $descriptors + [
            'cli' => new CliDescriptor(new CliDumper()),
            'html' => new DumpServer\HtmlDescriptor(new HtmlDumper()),
        ];

        parent::__construct();
    }

    /**
     * getLocker
     *
     */
    public function getLocker() : Lock
    {
        if (is_null($this->locker)) {
            if (SemaphoreStore::isSupported()) {
                $store = new SemaphoreStore();
            } else {
                $store = new FlockStore();
            }
            $this->locker = (new LockFactory($store))->createLock($this->getName());
        }

        return $this->locker;
    }

    /**
     * initFallbackDumper
     *
     */
    public function initFallbackDumper()
    {
        $locker = $this->getLocker();
        if (! $locker->acquire()) {
            $cloner = new VarCloner();
            $fallbackDumper = new HtmlDumper();
            $dumper = new ServerDumper(Qore::config('dump-server.client'), $fallbackDumper, [
                'cli' => new CliContextProvider(),
                'source' => new SourceContextProvider(),
            ]);
            VarDumper::setHandler(function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var)->withMaxDepth(40));
            });
        } else {
            $locker->release();
        }
    }

    /**
     * configure
     *
     */
    protected function configure()
    {
        $availableFormats = implode(', ', array_keys($this->descriptors));

        $this
            ->addOption('format', null, InputOption::VALUE_REQUIRED, sprintf('The output format (%s)', $availableFormats), 'cli')
            ->setDescription('Starts a dump server that collects and displays dumps in a single place')
            ->setHelp(<<<'EOF'
<info>%command.name%</info> starts a dump server that collects and displays
dumps in a single place for debugging you application:

  <info>php %command.full_name%</info>

You can consult dumped data in HTML format in your browser by providing the <comment>--format=html</comment> option
EOF
            );
    }

    /**
     * execute
     *
     * @param InputInterface $_input
     * @param OutputInterface $_output
     */
    protected function execute(InputInterface $_input, OutputInterface $_output): int
    {
        if (! $this->lock()) {
            $_output->writeln('The command is already running in another process.');
            return 0;
        }

        $io = new SymfonyStyle($_input, $_output);
        $format = $_input->getOption('format');

        if (!$descriptor = $this->descriptors[$format] ?? null) {
            throw new InvalidArgumentException(sprintf('Unsupported format "%s".', $format));
        }

        $errorIo = $io->getErrorStyle();
        $errorIo->title('Qore Var Dumper Server');

        $this->server->start();

        $errorIo->success(sprintf('Server listening on %s', $this->server->getHost()));

        $outputStream = new StreamOutput(fopen(Qore::config('dump-server.html-file'), 'a', false));

        $this->server->listen(function (Data $data, array $context, int $clientId) use ($descriptor, $outputStream) {
            $descriptor->describe($outputStream, $data, $context, $clientId);
        });
        return $result;
    }

    /**
     * lock
     *
     */
    protected function lock($_blocking = false)
    {
        $locker = $this->getLocker();
        if (! $locker->acquire($_blocking)) {
            return false;
        }

        return true;
    }

}
