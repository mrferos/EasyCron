<?php
namespace EasyCronTest;

use EasyCron\ECLang\ECWalker;

class ECWalkerTest extends \PHPUnit_Framework_TestCase
{
    public function testWalkExecuteStatement()
    {
        $command = 'foo';
        $executeMock = $this->getMock('\EasyCron\ECLang\AST\ExecuteStatement');
        $executeMock->expects($this->once())->method('getCommand')->will($this->returnValue($command));

        $ecWalker = new ECWalker();
        $ecWalker->walkExecuteStatement($executeMock);
        $components = $ecWalker->getComponents();
        $this->assertEquals($command, $components['command']);
    }

    public function testWalkDetermineOnTheStatement()
    {
        $days = array(1,2,3);
        $executeMock = $this->getMock('\EasyCron\ECLang\AST\DetermineOnTheStatement');
        $executeMock->expects($this->once())->method('getDays')->will($this->returnValue($days));

        $ecWalker = new ECWalker();
        $ecWalker->walkDetermineOnTheStatement($executeMock);
        $components = $ecWalker->getComponents();
        $this->assertEquals(implode(',', $days), $components['monthday']);
    }
}