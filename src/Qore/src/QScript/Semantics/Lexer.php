<?php

namespace Qore\QScript\Semantics;

use Phlexy\Lexer as PhlexyLexer;
use PhpParser\ErrorHandler;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Lexer as PhpParserLexer;

class Lexer extends PhpParserLexer
{
    /**
     * Constructor
     *
     * @param PhlexyLexer $_lexer 
     * @param array $_options (optional) 
     */
    public function __construct(PhlexyLexer $_lexer, array $_options = [])
    {
        $this->_lexer = $_lexer;
        parent::__construct($_options);
    }

    /**
     * Initializes the lexer for lexing the provided source code.
     *
     * This function does not throw if lexing errors occur. Instead, errors may be retrieved using
     * the getErrors() method.
     *
     * @param string $code The source code to lex
     * @param ErrorHandler|null $errorHandler Error handler to use for lexing errors. Defaults to
     *                                        ErrorHandler\Throwing
     */
    public function startLexing(string $code, ErrorHandler $errorHandler = null)
    {
        if (null === $errorHandler) {
            $errorHandler = new Throwing();
        }

        $this->code = $code; // keep the code around for __halt_compiler() handling
        $this->pos  = -1;
        $this->line =  1;
        $this->filePos = 0;

        // If inline HTML occurs without preceding code, treat it as if it had a leading newline.
        // This ensures proper composability, because having a newline is the "safe" assumption.
        $this->prevCloseTagHasNewline = true;

        $scream = ini_set('xdebug.scream', '0');

        $this->tokens = $this->normalizeTokens($this->_lexer->lex($this->code));
        
        $this->postprocessTokens($errorHandler);

        if (false !== $scream) {
            ini_set('xdebug.scream', $scream);
        }
    }

    /**
     * Normalize tokens array. Bring to the form token_get_all form
     *
     * @param array $_tokens 
     *
     * @return array 
     */
    protected function normalizeTokens(array $_tokens): array
    {
        foreach ($_tokens as &$token) {
            if (is_int($token[0])) {
                $t = $token[1];
                $token[1] = $token[2];
                $token[2] = $t;
            } else {
                $token = $token[0];
            }
        }

        return $_tokens;
    }

}
