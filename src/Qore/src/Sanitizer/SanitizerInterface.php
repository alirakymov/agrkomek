<?php

namespace Qore\Sanitizer;

use HtmlSanitizer\SanitizerInterface as HtmlSanitizerSanitizerInterface;

interface SanitizerInterface 
{
    /**
     * Sanitize input string with configuration
     *
     * @param string $_input 
     * @param array|null $_config (optional)
     *
     * @return string
     */
    public function sanitize(string $_input, ?array $_config = null): string;

}
