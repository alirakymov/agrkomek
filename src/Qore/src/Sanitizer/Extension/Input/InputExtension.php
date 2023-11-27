<?php

namespace Qore\Sanitizer\Extension\Input;

use HtmlSanitizer\Extension\ExtensionInterface;
use Qore\Sanitizer\Extension\Input\Visitor\InputNodeVisitor;

class InputExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'input';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'input' => new InputNodeVisitor($config['tags']['input'] ?? []),
        ];
    }
}
