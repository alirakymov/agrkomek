<?php

namespace Qore\Sanitizer\Extension\Input\Visitor;

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\NodeInterface;
use HtmlSanitizer\Visitor\AbstractNodeVisitor;
use HtmlSanitizer\Visitor\IsChildlessTagVisitorTrait;
use HtmlSanitizer\Visitor\NamedNodeVisitorInterface;
use Qore\Sanitizer\Extension\Input\Node\InputNode;

class InputNodeVisitor extends AbstractNodeVisitor implements NamedNodeVisitorInterface
{
    use IsChildlessTagVisitorTrait;

    protected function getDomNodeName(): string
    {
        return 'input';
    }

    public function getDefaultAllowedAttributes(): array
    {
        return [
            'type', 'disabled'
        ];
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface
    {
        // You need to pass the current node as your node parent
        $node = new InputNode($cursor->node);
        
        // You can use $this->config['custom_config'] to access the user-defined configuration
        return $node;
    }
}
