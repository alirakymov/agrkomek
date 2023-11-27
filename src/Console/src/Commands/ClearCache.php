<?php

declare(strict_types=1);

namespace Qore\Console\Commands;

use Qore\Qore;
use Qore\CacheManager\CacheCleaner;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class ClearCache extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'cache:clear';

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
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheDirectory = Qore::config('qore.paths.cache-dir');
        $output->writeln(sprintf('<info>Cache directory:</info> %s', $cacheDirectory));
        # - Clear directory cache
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $cacheDirectory,
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

        return 0;
    }
}
