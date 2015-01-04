<?php
namespace EasyCronTest\ECLang\AST;

use EasyCron\ECLang\AST\ExecuteStatement;

class ExecuteStatementTest extends \PHPUnit_Framework_TestCase
{
    public function testCommandSetterAndGetter()
    {
        $executeStatement = new ExecuteStatement();
        $command = 'foo';
        $executeStatement->setCommand($command);
        $this->assertEquals($command, $executeStatement->getCommand());
    }
}