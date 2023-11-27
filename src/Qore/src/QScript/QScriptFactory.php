<?php

namespace Qore\QScript;

use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateful\UsingMarks;
use Psr\Container\ContainerInterface;
use Qore\Qore;
use Qore\QScript\Semantics\Statement;

class QScriptFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): QScript  
    {
        return new QScript(
            (new UsingMarks(new LexerDataGenerator()))
                ->createLexer(Statement::getStatements())
        );
    }
}
