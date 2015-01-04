<?php
namespace EasyCronTest\ECLang\AST;

require_once __DIR__ . '/../../../_fixtures/EasyCron/ECLang/AST/TestStatement.php';

class NodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \EasyCron\ECLang\AST\ASTException
     */
    public function testDispatch()
    {
        $walker = $this->getMock('\EasyCron\ECLang\ECWalker');
        $ts = new TestStatement();
        $ts->dispatch($walker);
    }
}