<?php

namespace Qore\QScript;

use Hoa\Compiler\Test\Unit\Llk\Lexer as LlkLexer;
use Phlexy\Lexer;
use PhpParser\PrettyPrinter\Standard;
use Qore\QScript\Semantics\Lexer as SemanticsLexer;
use Qore\QScript\Semantics\Parser;

class Code
{
    /**
     * @var Lexer
     */
    private Lexer $_lexer;

    /**
     * @var string
     */
    private string $_code;

    /**
     * @var Parser
     */
    private Parser $parser;

    /**
     * @var ?PhpParser\Node\Stmt[]
     */
    private array $ast;

    /**
     * Constructor
     *
     * @param \Phlexy\Lexer $_lexer
     */
    public function __construct(Lexer $_lexer, string $_code)
    {
        $this->_lexer = $_lexer;
        $this->_code = $_code;
        $this->parse();
    }

    /**
     * Parse code with lexer
     *
     * @return void 
     */
    private function parse(): void
    {
        $this->parser = new Parser(new SemanticsLexer($this->_lexer));
        $this->ast = $this->parser->parse($this->_code);
    }

    /**
     * Compile QScript code to PHP native code
     *
     * @return string 
     */
    public function compile(): string
    {
        $prettyPrinter = new Standard;
        $newCode = $prettyPrinter->prettyPrint($this->ast);
        return $newCode;
    }

}
