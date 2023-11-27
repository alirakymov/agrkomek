<?php

namespace Qore\QScript;

use Phlexy\Lexer;

class QScript implements QScriptInterface
{
    /**
     * @var Lexer
     */
    private Lexer $_lexer;

    /**
     * @var Code
     */
    private Code $code;

    /**
     * Constructor
     *
     * @param \Phlexy\Lexer $_lexer
     */
    public function __construct(Lexer $_lexer)
    {
        $this->_lexer = $_lexer;
    }

    /**
     * Easy access for parser method
     *
     * @param string $_code 
     *
     * @return Code 
     */
    public function __invoke(string $_code): Code
    {
        return $this->parse($_code);
    }

    /**
     * Parse code and return builded code object
     *
     * @param string $_code 
     *
     * @return Code
     */
    public function parse(string $_code): Code
    {
        return $this->code = new Code($this->_lexer, $_code);
    }

}
