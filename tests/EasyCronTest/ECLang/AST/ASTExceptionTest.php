<?php
namespace EasyCronTest\ECLang\AST;

use EasyCron\ECLang\AST\ASTException;
use EasyCron\ECLang\AST\DetermineAtStatement;

class ASTExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \EasyCron\ECLang\AST\ASTException
     * @expectedMessages Double-dispatch for node EasyCron\ECLang\AST\DetermineAtStatement is not supported.
     */
    public function testNoDispatchForNode()
    {
        $atStatement = new DetermineAtStatement();
        throw ASTException::noDispatchForNode($atStatement);
    }
}