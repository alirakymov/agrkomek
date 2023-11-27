<?php

namespace Qore\Sanitizer\Extension\Input\Node;

use HtmlSanitizer\Node\AbstractTagNode;
use HtmlSanitizer\Node\IsChildlessTrait;

class InputNode extends AbstractTagNode
{
    use IsChildlessTrait;

    public function getTagName(): string
    {
        return 'input';
    }
}
