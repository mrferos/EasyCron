<?php
namespace EasyCronTest\ECLang\AST;

use EasyCron\ECLang\AST\DetermineInStatement;

class DetermineInStatementTest extends \PHPUnit_Framework_TestCase
{
    public function testSetterAndGetter()
    {
        $values = array('jan', 'feb', 'march');
        $ds = new DetermineInStatement();
        $ds->setMonths($values);
        $returnedValues = $ds->getMonths();
        foreach ($values as $value) {
            $this->assertContains($value, $returnedValues);
        }
    }
}