<?php

namespace Qore\Sanitizer;

use HtmlSanitizer\SanitizerBuilder;
use HtmlSanitizer\SanitizerInterface as HtmlSanitizerInterface;


class Sanitizer implements SanitizerInterface
{
    /**
     * @var SanitizerBuilder
     */
    private SanitizerBuilder $_builder;

    /**
     * @var array<HtmlSanitizerInterface>
     */
    private array $instances = [];

    /**
     * @var array
     */
    private array $_defaultConfig;

    /**
     * Constructor
     *
     * @param \HtmlSanitizer\SanitizerBuilder $_builder 
     */
    public function __construct(SanitizerBuilder $_builder, array $_defaultConfig)
    {
        $this->_builder = $_builder;
        $this->_defaultConfig = $_defaultConfig;
    }

    /**
     * Easy access to sanitize method
     *
     * @param string $_input 
     * @param array|null $_config (optional)
     *
     * @return string
     */
    public function __invoke(string $_input, ?array $_config = null): string 
    {
        return $this->sanitize($_input, $_config);
    }

    /**
     * @inheritdoc
     */
    public function sanitize(string $_input, ?array $_config = null): string 
    {
        $instance = $this->getInstance($_config ?? $this->_defaultConfig);
        return $instance->sanitize($_input);
    }

    /**
     * Get HtmlSanitizer instance
     *
     * @param array $_config
     *
     * @return SanitizerInterface
     */
    private function getInstance(array $_config): HtmlSanitizerInterface
    {
        $hash = sha1(serialize($_config));

        if (! isset($this->instances[$hash])) {
            $this->instances[$hash] = $this->_builder->build($_config);
        }

        return $this->instances[$hash];
    }

}
