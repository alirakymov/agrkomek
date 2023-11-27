<?php

declare(strict_types=1);

namespace Qore\Console\Commands\DumpServer;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Command\Descriptor\DumpDescriptorInterface;
use Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor as SymfonyHtmlDescriptor;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Class: HtmlDescriptor
 *
 * @see SymfonyHtmlDescriptor
 */
class HtmlDescriptor implements DumpDescriptorInterface
{
    /**
     * descriptor
     *
     * @var mixed
     */
    private $descriptor = null;

    /**
     * __construct
     *
     * @param HtmlDumper $_dumper
     */
    public function __construct(HtmlDumper $_dumper)
    {
        $descriptorClass = new \ReflectionClass(SymfonyHtmlDescriptor::class);
        $property = $descriptorClass->getProperty('initialized');
        $property->setAccessible(true);

        $this->descriptor = new SymfonyHtmlDescriptor($_dumper);
        $property->setValue($this->descriptor, true);
    }

    /**
     * describe
     *
     * @param OutputInterface $_output
     * @param Data $_data
     * @param array $_context
     * @param int $_clientId
     */
    public function describe(OutputInterface $_output, Data $_data, array $_context, int $_clientId): void
    {
        $this->descriptor->describe($_output, $_data, $_context, $_clientId);
    }

}
