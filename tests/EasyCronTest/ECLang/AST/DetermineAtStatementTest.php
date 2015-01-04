<?php
namespace EasyCronTest\ECLang\AST;

use EasyCron\ECLang\AST\DetermineAtStatement;

class DetermineAtStatementTest extends \PHPUnit_Framework_TestCase
{
    public function testSetterAndGetter()
    {
        $values = array('1am', '2am', '3pm');
        $ds = new DetermineAtStatement();
        $ds->setTimes($values);
        $returnedValues = $ds->getTimes();
        foreach ($values as $value) {
            $this->assertContains($value, $returnedValues);
        }
    }
}